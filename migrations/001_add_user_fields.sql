-- Adds missing columns for users table safely
-- Note: MySQL before 8.0 does not support IF NOT EXISTS for ADD COLUMN.
-- These statements will error if the column exists; the runner logs and continues.
ALTER TABLE usuarios ADD COLUMN fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE usuarios ADD COLUMN rol ENUM('admin','cliente') DEFAULT 'cliente';
ALTER TABLE usuarios ADD COLUMN password_reset_token VARCHAR(64) DEFAULT NULL;
ALTER TABLE usuarios ADD COLUMN password_reset_expires DATETIME DEFAULT NULL;
