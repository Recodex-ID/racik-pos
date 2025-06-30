# cPanel Git Version Control Setup Guide

## 📋 Overview

Panduan ini menjelaskan cara menggunakan fitur Git Version Control di cPanel untuk automated deployment RACIK POS. Ini adalah cara yang lebih mudah dibanding manual FTP atau SSH.

## 🎯 Keunggulan cPanel Git

✅ **Built-in di cPanel** - Tidak perlu setup GitHub Actions  
✅ **One-click deployment** - Cukup klik "Update from Remote"  
✅ **Automated build process** - Via file `.cpanel.yml`  
✅ **Direct server access** - Tidak perlu FTP/SSH credentials  
✅ **Real-time logs** - Lihat proses deployment langsung  

## 🛠️ Setup Instructions

### 1. Initial Setup (Sudah Dilakukan)

Berdasarkan screenshot Anda, setup sudah benar:
- ✅ Repository Path: `/home/recodexi/public_html/subdomain/racik-pos.web.id`
- ✅ Remote URL: `https://github.com/Recodex-ID/racik-pos.git`
- ✅ Branch: `main`

### 2. Setup Environment File

Buat file `.env` di server melalui cPanel File Manager:

```bash
# Path: /home/recodexi/public_html/subdomain/racik-pos.web.id/.env
```

Copy dari `.env.example` dan sesuaikan:

```env
APP_NAME="RACIK POS"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://racik-pos.web.id

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=recodexi_racikpos
DB_USERNAME=recodexi_dbuser
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mail.racik-pos.web.id
MAIL_PORT=587
MAIL_USERNAME=noreply@racik-pos.web.id
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@racik-pos.web.id
MAIL_FROM_NAME="RACIK POS"
```

### 3. Generate Application Key

Login via SSH atau Terminal di cPanel dan jalankan:

```bash
cd /home/recodexi/public_html/subdomain/racik-pos.web.id
php artisan key:generate
```

### 4. Setup Database

1. **Buat Database di cPanel:**
   - MySQL Databases → Create New Database
   - Nama: `recodexi_racikpos`

2. **Buat User Database:**
   - Add New User: `recodexi_dbuser`
   - Password: strong password

3. **Assign User ke Database:**
   - Add User to Database
   - Grant ALL PRIVILEGES

4. **Initial Data Import:**
   ```bash
   # Export dari local terlebih dahulu
   ./database-sync.sh export local
   
   # Upload file hasil export ke server
   # Via cPanel File Manager ke folder: database/backups/
   
   # Import di server
   cd /home/recodexi/public_html/subdomain/racik-pos.web.id
   ./database-sync.sh import production database/backups/racik_pos_local_TIMESTAMP.sql.gz
   ```

## 🚀 Deployment Process

### Method 1: cPanel Git (Recommended)

1. **Push changes ke GitHub:**
   ```bash
   git add .
   git commit -m "Update: fitur baru"
   git push origin main
   ```

2. **Deploy via cPanel:**
   - Login ke cPanel
   - Git Version Control
   - Klik "Update from Remote"
   - Tunggu proses selesai

### Method 2: Webhook (Optional)

Setup webhook di GitHub repository:
- URL: `https://racik-pos.web.id/webhook.php`
- Secret: set di environment variable
- Events: Push events

## 📁 File Structure di Server

```
/home/recodexi/public_html/subdomain/racik-pos.web.id/
├── .cpanel.yml                 # Deployment configuration
├── .env                        # Environment variables (buat manual)
├── app/                        # Laravel application
├── bootstrap/
├── config/
├── database/
│   └── backups/               # Database backups
├── public/                    # Web accessible files
├── resources/
├── routes/
├── storage/
│   └── logs/
│       └── deployment.log     # Deployment logs
├── vendor/                    # Will be created by composer
└── ...
```

## 🔧 Troubleshooting

### Error: "The system cannot deploy"

**Penyebab:** File `.cpanel.yml` tidak ada atau tidak valid

