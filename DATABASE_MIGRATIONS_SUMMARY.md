# 📊 Database Restructure - Migration Summary

## ✅ MIGRATIONS COMPLETED (11 Files)

### 🗑️ **Phase 1: Cleanup & Preparation**
- **File:** `2025_11_12_000001_drop_old_tables_and_prepare_restructure.php`
- **Action:** 
  - Drop table `company_regions` (tidak digunakan lagi)
  - Drop foreign key constraints dari revenues, companies, witels
- **Comment:** Mempersiapkan database untuk restructure besar

---

### 🆕 **Phase 2: Create New Tables**

#### 1️⃣ **Account Managers**
- **File:** `2025_11_12_000002_create_account_managers_table.php`
- **Structure:**
  - `nik` VARCHAR(10) PRIMARY KEY
  - `nama` VARCHAR(50) NOT NULL
  - `posisi` ENUM('AM','AM1','AM1PRO','AM2','AM2PRO','AM3','EAM','SAM')
  - `no_gsm` VARCHAR(15) NULLABLE
  - `idwitels` FK to witels (One-to-One)
- **Comment:** Table baru untuk data Account Manager

---

### 🔄 **Phase 3: Restructure Existing Tables**

#### 2️⃣ **Companies (Restructure)**
- **File:** `2025_11_12_000003_restructure_companies_table.php`
- **Changes:**
  - ❌ Hapus: `id` (auto increment)
  - ✅ Primary Key: `nip_nas` VARCHAR(25)
  - ❌ Hapus: `primary_region_id`, `primary_witel_id`
  - ✅ Keep: `nama_perusahaan`, `subsegment`, `source_data`
- **Comment:** Ubah PK dari id ke nip_nas, hapus direct FK ke region/witel

#### 3️⃣ **Regions (Restructure)**
- **File:** `2025_11_12_000004_restructure_regions_table.php`
- **Changes:**
  - ✅ Tambah: `code` ENUM('HQ TREG2', 'TREG1', 'TREG2', 'TREG3', 'TREG4', 'TREG5') UNIQUE
  - ✅ Update: `name` VARCHAR(25)
  - ✅ Tambah: `description` VARCHAR(25)
- **Seed Data:** Auto-seed 6 regions (HQ TREG2, TREG1-5)
- **Comment:** Standardize region codes dengan ENUM

#### 4️⃣ **Witels (Restructure)**
- **File:** `2025_11_12_000005_restructure_witels_table.php`
- **Changes:**
  - ❌ Hapus: `id` (auto increment)
  - ✅ Primary Key: `idwitels` INT (custom ID)
  - ✅ Update: `name` → `nama_witels` VARCHAR(25)
  - ✅ Tambah: `region_id` FK to regions
- **Comment:** Custom PK untuk sync dengan ID WITEL Telkom

---

### 🆕 **Phase 4: New Tables for Target & Timeline**

#### 5️⃣ **Lini Waktu**
- **File:** `2025_11_12_000006_create_lini_waktu_table.php`
- **Structure:**
  - `id` INT PRIMARY KEY AUTO INCREMENT
  - `quartal` ENUM('Q1','Q2','Q3','Q4')
  - `bulan_awal` DATETIME
  - `bulan_akhir` DATETIME
  - `tahun` YEAR
  - `nik_am` FK to account_managers.nik
- **Unique:** (nik_am, tahun, quartal)
- **Comment:** Periode waktu quartal untuk tracking target/realisasi

#### 6️⃣ **Target Account M**
- **File:** `2025_11_12_000007_create_target_account_m_table.php`
- **Structure:** 16 fields target KPI
  - `t_revenue`, `t_scalling`, `t_datin`, `t_hsi`, `t_wireline`
  - `t_wifi`, `t_cyc`, `t_cr`, `t_profit`, `t_nps`
  - `t_maps`, `t_lop`, `t_capability`, `t_cc`, `t_ngtma`, `t_sustain`
- **Comment:** Master data target KPI untuk Account Manager

---

### 🔄 **Phase 5: Restructure Revenues**

#### 7️⃣ **Revenues (Restructure)**
- **File:** `2025_11_12_000008_restructure_revenues_table.php`
- **Changes:**
  - ❌ Hapus: `company_id` (INT FK)
  - ✅ Tambah: `nip_nas` VARCHAR(25) FK to companies.nip_nas
  - ❌ Hapus: `region_id`, `witel_id`
  - ✅ Update: `revenue` → `total_revenue` DECIMAL(12,6)
  - ✅ Tambah: `note` VARCHAR(45)
  - ✅ Tambah: `target` DECIMAL(15,2)
