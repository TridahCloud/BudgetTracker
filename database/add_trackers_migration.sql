-- Migration: Add Multi-Tracker Support
-- Run this AFTER your current schema is in place

-- 1. Create Trackers/Workspaces table
CREATE TABLE IF NOT EXISTS trackers (
    tracker_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tracker_name VARCHAR(255) NOT NULL DEFAULT 'My Budget',
    tracker_type ENUM('personal', 'business', 'family', 'other') DEFAULT 'personal',
    description TEXT,
    icon VARCHAR(50) DEFAULT '💰',
    color VARCHAR(7) DEFAULT '#6366f1',
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_user_active (user_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add tracker_id to existing tables
ALTER TABLE income_sources 
    ADD COLUMN tracker_id INT AFTER user_id,
    ADD FOREIGN KEY (tracker_id) REFERENCES trackers(tracker_id) ON DELETE CASCADE,
    ADD INDEX idx_tracker_id (tracker_id);

ALTER TABLE income_transactions 
    ADD COLUMN tracker_id INT AFTER user_id,
    ADD FOREIGN KEY (tracker_id) REFERENCES trackers(tracker_id) ON DELETE CASCADE,
    ADD INDEX idx_tracker_id (tracker_id);

ALTER TABLE expense_transactions 
    ADD COLUMN tracker_id INT AFTER user_id,
    ADD FOREIGN KEY (tracker_id) REFERENCES trackers(tracker_id) ON DELETE CASCADE,
    ADD INDEX idx_tracker_id (tracker_id);

ALTER TABLE budgets 
    ADD COLUMN tracker_id INT AFTER user_id,
    ADD FOREIGN KEY (tracker_id) REFERENCES trackers(tracker_id) ON DELETE CASCADE,
    ADD INDEX idx_tracker_id (tracker_id);

ALTER TABLE financial_goals 
    ADD COLUMN tracker_id INT AFTER user_id,
    ADD FOREIGN KEY (tracker_id) REFERENCES trackers(tracker_id) ON DELETE CASCADE,
    ADD INDEX idx_tracker_id (tracker_id);

ALTER TABLE accounts 
    ADD COLUMN tracker_id INT AFTER user_id,
    ADD FOREIGN KEY (tracker_id) REFERENCES trackers(tracker_id) ON DELETE CASCADE,
    ADD INDEX idx_tracker_id (tracker_id);

ALTER TABLE recurring_transactions 
    ADD COLUMN tracker_id INT AFTER user_id,
    ADD FOREIGN KEY (tracker_id) REFERENCES trackers(tracker_id) ON DELETE CASCADE,
    ADD INDEX idx_tracker_id (tracker_id);

ALTER TABLE tags 
    ADD COLUMN tracker_id INT AFTER user_id,
    ADD FOREIGN KEY (tracker_id) REFERENCES trackers(tracker_id) ON DELETE CASCADE,
    ADD INDEX idx_tracker_id (tracker_id);

-- 3. Migrate existing data (create default tracker for each user)
INSERT INTO trackers (user_id, tracker_name, tracker_type, is_default)
SELECT user_id, 
       CONCAT(full_name, '''s Budget') as tracker_name,
       account_type,
       TRUE as is_default
FROM users;

-- 4. Update existing records to use the default tracker
UPDATE income_sources ics
JOIN trackers t ON ics.user_id = t.user_id AND t.is_default = TRUE
SET ics.tracker_id = t.tracker_id;

UPDATE income_transactions it
JOIN trackers t ON it.user_id = t.user_id AND t.is_default = TRUE
SET it.tracker_id = t.tracker_id;

UPDATE expense_transactions et
JOIN trackers t ON et.user_id = t.user_id AND t.is_default = TRUE
SET et.tracker_id = t.tracker_id;

UPDATE budgets b
JOIN trackers t ON b.user_id = t.user_id AND t.is_default = TRUE
SET b.tracker_id = t.tracker_id;

UPDATE financial_goals fg
JOIN trackers t ON fg.user_id = t.user_id AND t.is_default = TRUE
SET fg.tracker_id = t.tracker_id;

UPDATE accounts a
JOIN trackers t ON a.user_id = t.user_id AND t.is_default = TRUE
SET a.tracker_id = t.tracker_id;

UPDATE recurring_transactions rt
JOIN trackers t ON rt.user_id = t.user_id AND t.is_default = TRUE
SET rt.tracker_id = t.tracker_id;

UPDATE tags tg
JOIN trackers t ON tg.user_id = t.user_id AND t.is_default = TRUE
SET tg.tracker_id = t.tracker_id;

-- 5. Make tracker_id NOT NULL after migration
ALTER TABLE income_sources MODIFY tracker_id INT NOT NULL;
ALTER TABLE income_transactions MODIFY tracker_id INT NOT NULL;
ALTER TABLE expense_transactions MODIFY tracker_id INT NOT NULL;
ALTER TABLE budgets MODIFY tracker_id INT NOT NULL;
ALTER TABLE financial_goals MODIFY tracker_id INT NOT NULL;
ALTER TABLE accounts MODIFY tracker_id INT NOT NULL;
ALTER TABLE recurring_transactions MODIFY tracker_id INT NOT NULL;
ALTER TABLE tags MODIFY tracker_id INT NOT NULL;

-- Note: expense_categories stays global (shared across all trackers)
-- unless you want tracker-specific categories, then add tracker_id there too

