-- Central database already created by MYSQL_DATABASE env var
-- Pre-create tenant databases for local dev (optional)
CREATE DATABASE IF NOT EXISTS `tenant_demo`;
GRANT ALL PRIVILEGES ON `tenant_%`.* TO 'procure'@'%';
FLUSH PRIVILEGES;
