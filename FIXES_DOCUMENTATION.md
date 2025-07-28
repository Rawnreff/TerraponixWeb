# 🔧 Terraponix System Fixes Documentation

## 📋 Masalah yang Diperbaiki

### 1. **UI Dropdown Tidak Stabil di Settings**

**Masalah:**
- Device selection dropdown reset setiap 30 detik
- User selection tidak dipertahankan saat auto-refresh
- Auto-select logic conflict dengan user selection

**Perbaikan:**
- Menambahkan flag `isUserSelecting` untuk mencegah auto-reset selama user berinteraksi
- Menyimpan dan mempertahankan selection saat refresh devices
- Mengubah auto-refresh interval menjadi lebih responsif (3 detik untuk actuator status)
- Menambahkan `clearForm()` function untuk reset yang proper

**File yang diubah:**
- `resources/views/settings.blade.php`

### 2. **API Actuator Control Tidak Berfungsi**

**Masalah:**
- Route mismatch antara frontend dan backend
- Missing `auto_mode` field di `ActuatorStatus` model
- Inconsistent API endpoints

**Perbaikan:**
- Menambahkan field `auto_mode` ke model `ActuatorStatus`
- Membuat migration untuk menambahkan field `auto_mode`
- Memperbaiki API routes dengan prefix yang konsisten
- Membuat bridge controller untuk backward compatibility
- Menambahkan auto mode toggle functionality

**File yang diubah:**
- `app/Models/ActuatorStatus.php`
- `app/Http/Controllers/Api/ActuatorController.php`
- `app/Http/Controllers/ActuatorController.php`
- `routes/api.php`
- `database/migrations/2025_01_20_120000_add_auto_mode_to_actuator_statuses_table.php`

### 3. **Actuator Logs Tidak Ditampilkan**

**Masalah:**
- Logs hanya disimpan di client-side JavaScript
- Tidak ada persistence di database
- Tidak ada API endpoint untuk logs

**Perbaikan:**
- Membuat model `ActuatorLog` untuk menyimpan logs di database
- Membuat migration untuk tabel `actuator_logs`
- Menambahkan API endpoint untuk mengambil logs
- Mengubah frontend untuk load logs dari database
- Auto-refresh logs setiap 5 detik

**File yang dibuat:**
- `app/Models/ActuatorLog.php`
- `database/migrations/2025_01_20_120001_create_actuator_logs_table.php`

**File yang diubah:**
- `resources/views/actuator-control.blade.php`
- `app/Http/Controllers/Api/ActuatorController.php`
- `routes/api.php`

### 4. **Settings Auto-Refresh Tidak Real-time**

**Masalah:**
- Interval terlalu panjang (10 detik)
- Data tidak synchronized dengan database changes

**Perbaikan:**
- Mengubah interval menjadi 3 detik untuk actuator status
- Parallel loading untuk settings dan actuator status
- Real-time sync dengan ESP32 changes
- Menambahkan error handling yang lebih baik

**File yang diubah:**
- `resources/views/settings.blade.php`

## 🗂️ File Baru yang Dibuat

1. **Models:**
   - `app/Models/ActuatorLog.php` - Model untuk logging actuator operations

2. **Migrations:**
   - `database/migrations/2025_01_20_120000_add_auto_mode_to_actuator_statuses_table.php`
   - `database/migrations/2025_01_20_120001_create_actuator_logs_table.php`

3. **Seeders:**
   - `database/seeders/ActuatorStatusSeeder.php` - Seeder untuk default actuator status

4. **Scripts:**
   - `setup_database.sh` - Script untuk setup database

## 🔗 API Endpoints Baru

### Actuator Control
- `GET /api/v1/devices/{id}/actuator-status` - Get actuator status
- `POST /api/v1/actuator/control` - Control actuators
- `GET /api/v1/devices/{id}/actuator-logs` - Get actuator logs
- `POST /api/v1/actuator/auto-mode` - Toggle auto mode

### Backward Compatibility
- `POST /api/control-actuator` - Bridge endpoint untuk web frontend lama

## 🎯 Kompatibilitas dengan ESP32

Sistem tetap kompatibel dengan kode ESP32 yang ada:

```cpp
// ESP32 tetap menggunakan endpoint yang sama
String url = String(serverUrl) + "/devices/" + String(deviceId) + "/actuator-status";
```

Auto mode sekarang disinkronkan antara:
- ESP32 Hardware
- Database (actuator_statuses table)
- Web Frontend Real-time

## 🚀 Cara Menjalankan Perbaikan

1. **Setup Database:**
   ```bash
   ./setup_database.sh
   ```

2. **Start Server:**
   ```bash
   php artisan serve
   ```

3. **Test API:**
   ```bash
   curl http://localhost:8000/api/v1/devices/1/actuator-status
   ```

## 📊 Struktur Database Baru

### actuator_statuses table (updated)
```sql
- device_id (foreign key)
- curtain_position (0-100)
- fan_status (boolean)
- water_pump_status (boolean)
- auto_mode (boolean) <- BARU
- last_updated (timestamp)
```

### actuator_logs table (new)
```sql
- id (primary key)
- device_id (foreign key)
- actuator_type (enum: curtain, fan, water_pump, system)
- action (string: action description)
- value (string: value set)
- timestamp (timestamp)
```

## ✅ Testing Checklist

- [ ] Device dropdown stable di settings page
- [ ] Actuator control berfungsi dari settings page
- [ ] Actuator control berfungsi dari actuator-control page
- [ ] Logs muncul dan terupdate real-time
- [ ] Auto mode toggle berfungsi
- [ ] ESP32 dapat mengambil actuator status
- [ ] ESP32 dapat mengirim sensor data
- [ ] Real-time updates (3 detik interval)

## 🔧 Troubleshooting

### Jika Migration Gagal
```bash
php artisan migrate:reset
php artisan migrate
php artisan db:seed
```

### Jika API Error 404
- Pastikan routes di `routes/api.php` sudah benar
- Check namespace di controllers
- Verify model relationships

### Jika Frontend Tidak Update
- Clear browser cache
- Check browser console untuk JavaScript errors
- Verify API endpoints response

## 📝 Notes untuk Developer

1. **Auto Mode Logic:** Auto mode disimpan di `actuator_statuses` table dan disinkronkan real-time
2. **Logging:** Semua actuator operations dicatat di `actuator_logs` table
3. **Real-time:** Frontend menggunakan interval polling untuk real-time updates
4. **Backward Compatibility:** Bridge endpoints tersedia untuk frontend lama
5. **ESP32 Integration:** API endpoints kompatibel dengan kode ESP32 yang ada