**Solusi:**
1. Pastikan file `.cpanel.yml` sudah di-commit ke repository
2. Push changes ke GitHub
3. Coba "Update from Remote" lagi

### Error: "No uncommitted changes exist"

**Penyebab:** Ada file yang di-modify di server

**Solusi:**
```bash
# Via SSH atau Terminal
cd /home/recodexi/public_html/subdomain/racik-pos.web.id
git status
git stash  # Simpan perubahan lokal
git pull origin main
```

### Error: Composer Install Failed

**Penyebab:** Memory limit atau timeout

**Solusi:**
1. Increase PHP memory limit di cPanel
2. Atau install composer dependencies secara manual:
   ```bash
   /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader
   ```

### Error: NPM Build Failed

**Penyebab:** Node.js tidak tersedia atau versi lama

**Solusi:**
1. Check Node.js version di cPanel
2. Atau build assets di local dan commit:
   ```bash
   npm run build
   git add public/build
   git commit -m "Add built assets"
   git push origin main
   ```

### Error: Permission Denied

**Penyebab:** File permissions tidak tepat

**Solusi:**
```bash
chmod -R 775 storage bootstrap/cache
chmod +x database-sync.sh deploy.sh backup.sh
```

### Error: Database Connection

**Penyebab:** Kredensial database salah

**Solusi:**
1. Verify database credentials di cPanel
2. Update file `.env`
3. Test connection:
   ```bash
   php artisan migrate:status
   ```

## 📊 Monitoring

### 1. Deployment Logs

```bash
# Check deployment logs
tail -f storage/logs/deployment.log

# Check Laravel logs
tail -f storage/logs/laravel.log
```

### 2. Application Status

```bash
# Check application status
php artisan about

# Check database
php artisan migrate:status

# Check cache
php artisan cache:clear
```

### 3. Performance

```bash
# Check disk usage
du -sh *

# Check memory usage
free -h

# Check processes
ps aux | grep php
```

## 🔄 Workflow Examples

### 1. Feature Development

```bash
# Local development
git checkout -b feature/new-feature
# ... develop feature ...
git add .
git commit -m "feat: implement new feature"

# Merge to main
git checkout main
git merge feature/new-feature
git push origin main

# Deploy via cPanel
# → Login cPanel → Git Version Control → Update from Remote
```

### 2. Hotfix

```bash
# Quick fix
git add .
git commit -m "fix: critical bug"
git push origin main

# Deploy immediately
# → cPanel → Update from Remote
```

### 3. Database Changes

```bash
# Create migration
php artisan make:migration add_new_column_to_table

# Test locally
php artisan migrate

# Push to production
git add .
git commit -m "database: add new column"
git push origin main

# Deploy (migration will run automatically)
# → cPanel → Update from Remote
```

## 📋 Best Practices

### 1. Always Test Locally First
```bash
# Test semua perubahan di local
php artisan test
npm run build
```

### 2. Database Backup
```bash
# Backup otomatis via .cpanel.yml
# Manual backup jika perlu:
./database-sync.sh export production
```

### 3. Environment-specific Config
```bash
# Development
APP_ENV=local
APP_DEBUG=true

# Production  
APP_ENV=production
APP_DEBUG=false
```

### 4. Monitor After Deployment
```bash
# Check logs after deployment
tail -f storage/logs/laravel.log

# Verify application works
curl -I https://racik-pos.web.id
```

## 🆘 Emergency Rollback

Jika deployment bermasalah:

```bash
# Via SSH
cd /home/recodexi/public_html/subdomain/racik-pos.web.id

# Rollback to previous commit
git log --oneline -5  # See recent commits
git reset --hard HEAD~1  # Rollback 1 commit

# Restore database if needed
./database-sync.sh import production database/backups/pre_deploy_TIMESTAMP.sql
```

Dengan setup ini, deployment jadi sangat mudah - cukup push ke GitHub dan klik "Update from Remote" di cPanel! 🚀