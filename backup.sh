#!/bin/bash

# RACIK POS Backup Script
# Usage: ./backup.sh [backup-type]
# Types: full, database, files
# Example: ./backup.sh full

set -e

BACKUP_TYPE=${1:-full}
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="backups"
PROJECT_NAME="racik-pos"
BACKUP_PREFIX="${PROJECT_NAME}_${BACKUP_TYPE}_${TIMESTAMP}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
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

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Please run this script from the Laravel root directory."
    exit 1
fi

# Create backup directory
mkdir -p $BACKUP_DIR

print_info "Starting $BACKUP_TYPE backup for RACIK POS..."
print_info "Backup directory: $BACKUP_DIR"
print_info "Timestamp: $TIMESTAMP"

# Load environment variables
if [ -f ".env" ]; then
    source .env
else
    print_warning ".env file not found. Using default database connection."
fi

# Function to backup database
backup_database() {
    print_info "Creating database backup..."
    
    local db_backup_file="${BACKUP_DIR}/${BACKUP_PREFIX}_database.sql"
    
    if [ -n "$DB_CONNECTION" ] && [ "$DB_CONNECTION" = "mysql" ]; then
        # MySQL backup
        local mysql_cmd="mysqldump"
        
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
        
        # Execute backup
        eval "$mysql_cmd" > "$db_backup_file"
        
        if [ $? -eq 0 ]; then
            print_status "Database backup created: $db_backup_file"
            
            # Compress database backup
            gzip "$db_backup_file"
            print_status "Database backup compressed: ${db_backup_file}.gz"
        else
            print_error "Database backup failed!"
            return 1
        fi
        
    elif [ -n "$DB_CONNECTION" ] && [ "$DB_CONNECTION" = "sqlite" ]; then
        # SQLite backup
        local sqlite_file="database/database.sqlite"
        if [ -f "$sqlite_file" ]; then
            cp "$sqlite_file" "$db_backup_file"
            print_status "SQLite database backup created: $db_backup_file"
        else
            print_warning "SQLite database file not found: $sqlite_file"
        fi
    else
        print_warning "Unsupported database connection: $DB_CONNECTION"
    fi
}

# Function to backup files
backup_files() {
    print_info "Creating files backup..."
    
    local files_backup="${BACKUP_DIR}/${BACKUP_PREFIX}_files.tar.gz"
    local exclude_patterns=(
        "node_modules"
        "vendor"
        "storage/logs"
        "storage/framework/cache"
        "storage/framework/sessions"
        "storage/framework/views"
        "bootstrap/cache"
        ".git"
        "backups"
        "*.log"
    )
    
    # Build exclude parameters
    local exclude_params=""
    for pattern in "${exclude_patterns[@]}"; do
        exclude_params="$exclude_params --exclude=$pattern"
    done
    
    # Create backup
    tar -czf "$files_backup" $exclude_params \
        --exclude-from=<(echo "backups") \
        .
    
    if [ $? -eq 0 ]; then
        print_status "Files backup created: $files_backup"
        
        # Show backup size
        local backup_size=$(du -h "$files_backup" | cut -f1)
        print_info "Backup size: $backup_size"
    else
        print_error "Files backup failed!"
        return 1
    fi
}

# Function to backup storage files
backup_storage() {
    print_info "Creating storage backup..."
    
    local storage_backup="${BACKUP_DIR}/${BACKUP_PREFIX}_storage.tar.gz"
    
    if [ -d "storage/app/public" ]; then
        tar -czf "$storage_backup" storage/app/public
        print_status "Storage backup created: $storage_backup"
    else
        print_warning "Storage directory not found: storage/app/public"
    fi
}

# Function to create environment backup
backup_env() {
    print_info "Creating environment backup..."
    
    if [ -f ".env" ]; then
        local env_backup="${BACKUP_DIR}/${BACKUP_PREFIX}_env.txt"
        cp .env "$env_backup"
        print_status "Environment backup created: $env_backup"
    else
        print_warning ".env file not found"
    fi
}

# Function to create application info backup
backup_app_info() {
    print_info "Creating application info backup..."
    
    local info_backup="${BACKUP_DIR}/${BACKUP_PREFIX}_info.json"
    
    # Create info JSON
    cat > "$info_backup" << EOF
{
    "backup_date": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
    "backup_type": "$BACKUP_TYPE",
    "project_name": "$PROJECT_NAME",
    "php_version": "$(php -v | head -n 1)",
    "composer_version": "$(composer --version 2>/dev/null || echo 'Not available')",
    "npm_version": "$(npm --version 2>/dev/null || echo 'Not available')",
    "laravel_version": "$(php artisan --version 2>/dev/null || echo 'Not available')",
    "git_commit": "$(git rev-parse HEAD 2>/dev/null || echo 'Not available')",
    "git_branch": "$(git branch --show-current 2>/dev/null || echo 'Not available')",
    "environment": "$APP_ENV",
    "app_url": "$APP_URL"
}
EOF
    
    print_status "Application info backup created: $info_backup"
}

# Function to cleanup old backups
cleanup_old_backups() {
    local days_to_keep=${BACKUP_RETENTION_DAYS:-30}
    
    print_info "Cleaning up backups older than $days_to_keep days..."
    
    # Remove files older than retention period
    find "$BACKUP_DIR" -name "${PROJECT_NAME}_*" -type f -mtime +$days_to_keep -delete
    
    print_status "Old backups cleaned up"
}

# Function to verify backup integrity
verify_backup() {
    print_info "Verifying backup integrity..."
    
    local verification_failed=false
    
    # Check if backup files exist and are not empty
    for backup_file in "${BACKUP_DIR}/${BACKUP_PREFIX}"*; do
        if [ -f "$backup_file" ]; then
            if [ -s "$backup_file" ]; then
                print_status "✓ $(basename "$backup_file") - OK"
            else
                print_error "✗ $(basename "$backup_file") - Empty file"
                verification_failed=true
            fi
        fi
    done
    
    if [ "$verification_failed" = true ]; then
        print_error "Backup verification failed!"
        return 1
    else
        print_status "All backups verified successfully"
    fi
}

# Main backup logic
case $BACKUP_TYPE in
    "full")
        print_info "Performing full backup..."
        backup_database
        backup_files
        backup_storage
        backup_env
        backup_app_info
        ;;
    "database")
        print_info "Performing database backup..."
        backup_database
        backup_app_info
        ;;
    "files")
        print_info "Performing files backup..."
        backup_files
        backup_storage
        backup_env
        backup_app_info
        ;;
    *)
        print_error "Invalid backup type: $BACKUP_TYPE"
        print_info "Available types: full, database, files"
        exit 1
        ;;
esac

# Verify backup
verify_backup

# Cleanup old backups
cleanup_old_backups

# Final summary
print_status "Backup completed successfully!"
print_info "Backup files location: $BACKUP_DIR"
print_info "Backup prefix: $BACKUP_PREFIX"

# List created backups
print_info "Created backups:"
for backup_file in "${BACKUP_DIR}/${BACKUP_PREFIX}"*; do
    if [ -f "$backup_file" ]; then
        local file_size=$(du -h "$backup_file" | cut -f1)
        print_info "  - $(basename "$backup_file") ($file_size)"
    fi
done

print_info "Backup process completed at $(date)"