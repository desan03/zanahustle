-- ZanaHustle Database Schema
-- Drop and recreate database
DROP DATABASE IF EXISTS abc;
CREATE DATABASE abc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE abc;

-- Users table (single account, dual role)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    profile_photo VARCHAR(255),
    bio TEXT,
    phone VARCHAR(20),
    country VARCHAR(50),
    city VARCHAR(50),
    can_be_client BOOLEAN DEFAULT TRUE,
    can_be_freelancer BOOLEAN DEFAULT TRUE,
    primary_role VARCHAR(20) DEFAULT 'freelancer', -- client or freelancer (default role)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    KEY idx_username (username),
    KEY idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Profiles (extended info)
CREATE TABLE user_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    skills TEXT,
    portfolio_url VARCHAR(255),
    hourly_rate DECIMAL(10, 2),
    total_earnings DECIMAL(15, 2) DEFAULT 0,
    completed_projects INT DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0,
    reviews_count INT DEFAULT 0,
    verification_status VARCHAR(20) DEFAULT 'unverified',
    verification_date TIMESTAMP NULL,
    is_top_rated BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jobs table
CREATE TABLE jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100),
    budget_type VARCHAR(20), -- fixed, hourly
    budget_min DECIMAL(12, 2) NOT NULL CHECK (budget_min >= 10000), -- Minimum TZS 10,000
    budget_max DECIMAL(12, 2) NOT NULL CHECK (budget_max >= budget_min),
    duration VARCHAR(50),
    experience_level VARCHAR(20), -- beginner, intermediate, expert
    status VARCHAR(20) DEFAULT 'open', -- open, in_progress, completed, cancelled
    views INT DEFAULT 0,
    proposals_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deadline DATE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job attachments
CREATE TABLE job_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    file_path VARCHAR(255),
    file_name VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Proposals table
CREATE TABLE proposals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    bid_amount DECIMAL(12, 2) NOT NULL CHECK (bid_amount >= 10000), -- Minimum TZS 10,000
    cover_letter TEXT,
    timeline VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pending', -- pending, accepted, rejected, withdrawn
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_proposal (job_id, freelancer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contracts table
CREATE TABLE contracts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    client_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    proposal_id INT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL CHECK (amount >= 10000),
    status VARCHAR(20) DEFAULT 'active', -- active, completed, cancelled
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews/Ratings table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contract_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewee_id INT NOT NULL,
    rating INT NOT NULL, -- 1-5
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255),
    body TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services table (Freelancer Gigs)
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    freelancer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100),
    price DECIMAL(12, 2) NOT NULL CHECK (price >= 10000), -- TZS, minimum 10,000
    delivery_time INT, -- days
    revisions INT DEFAULT 2,
    features TEXT, -- JSON array of features
    status VARCHAR(20) DEFAULT 'active', -- active, inactive, paused
    views INT DEFAULT 0,
    orders_count INT DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0,
    reviews_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_freelancer_id (freelancer_id),
    KEY idx_category (category),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service Orders table
CREATE TABLE service_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT NOT NULL,
    client_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending', -- pending, in_progress, completed, cancelled
    delivery_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for performance
CREATE INDEX idx_job_client ON jobs(client_id);
CREATE INDEX idx_job_status ON jobs(status);
CREATE INDEX idx_proposal_job ON proposals(job_id);
CREATE INDEX idx_proposal_freelancer ON proposals(freelancer_id);
CREATE INDEX idx_proposal_status ON proposals(status);
CREATE INDEX idx_contract_client ON contracts(client_id);
CREATE INDEX idx_contract_freelancer ON contracts(freelancer_id);
CREATE INDEX idx_message_receiver ON messages(receiver_id);
CREATE INDEX idx_service_freelancer ON services(freelancer_id);
CREATE INDEX idx_service_orders_client ON service_orders(client_id);
CREATE INDEX idx_service_orders_freelancer ON service_orders(freelancer_id);
