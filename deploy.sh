#!/bin/bash

# QuizNote Deployment Script for CentOS/RHEL
# Usage: bash deploy.sh

set -e  # Exit on any error

echo "================================"
echo "QuizNote Deployment Script"
echo "================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
INSTALL_PATH="/var/www/quiznote"
APP_USER="nobody"
APP_GROUP="nobody"

echo -e "${YELLOW}[1/8] Checking system dependencies...${NC}"
# Check if running as root
if [ "$EUID" -ne 0 ]; then 
   echo -e "${RED}Error: This script must be run as root${NC}"
   exit 1
fi

# Check required commands
for cmd in git php composer mysql; do
    if ! command -v $cmd &> /dev/null; then
        echo -e "${YELLOW}Warning: $cmd not found${NC}"
    else
        echo -e "${GREEN}✓ $cmd installed${NC}"
    fi
done

echo -e "${YELLOW}[2/8] Creating application directory...${NC}"
if [ -d "$INSTALL_PATH" ]; then
    echo -e "${YELLOW}Directory exists. Backing up existing version...${NC}"
    mv "$INSTALL_PATH" "$INSTALL_PATH.backup.$(date +%Y%m%d_%H%M%S)"
fi
mkdir -p "$INSTALL_PATH"

echo -e "${YELLOW}[3/8] Cloning repository...${NC}"
cd /var/www
git clone https://github.com/bangmaix/QuizNote.git quiznote
cd "$INSTALL_PATH"

echo -e "${YELLOW}[4/8] Installing dependencies...${NC}"
composer install --no-dev --optimize-autoloader

echo -e "${YELLOW}[5/8] Setting up environment...${NC}"
cp .env.example .env
php artisan key:generate

echo "Setup complete! Follow these steps:"
echo ""
echo "1. Edit .env file:"
echo "   nano $INSTALL_PATH/.env"
echo ""
echo "2. Configure database (update these values in .env):"
echo "   DB_CONNECTION=mysql"
echo "   DB_HOST=127.0.0.1"
echo "   DB_PORT=3306"
echo "   DB_DATABASE=quiznote"
echo "   DB_USERNAME=root"
echo "   DB_PASSWORD=your_password"
echo ""
echo "3. Run migrations:"
echo "   cd $INSTALL_PATH"
echo "   php artisan migrate"
echo ""
echo "4. Set permissions:"
echo "   chown -R $APP_USER:$APP_GROUP $INSTALL_PATH"
echo "   chmod -R 755 $INSTALL_PATH"
echo "   chmod -R 755 $INSTALL_PATH/storage"
echo "   chmod -R 755 $INSTALL_PATH/bootstrap/cache"
echo ""
echo "5. For Nginx, add virtual host config:"
echo "   Create file: /etc/nginx/conf.d/quiznote.conf"
echo ""
echo "6. Start PHP-FPM and Nginx:"
echo "   systemctl restart php-fpm"
echo "   systemctl restart nginx"
echo ""
echo -e "${GREEN}Installation script completed!${NC}"
