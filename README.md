# Asternic Call Center Stats Lite

## Overview

Asternic Call Center Stats Lite is an open-source queue reporting solution for Asterisk© PBX. It provides detailed call center statistics and reporting capabilities for organizations using Asterisk telephony systems.

## Requirements

- Linux server
- MySQL or MariaDB database
- PHP 5.4 or higher
- Web server (Apache recommended)
- Asterisk PBX with queue support

Compatible with:
- Standard LAMP stack
- FreePBX distribution
- Issabel platform

## Security Warning

**Important**: This application exposes call activity data without built-in authentication. It is recommended to:
- Implement HTTP Basic Authentication
- Use .htaccess protection
- Secure web access if the server is publicly accessible

For enhanced security, consider Asternic Call Center Stats PRO.

## Installation

### 1. Clone the repository

```bash
cd /usr/src
git clone https://github.com/asternic/asternic-stats-lite.git
```

### 2. Change to repo directory

```bash
cd asternic-stats-lite
```

### 3. Database Setup

#### Create Database
```bash
mysqladmin -u root -p create qstatslite
```

#### Create Database Tables
```bash
mysql -u root -p qstatslite < sql/qstats.sql
```

#### Create MySQL User
```bash
mysql -u root -p -e "CREATE USER 'qstatsliteuser'@'localhost' IDENTIFIED by 'somepassword'"
mysql -u root -p -e "GRANT select,insert,update,delete ON qstatslite.* TO qstatsliteuser"
```

### 4. File Placement

Move HTML directory:
```bash
mv /usr/src/asternic-stats/html /var/www/html/queue-stats
```

Move Parselog directory:
```bash
mv /usr/src/asternic-stats/parselog /usr/local/parseloglite
```

## Configuration

### Web Application Configuration
Edit `/var/www/html/queue-stats/config.php`:

```php
$dbhost = 'localhost';
$dbname = 'qstatslite';
$dbuser = 'qstatsliteuser';
$dbpass = 'somepassword';
$manager_host = '127.0.0.1';
$manager_user = 'admin';
$manager_secret = 'admin';
$language = 'es';
```

### Log Parser Configuration
Edit `/usr/local/parseloglite/config.php`:

```php
$queue_log_dir = '/var/log/asterisk/';
$queue_log_file = 'queue_log';
$dbhost = 'localhost';
$dbname = 'qstatslite';
$dbuser = 'qstatsliteuser';
$dbpass = 'somepassword';
```

## Log Parsing

Set up a cron job to parse queue logs:

```bash
crontab -e
```

Add the following line:
```
0 * * * * php -q /usr/local/parseloglite/parselog.php convertlocal
```

## Accessing Reports

Access your call center statistics by navigating to:
`http://[your-server-ip]/queue-stats`

## Troubleshooting

- Verify Asterisk Manager settings in `/etc/asterisk/manager.conf`
- Ensure MySQL credentials are correct
- Check file permissions
- Validate log file locations

## License

GPL-3.0 license

## Support

For advanced features and professional support, consider Asternic Call Center Stats PRO.
