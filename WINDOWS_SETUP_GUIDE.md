# 🪟 Windows Setup Guide - Terraponix

## 🚨 Fix Migration Error

Jika Anda mengalami error:
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'terraponix.actuator_statuses' doesn't exist
```

## 📋 Langkah Perbaikan

### Opsi 1: Menggunakan PowerShell (Recommended)
```powershell
# Buka PowerShell sebagai Administrator, navigate ke folder project
cd C:\xampp\htdocs\terraponix

# Jalankan script setup
.\setup_database.ps1
```

### Opsi 2: Menggunakan Command Prompt
```cmd
# Buka CMD, navigate ke folder project  
cd C:\xampp\htdocs\terraponix

# Jalankan script setup
setup_database_windows.bat
```

### Opsi 3: Manual Commands
```cmd
# Reset migrations
php artisan migrate:reset --force

# Run fresh migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force
```

## 🔧 Troubleshooting

### Error: "php command not found"
**Solusi:** Tambahkan PHP ke PATH Windows
1. Buka System Properties → Advanced → Environment Variables
2. Tambahkan `C:\xampp\php` ke PATH
3. Restart Command Prompt/PowerShell

### Error: Database Connection
**Solusi:** Pastikan XAMPP MySQL berjalan
1. Buka XAMPP Control Panel
2. Start Apache dan MySQL
3. Cek database `terraponix` ada di phpMyAdmin

### Error: Permission Denied
**Solusi:** Jalankan sebagai Administrator
1. Klik kanan pada PowerShell/CMD
2. Pilih "Run as Administrator"

## 📊 Verify Setup

Setelah setup berhasil, test dengan:

```cmd
# Start Laravel server
php artisan serve

# Test API endpoint
curl http://localhost:8000/api/v1/devices/1/actuator-status
```

Atau buka browser: `http://localhost:8000`

## 🎯 Expected Result

Jika berhasil, Anda akan melihat:
- ✅ Database tables created successfully
- ✅ Default device seeded
- ✅ Actuator status initialized
- ✅ Web interface accessible

## 🆘 Jika Masih Error

1. **Check Laravel Log:**
   ```cmd
   type storage\logs\laravel.log
   ```

2. **Check Database:**
   - Buka phpMyAdmin
   - Pastikan database `terraponix` exist
   - Cek tabel: `devices`, `actuator_statuses`, `actuator_logs`, `settings`

3. **Reset Complete:**
   ```cmd
   php artisan migrate:fresh --seed
   ```

## 🌟 Quick Start Commands

```cmd
# 1. Setup database  
.\setup_database.ps1

# 2. Start server
php artisan serve

# 3. Open browser
start http://localhost:8000
```

Sistem sekarang siap digunakan! 🚀