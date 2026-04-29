-- Add Deployment Plan Permissions to the database
-- Run this SQL script directly in your database

-- Insert Daily Deployment Plan permissions (only if they don't exist)
INSERT IGNORE INTO permissions (name, user_id, created_at, updated_at) VALUES
('daily-deployment-plan-add', 1, NOW(), NOW()),
('daily-deployment-plan-view', 1, NOW(), NOW()),
('daily-deployment-plan-edit', 1, NOW(), NOW()),
('daily-deployment-plan-delete', 1, NOW(), NOW()),
('friday-deployment-plan-add', 1, NOW(), NOW()),
('friday-deployment-plan-view', 1, NOW(), NOW()),
('friday-deployment-plan-edit', 1, NOW(), NOW()),
('friday-deployment-plan-delete', 1, NOW(), NOW());

