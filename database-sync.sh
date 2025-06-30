#!/bin/bash

# RACIK POS Database Sync Script
# Usage: ./database-sync.sh [action] [environment]
# Actions: export, import, migrate, seed, sync
# Examples: 
#   ./database-sync.sh export local
#   ./database-sync.sh import production
#   ./database-sync.sh sync production

set -e

ACTION=${1:-sync}
ENVIRONMENT=${2:-production}
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_BACKUP_DIR="database/backups"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] ✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')] ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] ⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ❌ $1${NC}"
}

# Check if we're in Laravel root
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Please run from Laravel root directory."
    exit 1
fi

# Create backup directory
mkdir -p $DB_BACKUP_DIR

# Load environment variables
if [ -f ".env" ]; then
    source .env
else
    print_error ".env file not found!"
    exit 1
fi

# Function to export database
export_database() {
    local env=$1
    local backup_file="${DB_BACKUP_DIR}/racik_pos_${env}_${TIMESTAMP}.sql"
    
    print_info "Exporting database for $env environment..."
    
    if [ "$DB_CONNECTION" = "mysql" ]; then
        # MySQL export
        local mysql_cmd="mysqldump --single-transaction --routines --triggers"
        
        if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "localhost" ]; then
            mysql_cmd="$mysql_cmd -h $DB_HOST"
        fi
        
        if [ -n "$DB_PORT" ] && [ "$DB_PORT" != "3306" ]; then
            mysql_cmd="$mysql_cmd -P $DB_PORT"
        fi
        
        if [ -n "$DB_USERNAME" ]; then
            mysql_cmd="$mysql_cmd -u $DB_USERNAME"
        fi
        
        if [ -n "$DB_PASSWORD" ]; then
            mysql_cmd="$mysql_cmd -p$DB_PASSWORD"
        fi
        
        if [ -n "$DB_DATABASE" ]; then
            mysql_cmd="$mysql_cmd $DB_DATABASE"
        fi
        
        # Execute export
        eval "$mysql_cmd" > "$backup_file"
        
        if [ $? -eq 0 ]; then
            print_status "Database exported to: $backup_file"
            
            # Add metadata to SQL file
            sed -i '1i-- RACIK POS Database Export' "$backup_file"
            sed -i "2i-- Environment: $env" "$backup_file"
            sed -i "3i-- Export Date: $(date)" "$backup_file"
            sed -i "4i-- Database: $DB_DATABASE" "$backup_file"
            sed -i '5i-- ' "$backup_file"
            
            # Compress backup
            gzip "$backup_file"
            print_status "Database backup compressed: ${backup_file}.gz"
            
            echo "$backup_file.gz"
        else
            print_error "Database export failed!"
            exit 1
        fi
        
    elif [ "$DB_CONNECTION" = "sqlite" ]; then
        # SQLite export
        local sqlite_file="database/database.sqlite"
        if [ -f "$sqlite_file" ]; then
            cp "$sqlite_file" "$backup_file"
            print_status "SQLite database exported to: $backup_file"
            echo "$backup_file"
        else
            print_error "SQLite database file not found: $sqlite_file"
            exit 1
        fi
    else
        print_error "Unsupported database connection: $DB_CONNECTION"
        exit 1
    fi
}

# Function to import database
import_database() {
    local backup_file=$1
    
    if [ ! -f "$backup_file" ]; then
        print_error "Backup file not found: $backup_file"
        exit 1
    fi
    
    print_info "Importing database from: $backup_file"
    
    # Check if file is compressed
    if [[ "$backup_file" == *.gz ]]; then
        print_info "Decompressing backup file..."
        gunzip -k "$backup_file"
        backup_file="${backup_file%.gz}"
    fi
    
    if [ "$DB_CONNECTION" = "mysql" ]; then
        # MySQL import
        local mysql_cmd="mysql"
        
        if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "localhost" ]; then
            mysql_cmd="$mysql_cmd -h $DB_HOST"
        fi
        
        if [ -n "$DB_PORT" ] && [ "$DB_PORT" != "3306" ]; then
            mysql_cmd="$mysql_cmd -P $DB_PORT"
        fi
        
        if [ -n "$DB_USERNAME" ]; then
            mysql_cmd="$mysql_cmd -u $DB_USERNAME"
        fi
        
        if [ -n "$DB_PASSWORD" ]; then
            mysql_cmd="$mysql_cmd -p$DB_PASSWORD"
        fi
        
        if [ -n "$DB_DATABASE" ]; then
            mysql_cmd="$mysql_cmd $DB_DATABASE"
        fi
        
        # Execute import
        eval "$mysql_cmd" < "$backup_file"
        
        if [ $? -eq 0 ]; then
            print_status "Database imported successfully"
        else
            print_error "Database import failed!"
            exit 1
        fi
        
    elif [ "$DB_CONNECTION" = "sqlite" ]; then
        # SQLite import
        local sqlite_file="database/database.sqlite"
        cp "$backup_file" "$sqlite_file"
        print_status "SQLite database imported successfully"
    fi
}

