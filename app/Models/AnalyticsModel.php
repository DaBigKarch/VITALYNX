<?php
namespace App\Models;
use App\Core\Model;

class AnalyticsModel extends Model {

    private function getTimeCondition($range) {
        switch ($range) {
            case 'today': return " AND e.created_at >= CURDATE()";
            case '7d':    return " AND e.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30d':   return " AND e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90d':   return " AND e.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            default:      return "";
        }
    }

    public function getCoreMetrics($range) {
        $timeCond = $this->getTimeCondition($range);
        
        $metrics = [
            'total' => 0, 'REPORTED' => 0, 'TRIAGED' => 0,
            'ACCEPTED' => 0, 'ASSIGNED' => 0, 'IN_CARE' => 0, 'RESOLVED' => 0
        ];
        
        $sql = "SELECT status, COUNT(*) as cnt FROM emergency_cases e WHERE 1=1 $timeCond GROUP BY status";
        $stmt = $this->db->query($sql);
        $total = 0;
        foreach ($stmt->fetchAll() as $row) {
            $metrics[$row['status']] = $row['cnt'];
            $total += $row['cnt'];
        }
        $metrics['total'] = $total;
        
        return $metrics;
    }

    public function getUrgencyDistribution($range) {
        $timeCond = $this->getTimeCondition($range);
        $sql = "SELECT a.urgency, COUNT(*) as cnt FROM ai_assessments a 
                JOIN emergency_cases e ON a.case_id = e.id 
                WHERE 1=1 $timeCond GROUP BY a.urgency";
        $stmt = $this->db->query($sql);
        $dist = ['critical' => 0, 'high' => 0, 'moderate' => 0, 'low' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $u = strtolower($row['urgency']);
            if (isset($dist[$u])) $dist[$u] = $row['cnt'];
        }
        return $dist;
    }

    public function getSosMetrics($range) {
        $timeCond = $this->getTimeCondition($range);
        // Using EXISTS for efficient lookup rather than full join if possible, 
        // but simple JOIN is fine here.
        $sql = "SELECT e.status, COUNT(DISTINCT e.id) as cnt 
                FROM emergency_cases e 
                JOIN case_events ce ON e.id = ce.case_id 
                WHERE ce.event = 'SOS_ACTIVATED' $timeCond 
                GROUP BY e.status";
        $stmt = $this->db->query($sql);
        
        $metrics = ['total' => 0, 'active' => 0, 'resolved' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $cnt = (int)$row['cnt'];
            $metrics['total'] += $cnt;
            if ($row['status'] === 'RESOLVED') {
                $metrics['resolved'] += $cnt;
            } else {
                $metrics['active'] += $cnt;
            }
        }
        return $metrics;
    }

    public function getHospitalRoutingMetrics($range) {
        $timeCond = $this->getTimeCondition($range);
        $sql = "SELECT ch.status, COUNT(*) as cnt 
                FROM case_hospitals ch 
                JOIN emergency_cases e ON ch.case_id = e.id 
                WHERE 1=1 $timeCond 
                GROUP BY ch.status";
        $stmt = $this->db->query($sql);
        
        $metrics = ['matched' => 0, 'accepted' => 0, 'declined' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $s = strtolower($row['status']);
            // e.g. matched, notified, viewed can be aggregated as "matched" attempts or we can track precisely
            if ($s === 'accepted') $metrics['accepted'] += $row['cnt'];
            elseif ($s === 'declined') $metrics['declined'] += $row['cnt'];
            else $metrics['matched'] += $row['cnt'];
        }
        return $metrics;
    }

    public function getFollowupMetrics($range) {
        $timeCond = $this->getTimeCondition($range);
        $sql = "SELECT COUNT(*) as total, 
                       AVG(f.experience_rating) as avg_rating, 
                       SUM(f.needs_further_coordination) as coord_req 
                FROM case_followups f 
                JOIN emergency_cases e ON f.case_id = e.id 
                WHERE 1=1 $timeCond";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();
        return [
            'total' => (int)$row['total'],
            'avg_rating' => $row['avg_rating'] ? round((float)$row['avg_rating'], 1) : null,
            'coordination_requests' => (int)$row['coord_req']
        ];
    }

    public function getResolutionOutcomes($range) {
        $timeCond = $this->getTimeCondition($range);
        $sql = "SELECT e.resolution_outcome, COUNT(*) as cnt 
                FROM emergency_cases e 
                WHERE e.status = 'RESOLVED' $timeCond 
                GROUP BY e.resolution_outcome";
        $stmt = $this->db->query($sql);
        
        $metrics = [];
        foreach ($stmt->fetchAll() as $row) {
            $outcome = $row['resolution_outcome'] ?: 'UNKNOWN';
            $metrics[$outcome] = $row['cnt'];
        }
        return $metrics;
    }

    public function getTimingMetrics($range) {
        $timeCond = $this->getTimeCondition($range);
        // We will calculate average seconds between events.
        // A robust way in SQL: AVG(TIMESTAMPDIFF(SECOND, start_event.created_at, end_event.created_at))
        $pairs = [
            'report_triage' => ['EMERGENCY_REPORTED', 'AI_ASSESSMENT_COMPLETED'],
            'triage_accept' => ['AI_ASSESSMENT_COMPLETED', 'CASE_ACCEPTED'],
            'accept_assign' => ['CASE_ACCEPTED', 'CASE_ASSIGNED'],
            'assign_care'   => ['CASE_ASSIGNED', 'CASE_IN_CARE'],
            'care_resolve'  => ['CASE_IN_CARE', 'CASE_RESOLVED'],
            'report_resolve'=> ['EMERGENCY_REPORTED', 'CASE_RESOLVED']
        ];

        $timings = [];
        foreach ($pairs as $key => $events) {
            $start = $events[0];
            $end = $events[1];
            $sql = "SELECT AVG(TIMESTAMPDIFF(SECOND, ev1.min_time, ev2.min_time)) as avg_sec 
                    FROM (SELECT case_id, MIN(created_at) as min_time FROM case_events WHERE event = '$start' GROUP BY case_id) ev1 
                    JOIN (SELECT case_id, MIN(created_at) as min_time FROM case_events WHERE event = '$end' GROUP BY case_id) ev2 ON ev1.case_id = ev2.case_id 
                    JOIN emergency_cases e ON ev1.case_id = e.id 
                    WHERE ev2.min_time >= ev1.min_time 
                      $timeCond";
            $stmt = $this->db->query($sql);
            $avg = $stmt->fetchColumn();
            $timings[$key] = ($avg !== null && $avg !== false) ? (int)$avg : null;
        }
        return $timings;
    }
}
