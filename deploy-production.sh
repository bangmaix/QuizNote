#!/bin/bash

################################################################################
# QuizNote Complete Deployment Script for CentOS/RHEL
# This script automates the entire deployment process
# Usage: sudo bash deploy-production.sh
################################################################################

set -e  # Exit on any error

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
INSTALL_PATH="/var/www/quiznote"
APP_USER="nobody"
APP_GROUP="nobody"
DB_NAME="quiznote"
DB_USER="quiznote_user"
DB_PASSWORD="QuizNote@2026"  # Change this!
REPO_URL="https://github.com/bangmaix/QuizNote.git"

################################################################################
# Function: Print colored output
################################################################################
print_header() {
    echo -e "\n${BLUE}================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}================================${NC}\n"
}

print_step() {
    echo -e "${YELLOW}[STEP] $1${NC}"
}

print_success() {
    echo -e "${GREEN}[✓] $1${NC}"
}

print_error() {
    echo -e "${RED}[✗] $1${NC}"
}

################################################################################
# Function: Check prerequisites
################################################################################
check_prerequisites() {
    print_header "CHECKING PREREQUISITES"
    
    # Check if root
    if [ "$EUID" -ne 0 ]; then 
        print_error "This script must be run as root (use: sudo bash deploy-production.sh)"
        exit 1
    fi
    print_success "Running as root"
    
    # Check OS
    if [ -f /etc/redhat-release ]; then
        print_success "CentOS/RHEL detected"
    else
        print_error "This script is designed for CentOS/RHEL"
        exit 1
    fi
    
    # Check required commands
    local required_cmds=("git" "php" "composer" "mysql" "nginx" "systemctl")
    for cmd in "${required_cmds[@]}"; do
        if command -v $cmd &> /dev/null; then
            print_success "$cmd is installed"
        else
            print_error "$cmd is NOT installed - aborting"
            exit 1
        fi
    done
    
    # Check PHP version
    local php_version=$(php -v | head -1 | grep -oP '\d+\.\d+' | head -1)
    print_success "PHP version: $php_version"
}

################################################################################
# Function: Create directory structure
################################################################################
setup_directory() {
    print_header "SETTING UP DIRECTORY"
    
    if [ -d "$INSTALL_PATH" ]; then
        print_step "Backing up existing installation..."
        local backup_dir="$INSTALL_PATH.backup.$(date +%Y%m%d_%H%M%S)"
        mv "$INSTALL_PATH" "$backup_dir"
        print_success "Backup created: $backup_dir"
    fi
    
    mkdir -p "$INSTALL_PATH"
    cd "$INSTALL_PATH"
    print_success "Directory created: $INSTALL_PATH"
}

################################################################################
# Function: Clone repository
################################################################################
clone_repository() {
    print_header "CLONING REPOSITORY"
    
    print_step "Cloning from $REPO_URL..."
    git clone "$REPO_URL" .
    print_success "Repository cloned"
    
    print_step "Current commit:"
    git log --oneline | head -1
}

################################################################################
# Function: Install dependencies
################################################################################
install_dependencies() {
    print_header "INSTALLING DEPENDENCIES"
    
    print_step "Installing PHP dependencies via Composer..."
    composer install --no-dev --optimize-autoloader
    print_success "Dependencies installed"
}

################################################################################
# Function: Setup environment
################################################################################
setup_environment() {
    print_header "SETTING UP ENVIRONMENT"
    
    print_step "Copying .env file..."
    cp .env.example .env
    
    print_step "Generating application key..."
    php artisan key:generate
    
    print_step "Updating .env with configuration..."
    # Update database configuration
    sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
    sed -i "s/DB_HOST=.*/DB_HOST=127.0.0.1/" .env
    sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
    
    print_success "Environment configured"
    print_step "Generated APP_KEY: $(grep APP_KEY .env)"
}

################################################################################
# Function: Setup database
################################################################################
setup_database() {
    print_header "SETTING UP DATABASE"
    
    print_step "Creating database user and granting privileges..."
    mysql -u root << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    print_success "Database created and user configured"
    
    print_step "Running migrations..."
    php artisan migrate --force
    print_success "Migrations completed"
}

################################################################################
# Function: Set file permissions
################################################################################
set_permissions() {
    print_header "SETTING FILE PERMISSIONS"
    
    print_step "Changing ownership..."
    chown -R "$APP_USER:$APP_GROUP" "$INSTALL_PATH"
    
    print_step "Setting directory permissions..."
    chmod -R 755 "$INSTALL_PATH"
    chmod -R 775 "$INSTALL_PATH/storage"
    chmod -R 775 "$INSTALL_PATH/bootstrap/cache"
    chmod 644 "$INSTALL_PATH/.env"
    
    print_success "Permissions set correctly"
}