# Function to run migrations
run_migrations() {
    print_info "Running database migrations..."
    
    if [ "$ENVIRONMENT" = "production" ]; then
        php artisan migrate --force
    else
        php artisan migrate
    fi
    
    if [ $? -eq 0 ]; then
        print_status "Migrations completed successfully"
    else
        print_error "Migration failed!"
        exit 1
    fi
}

# Function to run seeders
run_seeders() {
    print_info "Running database seeders..."
    
    # Check if we should run seeders based on environment
    if [ "$ENVIRONMENT" = "production" ]; then
        print_warning "Skipping seeders in production environment"
        print_info "To run seeders in production, use: php artisan db:seed --force"
    else
        php artisan db:seed
        
        if [ $? -eq 0 ]; then
            print_status "Seeders completed successfully"
        else
            print_error "Seeding failed!"
            exit 1
        fi
    fi
}

# Function to sync database (full migration + seeding)
sync_database() {
    print_info "Syncing database for $ENVIRONMENT environment..."
    
    # Backup current database first
    local current_backup=$(export_database "current")
    print_status "Current database backed up to: $current_backup"
    
    # Run fresh migrations
    if [ "$ENVIRONMENT" = "production" ]; then
        print_warning "Production environment detected"
        print_info "Running migrations only (no fresh install)"
        run_migrations
    else
        print_info "Running fresh migrations with seeding..."
        php artisan migrate:fresh --seed
        
        if [ $? -eq 0 ]; then
            print_status "Fresh database sync completed"
        else
            print_error "Database sync failed!"
            exit 1
        fi
    fi
}

# Function to list available backups
list_backups() {
    print_info "Available database backups:"
    
    if [ -d "$DB_BACKUP_DIR" ]; then
        local backups=($(ls -t "$DB_BACKUP_DIR"/racik_pos_*.sql* 2>/dev/null))
        
        if [ ${#backups[@]} -eq 0 ]; then
            print_warning "No backups found in $DB_BACKUP_DIR"
        else
            for backup in "${backups[@]}"; do
                local size=$(du -h "$backup" | cut -f1)
                local date=$(stat -c %y "$backup" 2>/dev/null || stat -f %Sm "$backup" 2>/dev/null)
                print_info "  - $(basename "$backup") ($size) - $date"
            done
        fi
    else
        print_warning "Backup directory not found: $DB_BACKUP_DIR"
    fi
}

# Function to show database status
show_db_status() {
    print_info "Database Status for $ENVIRONMENT environment:"
    print_info "  Connection: $DB_CONNECTION"
    print_info "  Host: $DB_HOST"
    print_info "  Database: $DB_DATABASE"
    print_info "  User: $DB_USERNAME"
    
    # Test connection
    if [ "$DB_CONNECTION" = "mysql" ]; then
        local test_cmd="mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD -e 'SELECT 1;' $DB_DATABASE"
        if eval "$test_cmd" >/dev/null 2>&1; then
            print_status "  Connection: ✅ Connected"
        else
            print_error "  Connection: ❌ Failed"
        fi
    fi
    
    # Show migration status
    print_info "Migration Status:"
    php artisan migrate:status
}

# Main logic
case $ACTION in
    "export")
        export_database $ENVIRONMENT
        ;;
    "import")
        if [ -z "$3" ]; then
            print_error "Usage: ./database-sync.sh import [environment] [backup_file]"
            list_backups
            exit 1
        fi
        import_database "$3"
        ;;
    "migrate")
        run_migrations
        ;;
    "seed")
        run_seeders
        ;;
    "sync")
        sync_database
        ;;
    "list")
        list_backups
        ;;
    "status")
        show_db_status
        ;;
    *)
        print_error "Invalid action: $ACTION"
        print_info "Available actions:"
        print_info "  export [env]           - Export database"
        print_info "  import [env] [file]    - Import database from backup"
        print_info "  migrate                - Run migrations"
        print_info "  seed                   - Run seeders"
        print_info "  sync [env]             - Full database sync"
        print_info "  list                   - List available backups"
        print_info "  status [env]           - Show database status"
        print_info ""
        print_info "Examples:"
        print_info "  ./database-sync.sh export local"
        print_info "  ./database-sync.sh import production backup_file.sql.gz"
        print_info "  ./database-sync.sh sync production"
        exit 1
        ;;
esac

print_status "Database operation completed successfully!"