# Database Management Guide - RACIK POS

## 📋 Overview

Panduan ini menjelaskan cara mengelola database dalam workflow CI/CD, menggantikan proses manual export/import melalui phpMyAdmin.

## 🔄 Workflow Database yang Direkomendasikan

### 1. Development ke Production (Initial Setup)

```bash
# 1. Export database dari local
./database-sync.sh export local

# 2. Upload backup ke server production
# 3. Import di server production
./database-sync.sh import production racik_pos_local_20241230_120000.sql.gz
```

### 2. Automated Migration (Setelah setup CI/CD)

Database akan otomatis ter-migrate saat deployment melalui GitHub Actions:

```yaml
# Di workflow sudah include:
- Database backup sebelum migration
- Migration dengan --force flag
- Error handling jika migration gagal
```

## 🛠️ Database Sync Script Usage

### Export Database

```bash
# Export database local
./database-sync.sh export local

# Export database production (jika akses SSH tersedia)
./database-sync.sh export production
```

### Import Database

```bash
# Import ke database local
./database-sync.sh import local backup_file.sql.gz

# Import ke database production
./database-sync.sh import production backup_file.sql.gz
```

### Full Database Sync

```bash
# Sync database (fresh install dengan seed)
./database-sync.sh sync development

# Sync database production (hanya migration)
./database-sync.sh sync production
```

### Utility Commands

```bash
# List semua backup yang tersedia
./database-sync.sh list

# Cek status database dan koneksi
./database-sync.sh status production

# Run migration saja
./database-sync.sh migrate

# Run seeder saja (tidak untuk production)
./database-sync.sh seed
```

## 📊 Migration Strategy

### 1. Local Development

```bash
# Fresh install dengan sample data
php artisan migrate:fresh --seed

# Atau menggunakan script
./database-sync.sh sync development
```

### 2. Production Deployment

```bash
# Hanya migration, tidak menghapus data existing
php artisan migrate --force

# Atau menggunakan script
./database-sync.sh migrate
```

### 3. Staging Environment

```bash
# Bisa menggunakan fresh install atau migration
./database-sync.sh sync staging
```

## 🔐 Environment Configuration

### Local Environment (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=racik_pos_local
DB_USERNAME=root
DB_PASSWORD=
```

### Production Environment (.env)

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpanel_username_racikpos
DB_USERNAME=cpanel_username_dbuser
DB_PASSWORD=strong_password_here
```

## 📁 Backup Management

### Automatic Backups

1. **Pre-deployment backup** (via GitHub Actions)
2. **Scheduled backup** (via cron job)
3. **Manual backup** (via script)

### Backup Location

```
database/backups/
├── racik_pos_local_20241230_120000.sql.gz
├── racik_pos_production_20241230_130000.sql.gz
├── pre_deploy_20241230_140000.sql
└── ...
```

### Backup Retention

- Default: 30 hari
- Configurable via environment variable: `BACKUP_RETENTION_DAYS=60`

## 🚀 Initial Setup Process

### 1. Local ke Production (Pertama Kali)

```bash
# Step 1: Export database local dengan data lengkap
./database-sync.sh export local

# Step 2: Upload file backup ke server
# Bisa via FTP, cPanel File Manager, atau scp

# Step 3: Login SSH ke server dan import
cd /path/to/project
./database-sync.sh import production racik_pos_local_TIMESTAMP.sql.gz

# Step 4: Verify import berhasil
./database-sync.sh status production
php artisan migrate:status
```

### 2. Setup CI/CD Database Environment Variables

Tambahkan ke GitHub Secrets:

```
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

### 3. Setup Cron Job untuk Automated Backup

```bash
# Daily backup at 2 AM
0 2 * * * cd /path/to/project && ./database-sync.sh export production

# Weekly cleanup of old backups
0 3 * * 0 cd /path/to/project && find database/backups -name "*.sql*" -mtime +30 -delete
```

## 🔧 Troubleshooting

### Migration Errors

```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Reset migrations (DANGER: akan hapus semua data)
php artisan migrate:fresh --seed
```

### Connection Errors

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check database configuration
php artisan config:show database.connections.mysql
```

### Permission Errors

```bash
# Fix database file permissions
chmod 644 database/database.sqlite
chown www-data:www-data database/database.sqlite

# Fix backup directory permissions
chmod 755 database/backups
```

## 📈 Best Practices

### 1. Development Workflow

```bash
# 1. Pull latest code
git pull origin main

# 2. Update database
./database-sync.sh sync development

# 3. Start development
```

### 2. Production Deployment

```bash
# 1. Backup production database
./database-sync.sh export production

# 2. Deploy via GitHub Actions (otomatis backup + migrate)
git push origin main

# 3. Verify deployment
./database-sync.sh status production
```

### 3. Rollback Strategy

```bash
# Jika migration gagal, restore dari backup
./database-sync.sh import production pre_deploy_TIMESTAMP.sql

# Rollback migration tertentu
php artisan migrate:rollback --step=1
```

## 🔍 Monitoring

### Database Health Check

```bash
# Via script
./database-sync.sh status production

# Via Laravel
php artisan about --only=database
```

### Backup Monitoring

```bash
# List backups
./database-sync.sh list

# Check backup sizes
du -sh database/backups/*
```

## 🆘 Emergency Procedures

### Database Corruption

```bash
# 1. Stop application (maintenance mode)
php artisan down

# 2. Restore from latest backup
./database-sync.sh import production latest_backup.sql.gz

# 3. Verify data integrity
php artisan migrate:status

# 4. Bring application back online
php artisan up
```

### Failed Migration

```bash
# 1. Check error logs
tail -f storage/logs/laravel.log

# 2. Rollback to previous state
php artisan migrate:rollback

# 3. Fix migration file

# 4. Re-run migration
php artisan migrate
```

## 📞 Support

Jika mengalami masalah dengan database:

1. Check logs: `storage/logs/laravel.log`
2. Verify connection: `./database-sync.sh status`
3. Check migration status: `php artisan migrate:status`
4. Contact hosting support jika masalah server