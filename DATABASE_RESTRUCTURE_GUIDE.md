# 🔄 Database Restructure - Backup & Migration Guide

## ⚠️ PENTING: WAJIB BACKUP SEBELUM MIGRATE!

### 1. Backup Database (Wajib!)

#### Untuk Windows (PowerShell):
```powershell
# Masuk ke direktori backup
cd C:\laragon\www\webtelkom\MagangTelkom-Dashboard\database\backups

# Buat folder backup jika belum ada
New-Item -ItemType Directory -Force -Path "backups"

# Backup database (ganti password jika ada)
C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe -u root -P 3309 --databases telkomtws > "backup_before_restructure_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
```

#### Alternatif via Laragon/HeidiSQL:
1. Buka HeidiSQL dari Laragon
2. Klik kanan database `telkomtws`
3. Pilih "Export database as SQL"
4. Save dengan nama: `backup_before_restructure_YYYYMMDD.sql`

---

### 2. Verifikasi Backup
```powershell
# Cek ukuran file backup
Get-ChildItem .\database\backups\*.sql | Sort-Object LastWriteTime -Descending | Select-Object Name, Length, LastWriteTime
```

---

### 3. Jalankan Migration

**⚠️ JANGAN JALANKAN DI PRODUCTION TANPA BACKUP!**

```powershell
# Step 1: Drop old tables dan foreign keys
php artisan migrate --path=database/migrations/2025_11_12_000001_drop_old_tables_and_prepare_restructure.php

# Step 2-10: Jalankan migration berikutnya (akan dibuat bertahap)
php artisan migrate
```

---

### 4. Rollback (Jika Ada Masalah)

```powershell
# Rollback 1 step
php artisan migrate:rollback --step=1

# Atau restore dari backup
mysql -u root -P 3309 telkomtws < database/backups/backup_before_restructure_YYYYMMDD.sql
```

---

## 📋 Checklist Sebelum Migrate

- [ ] ✅ Database sudah di-backup
- [ ] ✅ File backup sudah diverifikasi (ukuran > 0 KB)
- [ ] ✅ Development environment (bukan production)
- [ ] ✅ Semua tim sudah koordinasi
- [ ] ✅ Node.js sudah update ke versi yang sesuai
- [ ] ✅ Composer dependencies sudah ter-install

---

## 🗂️ Struktur Database Lama vs Baru

### Tables yang Dihapus:
- ❌ `company_regions` → Diganti dengan `account_manager_company` (pivot baru)

### Tables yang Di-Restructure:
- 🔄 `companies` → Primary key: `nip_nas` (bukan `id`), hapus `primary_region_id`, `primary_witel_id`
- 🔄 `regions` → Tambah ENUM `code`, field `description`
- 🔄 `witels` → Primary key: `idwitels`, tambah `region_id` FK
- 🔄 `revenues` → Hapus `region_id`, `witel_id`, pertahankan `company_id` FK

### Tables Baru:
- ✨ `account_managers` (nik PK, posisi ENUM, idwitels FK)
- ✨ `lini_waktu` (quartal, bulan_awal, bulan_akhir, tahun, nik_am FK)
- ✨ `target_account_m` (semua field target: t_revenue, t_scalling, dll)
- ✨ `account_manager_company` (pivot: proporsi, pembagian, segment)
- ✨ `lini_waktu_target` (pivot: semua field realisasi: r_revenue, r_scalling, dll)

---

## 📞 Support

Jika ada error saat migration:
1. **STOP** dan jangan lanjutkan
2. Catat error message
3. Rollback atau restore dari backup
4. Konsultasi dengan tim

---

**Status Migration:** STEP 1 of 20 ✅ SELESAI
**Next Step:** Create migration untuk table `account_managers`
