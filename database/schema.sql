-- Complete schema for the current VITALYNX application.
-- This file is for a fresh database. Back up and migrate existing databases separately.
CREATE DATABASE IF NOT EXISTS vitalynx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vitalynx;

CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    address TEXT NULL,
    phone VARCHAR(50) NULL,
    emergency_available TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hospitals_routing (status, emergency_available, latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'hospital', 'admin') NOT NULL DEFAULT 'patient',
    staff_role ENUM('doctor', 'nurse') NULL,
    hospital_id INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_hospital_staff (hospital_id, role, staff_role),
    CONSTRAINT fk_users_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS emergency_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    description TEXT NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    urgency ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    status ENUM('REPORTED', 'AI_ASSESSING', 'TRIAGED', 'HOSPITAL_NOTIFIED', 'ACCEPTED', 'ASSIGNED', 'IN_CARE', 'RESOLVED') NOT NULL DEFAULT 'REPORTED',
    resolved_at DATETIME NULL,
    resolved_by INT NULL,
    resolution_outcome ENUM('CARE_COMPLETED', 'TRANSFERRED', 'REFERRED', 'OTHER') NULL,
    resolution_summary TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cases_patient_status (user_id, status, created_at),
    INDEX idx_cases_status_created (status, created_at),
    CONSTRAINT fk_cases_patient FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cases_resolved_by FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    summary TEXT NOT NULL,
    urgency ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    confidence DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    red_flags JSON NOT NULL,
    recommendation TEXT NOT NULL,
    model VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_assessments_case (case_id, id),
    CONSTRAINT fk_assessments_case FOREIGN KEY (case_id) REFERENCES emergency_cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    sender ENUM('user', 'ai', 'system') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ai_messages_case_order (case_id, created_at, id),
    CONSTRAINT fk_ai_messages_case FOREIGN KEY (case_id) REFERENCES emergency_cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS case_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    sender_id INT NOT NULL,
    sender_role ENUM('patient', 'hospital') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_case_messages_order (case_id, created_at, id),
    CONSTRAINT fk_case_messages_case FOREIGN KEY (case_id) REFERENCES emergency_cases(id) ON DELETE CASCADE,
    CONSTRAINT fk_case_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS case_hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    hospital_id INT NOT NULL,
    distance DECIMAL(8, 2) NULL,
    status ENUM('MATCHED', 'NOTIFIED', 'VIEWED', 'ACCEPTED', 'DECLINED', 'EXPIRED') NOT NULL DEFAULT 'MATCHED',
    assigned_to INT NULL,
    requires_doctor_review TINYINT(1) NOT NULL DEFAULT 0,
    notified_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_case_hospital (case_id, hospital_id),
    INDEX idx_case_hospitals_hospital_status (hospital_id, status, notified_at),
    INDEX idx_case_hospitals_assignee (assigned_to, status),
    CONSTRAINT fk_case_hospitals_case FOREIGN KEY (case_id) REFERENCES emergency_cases(id) ON DELETE CASCADE,
    CONSTRAINT fk_case_hospitals_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    CONSTRAINT fk_case_hospitals_assignee FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS case_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    event VARCHAR(255) NOT NULL,
    actor VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_case_events_case_event_time (case_id, event, created_at),
    INDEX idx_case_events_event_time (event, created_at),
    CONSTRAINT fk_case_events_case FOREIGN KEY (case_id) REFERENCES emergency_cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    hospital_id INT NULL,
    case_id INT NULL,
    type VARCHAR(80) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user_unread (user_id, is_read, created_at),
    INDEX idx_notifications_hospital_unread (hospital_id, is_read, created_at),
    INDEX idx_notifications_dedupe (case_id, type, is_read),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_case FOREIGN KEY (case_id) REFERENCES emergency_cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS care_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    hospital_id INT NOT NULL,
    user_id INT NOT NULL,
    update_type VARCHAR(50) NOT NULL,
    update_text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_care_updates_case_order (case_id, created_at),
    CONSTRAINT fk_care_updates_case FOREIGN KEY (case_id) REFERENCES emergency_cases(id) ON DELETE CASCADE,
    CONSTRAINT fk_care_updates_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    CONSTRAINT fk_care_updates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clinical_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    user_id INT NOT NULL,
    hospital_id INT NOT NULL,
    note_text TEXT NOT NULL,
    note_type VARCHAR(50) NOT NULL DEFAULT 'GENERAL',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_clinical_notes_case_hospital (case_id, hospital_id, created_at),
    CONSTRAINT fk_clinical_notes_case FOREIGN KEY (case_id) REFERENCES emergency_cases(id) ON DELETE CASCADE,
    CONSTRAINT fk_clinical_notes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_clinical_notes_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS case_followups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    user_id INT NOT NULL,
    hospital_id INT NULL,
    needs_further_coordination TINYINT(1) NOT NULL DEFAULT 0,
    experience_rating TINYINT UNSIGNED NULL,
    feedback_text TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_case_followups_case (case_id),
    INDEX idx_case_followups_hospital (hospital_id, created_at),
    CONSTRAINT fk_case_followups_case FOREIGN KEY (case_id) REFERENCES emergency_cases(id) ON DELETE CASCADE,
    CONSTRAINT fk_case_followups_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_case_followups_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE SET NULL,
    CONSTRAINT chk_case_followup_rating CHECK (experience_rating IS NULL OR experience_rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id INT NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_audit_time (created_at),
    CONSTRAINT fk_admin_audit_user FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    expires_at DATETIME NULL,
    revoked_at DATETIME NULL,
    last_used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_tokens_user (user_id, revoked_at),
    CONSTRAINT fk_api_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
