<?php
namespace App\Services;
use App\Models\Hospital;

class HospitalMatchingService {
    
    public function matchAndRouteCase($caseId, $patientLat, $patientLng, $urgency = 'MODERATE') {
        $hospitals = $this->getRecommendedHospitals($patientLat, $patientLng, $urgency);
        
        if (empty($hospitals)) {
            return false;
        }

        $caseHospitalModel = new \App\Models\CaseHospital();
        $notificationService = new \App\Services\NotificationService();
        $eventModel = new \App\Models\CaseEvent();
        
        $isSos = $eventModel->hasEvent($caseId, 'SOS_ACTIVATED');
        
        // For SOS or CRITICAL, we must treat it as critical workflow
        if ($urgency === 'CRITICAL' || $isSos) {
            $urgency = 'CRITICAL';
        }
        
        foreach ($hospitals as $h) {
            $caseHospitalModel->createOrUpdate($caseId, $h['id'], $h['distance'], 'MATCHED');
            if ($isSos || $urgency === 'CRITICAL') {
                $notificationService->notifyHospital($h['id'], 'CRITICAL_CASE_ROUTED', 'CRITICAL emergency case requires immediate attention.', $caseId);
            } else {
                $notificationService->notifyHospital($h['id'], 'CASE_ROUTED', "New $urgency urgency case routed to your facility.", $caseId);
            }
            $eventModel->create($caseId, 'HOSPITAL_NOTIFIED', 'hospital_' . $h['id']);
        }

        $eventModel->create($caseId, 'HOSPITALS_MATCHED', 'system');
        $eventModel->create($caseId, 'CASE_ROUTED', 'system');

        return true;
    }

    public function getRecommendedHospitals($patientLat, $patientLng, $urgency = 'MODERATE') {
        if ($patientLat === null || $patientLng === null) {
            return [];
        }

        $hospitalModel = new Hospital();
        
        // Match logic:
        // CRITICAL and HIGH urgency cases ONLY get facilities with emergency_available = 1
        // MODERATE and LOW urgency can get any active facility (including clinics without full emergency_available)
        if ($urgency === 'CRITICAL' || $urgency === 'HIGH') {
            $hospitals = $hospitalModel->getActiveEmergencyHospitals();
        } else {
            $hospitals = $hospitalModel->getAllActiveHospitals();
        }
        
        $results = [];

        foreach ($hospitals as $h) {
            $distance = $this->calculateDistance($patientLat, $patientLng, $h['latitude'], $h['longitude']);
            
            // Limit distance matching to ensure patients aren't routed to unrealistic locations
            // CRITICAL cases might have a larger absolute catchment radius if desperate, but typically limit to 50km
            // LOW cases might be limited to 20km
            if ($urgency === 'CRITICAL' || $urgency === 'HIGH') {
                $maxDistance = 50; 
            } else {
                $maxDistance = 20;
            }
            
            if ($distance <= $maxDistance) {
                $h['distance'] = $distance;
                $results[] = $h;
            }
        }

        // Sort by distance (shortest first)
        usort($results, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        // Priority sorting logic for Critical/High if needed can be added, but distance is primary.
        
        // Take top 3 for CRITICAL (to not overwhelm/spam), top 5 for others
        $limit = ($urgency === 'CRITICAL') ? 3 : 5;
        return array_slice($results, 0, $limit);
    }

    /**
     * Haversine formula to calculate the great-circle distance between two points on a sphere.
     * Returns distance in kilometers.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
}