################################################################################
# Function: Setup storage symlink
################################################################################
setup_storage_link() {
    print_header "SETTING UP STORAGE SYMLINK"
    
    print_step "Creating storage symlink..."
    php artisan storage:link
    print_success "Storage symlink created"
}

################################################################################
# Function: Setup Nginx
################################################################################
setup_nginx() {
    print_header "SETTING UP NGINX"
    
    print_step "Copying Nginx configuration..."
    cp nginx.conf.example /etc/nginx/conf.d/quiznote.conf
    
    print_step "Testing Nginx configuration..."
    nginx -t || {
        print_error "Nginx configuration test failed"
        exit 1
    }
    print_success "Nginx configuration is valid"
    
    print_step "Restarting Nginx..."
    systemctl restart nginx
    print_success "Nginx restarted"
}

################################################################################
# Function: Start services
################################################################################
start_services() {
    print_header "STARTING SERVICES"
    
    local services=("php-fpm" "nginx" "mariadb")
    
    for service in "${services[@]}"; do
        print_step "Starting $service..."
        systemctl start "$service"
        systemctl enable "$service"
        
        if systemctl is-active --quiet "$service"; then
            print_success "$service is running"
        else
            print_error "$service failed to start"
            exit 1
        fi
    done
}

################################################################################
# Function: Verify installation
################################################################################
verify_installation() {
    print_header "VERIFYING INSTALLATION"
    
    # Check Laravel artisan works
    print_step "Testing Laravel installation..."
    cd "$INSTALL_PATH"
    php artisan --version
    print_success "Laravel is working"
    
    # Check database connection
    print_step "Testing database connection..."
    php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection OK\n';" || {
        print_error "Database connection failed"
        exit 1
    }
    print_success "Database connection verified"
    
    # Check file permissions
    print_step "Verifying file permissions..."
    ls -ld storage bootstrap/cache | awk '{print $1, $3, $4, $9}'
    print_success "File permissions verified"
}

################################################################################
# Function: Print summary
################################################################################
print_summary() {
    print_header "DEPLOYMENT COMPLETE"
    
    echo -e "${GREEN}Your QuizNote application is now running!${NC}\n"
    
    echo -e "${BLUE}Access Information:${NC}"
    echo "  URL: http://192.168.0.147"
    echo "  Or configure domain: /etc/nginx/conf.d/quiznote.conf"
    echo ""
    
    echo -e "${BLUE}Test Credentials:${NC}"
    echo "  Creator: creator@test.com / password123"
    echo "  Student: peserta@test.com / password123"
    echo ""
    
    echo -e "${BLUE}Important Paths:${NC}"
    echo "  Installation: $INSTALL_PATH"
    echo "  Logs: $INSTALL_PATH/storage/logs/laravel.log"
    echo "  Nginx config: /etc/nginx/conf.d/quiznote.conf"
    echo ""
    
    echo -e "${BLUE}Useful Commands:${NC}"
    echo "  View logs: tail -f $INSTALL_PATH/storage/logs/laravel.log"
    echo "  Restart app: systemctl restart php-fpm"
    echo "  Update app: cd $INSTALL_PATH && git pull && composer install && php artisan migrate"
    echo ""
    
    echo -e "${BLUE}Security Notes:${NC}"
    echo "  ⚠️  Change DB_PASSWORD in .env: $INSTALL_PATH/.env"
    echo "  ⚠️  Setup SSL/HTTPS with Let's Encrypt"
    echo "  ⚠️  Configure firewall rules"
    echo "  ⚠️  Setup regular database backups"
    echo ""
    
    echo -e "${GREEN}================================${NC}"
    echo -e "${GREEN}Ready to use!${NC}"
    echo -e "${GREEN}================================${NC}\n"
}

################################################################################
# MAIN EXECUTION
################################################################################

print_header "QUIZNOTE PRODUCTION DEPLOYMENT"
echo "Starting deployment process..."
echo "Install path: $INSTALL_PATH"
echo "Database: $DB_NAME"
echo ""

# Run all setup steps
check_prerequisites
setup_directory
clone_repository
install_dependencies
setup_environment
setup_database
set_permissions
setup_storage_link
setup_nginx
start_services
verify_installation
print_summary

exit 0
