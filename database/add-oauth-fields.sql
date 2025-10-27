-- Add OAuth fields to users table
USE campus_notes_hub;

ALTER TABLE users 
ADD COLUMN google_id VARCHAR(255) DEFAULT NULL AFTER profile_picture,
ADD COLUMN oauth_provider VARCHAR(50) DEFAULT NULL AFTER google_id,
ADD COLUMN email_verified_by_oauth BOOLEAN DEFAULT FALSE AFTER oauth_provider,
ADD INDEX idx_google_id (google_id);

-- Make password nullable for OAuth users
ALTER TABLE users 
MODIFY COLUMN password VARCHAR(255) NULL;