- **Unique:** (nip_nas, tahun, bulan)
- **Comment:** FK ke companies.nip_nas, hapus direct FK ke region/witel

---

### 🔗 **Phase 6: Pivot Tables (Many-to-Many)**

#### 8️⃣ **Account Manager ↔ Company**
- **File:** `2025_11_12_000009_create_account_manager_company_pivot_table.php`
- **Structure:**
  - `nik_am` FK to account_managers.nik
  - `nip_nas` FK to companies.nip_nas
  - `proporsi` DECIMAL(5,2) - Proporsi pembagian (%)
  - `pembagian` ENUM('SINGLE','MULTI')
  - `segment` VARCHAR(20)
- **Unique:** (nik_am, nip_nas)
- **Comment:** Relasi Many-to-Many AM dan Company dengan proporsi

#### 9️⃣ **Lini Waktu ↔ Target (Realisasi)**
- **File:** `2025_11_12_000010_create_lini_waktu_target_pivot_table.php`
- **Structure:**
  - `lini_waktu_id` FK to lini_waktu.id
  - `target_id` FK to target_account_m.id
  - 14 fields realisasi (r_*):
    - `r_revenue`, `r_scalling`, `r_datin`, `r_hsi`, `r_wireline`
    - `r_wifi`, `r_cyc`, `r_cr`, `r_profit`, `r_nps`
    - `r_maps`, `r_lop`, `r_capability`, `r_cc`
- **Unique:** (lini_waktu_id, target_id)
- **Comment:** Pivot untuk menyimpan realisasi KPI per periode

---

### 🔗 **Phase 7: Add Remaining FK**

#### 🔟 **Account Managers FK**
- **File:** `2025_11_12_000011_add_foreign_key_to_account_managers.php`
- **Action:** Tambah FK `idwitels` → `witels.idwitels`
- **Comment:** FK constraint yang tertunda setelah witels ter-restructure

---

## 📋 Migration Order (WAJIB URUT!)

```bash
1. 2025_11_12_000001 - Drop old tables & FK
2. 2025_11_12_000002 - Create account_managers
3. 2025_11_12_000003 - Restructure companies
4. 2025_11_12_000004 - Restructure regions (+ seed)
5. 2025_11_12_000005 - Restructure witels
6. 2025_11_12_000006 - Create lini_waktu
7. 2025_11_12_000007 - Create target_account_m
8. 2025_11_12_000008 - Restructure revenues
9. 2025_11_12_000009 - Create pivot account_manager_company
10. 2025_11_12_000010 - Create pivot lini_waktu_target
11. 2025_11_12_000011 - Add FK to account_managers
```

---

## 🎯 Relasi Database Baru

```
regions (HQ, TREG1-5)
  ↓ (One-to-Many)
witels
  ↓ (One-to-One)
account_managers
  ↓ (Many-to-Many via pivot: account_manager_company)
companies
  ↓ (One-to-Many)
revenues

account_managers
  ↓ (One-to-Many)
lini_waktu
  ↓ (Many-to-Many via pivot: lini_waktu_target)
target_account_m
```

---

## ⚠️ IMPORTANT NOTES

### 1. **Data Migration**
- Data companies, revenues akan di-preserve (dengan adjustment FK)
- Data regions akan di-seed ulang (standardize)
- Data witels perlu di-seed ulang (custom idwitels)
- Data account_managers adalah table baru (perlu seed manual)

### 2. **Backup Wajib**
```powershell
# Backup database sebelum migrate
C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe -u root -P 3309 --databases telkomtws > database\backups\backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql
```

### 3. **Run Migrations**
```powershell
# Fresh migrate (HATI-HATI: akan reset semua data!)
php artisan migrate:fresh

# Atau migrate step by step
php artisan migrate
```

### 4. **Rollback**
```powershell
# Rollback 1 step
php artisan migrate:rollback --step=1

# Rollback all (11 migrations)
php artisan migrate:rollback --step=11
```

---

## 📝 Next Steps

✅ **STEP 1-10: Migrations Created**
🔜 **STEP 11-17: Create/Update Models**
🔜 **STEP 18: Update Controllers**
🔜 **STEP 19: Create Seeders**
🔜 **STEP 20: Testing & Validation**

---

**Status:** ✅ 10/20 COMPLETED (Migrations Done)
**Next:** Create Models dengan relations baru
