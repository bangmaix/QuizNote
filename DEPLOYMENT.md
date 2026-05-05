# QuizNote Server Deployment Guide

**Target Server:** 192.168.0.147 (CentOS/RHEL)  
**Install Path:** `/var/www/quiznote`  
**Database:** MySQL/MariaDB

## Prerequisites Check

Login to your server via SSH:
```bash
ssh root@192.168.0.147
```

### 1. Verify Required Software Installed

```bash
# Check PHP version (need 8.0+)
php -v

# Check if Composer is installed
composer -v

# Check if Git is installed
git --version

# Check if MySQL is installed
mysql --version

# Check if web server is running
# For Nginx:
nginx -v
# For Apache:
httpd -v
```

If any are missing, install them:

**For CentOS/RHEL:**
```bash
# Update system
yum update -y

# Install PHP 8.2 with extensions
yum install -y php php-fpm php-mysql php-mbstring php-xml php-json php-curl php-zip

# Install MySQL 8.0
yum install -y mysql-server

# Install other dependencies
yum install -y git composer nginx
```

## Step-by-Step Installation

### Step 1: Create Application Directory
```bash
mkdir -p /var/www/quiznote
cd /var/www/quiznote
```

### Step 2: Clone Repository
```bash
git clone https://github.com/bangmaix/QuizNote.git .
```

### Step 3: Install Composer Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Step 4: Setup Environment File
```bash
cp .env.example .env
php artisan key:generate
```

### Step 5: Configure Database in .env

Edit `.env` file:
```bash
nano .env
```

Update these values:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quiznote
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
APP_URL=http://quiznote.com
```

### Step 6: Create Database

```bash
# Login to MySQL
mysql -u root -p

# Inside MySQL console:
CREATE DATABASE quiznote CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 7: Run Migrations
```bash
cd /var/www/quiznote
php artisan migrate
```

### Step 8: Set File Permissions
```bash
chown -R nobody:nobody /var/www/quiznote
chmod -R 755 /var/www/quiznote
chmod -R 755 /var/www/quiznote/storage
chmod -R 755 /var/www/quiznote/bootstrap/cache
chmod 644 /var/www/quiznote/.env
```

### Step 9: Setup Web Server (Nginx)

Copy and modify Nginx config:
```bash
cp nginx.conf.example /etc/nginx/conf.d/quiznote.conf
nano /etc/nginx/conf.d/quiznote.conf
```

Update `server_name` to your domain:
```
server_name quiznote.com www.quiznote.com;
```

Test Nginx config:
```bash
nginx -t
```

Start services:
```bash
systemctl start php-fpm
systemctl start nginx
systemctl start mariadb

# Enable on boot
systemctl enable php-fpm
systemctl enable nginx
systemctl enable mariadb
```

### Step 10: Setup Storage Symlink
```bash
cd /var/www/quiznote
php artisan storage:link
```

### Step 11: Verify Installation

```bash
# Check if services are running
systemctl status php-fpm
systemctl status nginx
systemctl status mariadb

# Check file permissions
ls -la /var/www/quiznote/storage

# Test Laravel app
cd /var/www/quiznote
php artisan tinker
# Then type: exit
```

## Testing Application

1. **Access via browser:**
   - http://192.168.0.147 (or your domain)

2. **Test credentials:**
   - **Creator:** email=`creator@test.com`, password=`password123`
   - **Student:** email=`peserta@test.com`, password=`password123`

3. **Check logs:**
   ```bash
   tail -f /var/www/quiznote/storage/logs/laravel.log
   tail -f /var/log/nginx/quiznote_error.log
   ```

## SSL/HTTPS Setup (Optional but Recommended)

### Using Let's Encrypt with Certbot:
```bash
# Install Certbot
yum install -y certbot python3-certbot-nginx

# Generate certificate
certbot certonly --nginx -d quiznote.com -d www.quiznote.com

# Enable HTTPS in Nginx config - uncomment the HTTPS section
nano /etc/nginx/conf.d/quiznote.conf

# Restart Nginx
nginx -t
systemctl restart nginx

# Setup auto-renewal
systemctl enable certbot-renew.timer
```

## Database Backup

```bash
# Backup database
mysqldump -u root -p quiznote > /backup/quiznote_$(date +%Y%m%d).sql

# Restore from backup
mysql -u root -p quiznote < /backup/quiznote_YYYYMMDD.sql
```

## Troubleshooting

### Issue: Permission Denied
```bash
chown -R nobody:nobody /var/www/quiznote
chmod -R 775 /var/www/quiznote/storage
chmod -R 775 /var/www/quiznote/bootstrap/cache
```

### Issue: Connection Refused to MySQL
```bash
# Check if MySQL is running
systemctl status mariadb

# Start MySQL
systemctl start mariadb
```

### Issue: 403 Forbidden
- Check Nginx config is correct
- Verify `root` path points to `/var/www/quiznote/public`
- Check file permissions

### Issue: Blank Page
```bash
# Check error logs
tail -50 /var/www/quiznote/storage/logs/laravel.log
tail -50 /var/log/nginx/quiznote_error.log
```

### Issue: Database Connection Error
- Verify credentials in `.env`
- Check MySQL is running: `systemctl status mariadb`
- Test MySQL connection: `mysql -u root -p -h 127.0.0.1`

## Production Recommendations

1. **Keep application updated:**
   ```bash
   cd /var/www/quiznote
   git pull origin main
   composer install --no-dev
   php artisan migrate
   ```

2. **Enable SSH key authentication** (replace password)

3. **Setup firewall:**
   ```bash
   firewall-cmd --permanent --add-port=80/tcp
   firewall-cmd --permanent --add-port=443/tcp
   firewall-cmd --reload
   ```

4. **Configure automatic backups:**
   ```bash
   # Add to crontab
   crontab -e
   # Add: 0 2 * * * mysqldump -u root -p[password] quiznote > /backup/quiznote_$(date +\%Y\%m\%d).sql
   ```

5. **Monitor logs:**
   ```bash
   # Check for errors regularly
   tail -f /var/www/quiznote/storage/logs/laravel.log
   ```

## Support

If you encounter any issues, check:
1. Error logs in `/var/www/quiznote/storage/logs/`
2. Nginx logs in `/var/log/nginx/`
3. MySQL logs in `/var/log/mysql/`
4. GitHub repository documentation
