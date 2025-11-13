# 🎉 Database Restructure - PHASE 1 & 2 COMPLETED!

## ✅ PROGRESS: 17/20 TASKS DONE (85%)

---

## 📦 **COMPLETED: MIGRATIONS (11 Files)**

### Migration Files Created:
1. ✅ `2025_11_12_000001` - Drop old tables & prepare restructure
2. ✅ `2025_11_12_000002` - Create account_managers table
3. ✅ `2025_11_12_000003` - Restructure companies table
4. ✅ `2025_11_12_000004` - Restructure regions table (+ auto-seed)
5. ✅ `2025_11_12_000005` - Restructure witels table
6. ✅ `2025_11_12_000006` - Create lini_waktu table
7. ✅ `2025_11_12_000007` - Create target_account_m table
8. ✅ `2025_11_12_000008` - Restructure revenues table
9. ✅ `2025_11_12_000009` - Create pivot: account_manager_company
10. ✅ `2025_11_12_000010` - Create pivot: lini_waktu_target
11. ✅ `2025_11_12_000011` - Add FK to account_managers

---

## 📊 **COMPLETED: MODELS (7 Files)**

### Models Created/Updated:
1. ✅ **AccountManager.php** (NEW)
   - Primary Key: `nik` (string)
   - Relations: witel (BelongsTo), companies (BelongsToMany), liniWaktu (HasMany)
   - Scopes: byPosisi, byWitel
   - Helpers: handlesCompany(), getTotalCompaniesAttribute()

2. ✅ **Company.php** (UPDATED)
   - Primary Key: `nip_nas` (string) - CHANGED from id
   - Removed: primaryRegion(), primaryWitel(), regions(), witels(), companyRegions()
   - Added: accountManagers (BelongsToMany)
   - Updated: revenues() to use nip_nas FK
   - Helpers: getPrimaryAccountManagerAttribute()

3. ✅ **Region.php** (UPDATED)
   - Added: code (ENUM), description fields
   - Removed: companies(), companyRegions(), revenues()
   - Added: accountManagers (HasManyThrough via Witel)
   - Constants: CODE_HQ, CODE_TREG1-5
   - Helpers: isHQ(), getTotalWitelsAttribute()

4. ✅ **Witel.php** (UPDATED)
   - Primary Key: `idwitels` (int custom) - CHANGED from id
   - Field: `name` → `nama_witels`
   - Removed: companies(), companyRegions(), revenues()
   - Added: accountManager (HasOne), companies (HasManyThrough)
   - Helpers: hasAccountManager(), getTotalCompaniesAttribute()

5. ✅ **LiniWaktu.php** (NEW)
   - Fields: quartal (ENUM Q1-Q4), bulan_awal, bulan_akhir, tahun, nik_am
   - Relations: accountManager (BelongsTo), targets (BelongsToMany)
   - Helpers: setDatesFromQuartal(), isActive(), getPeriodNameAttribute()
   - Scopes: byTahun, byQuartal, currentQuarter

6. ✅ **TargetAccountM.php** (NEW)
   - 16 target fields: t_revenue, t_scalling, t_datin, t_hsi, dll
   - Relations: liniWaktu (BelongsToMany with 14 realisasi fields)
   - Helpers: getRealisasiForPeriod(), calculateAchievement(), getTotalTargetAttribute()

7. ✅ **Revenue.php** (UPDATED)
   - FK: `company_id` → `nip_nas` - CHANGED
   - Field: `revenue` → `total_revenue` - CHANGED
   - Added: note, target fields
   - Removed: region(), witel() relations
   - Helpers: getAchievementPercentageAttribute(), isTargetAchieved()
   - Scopes: byYear, byPeriod, aboveTarget, belowTarget

---

## 📈 **STRUCTURE OVERVIEW**

### Relasi Database Baru:
```
regions (HQ, TREG1-5)
  ↓ One-to-Many
witels (idwitels)
  ↓ One-to-One
account_managers (nik)
  ↓ Many-to-Many (pivot: account_manager_company)
companies (nip_nas)
  ↓ One-to-Many
revenues (nip_nas FK)

account_managers
  ↓ One-to-Many
lini_waktu (quartal periods)
  ↓ Many-to-Many (pivot: lini_waktu_target dengan realisasi)
target_account_m (16 KPI targets)
```

---

## 🔄 **KEY CHANGES SUMMARY**

### Primary Key Changes:
- ✅ Company: `id` → `nip_nas` (VARCHAR)
- ✅ Witel: `id` → `idwitels` (INT custom)
- ✅ AccountManager: `nik` (VARCHAR, NEW)

### Field Name Changes:
- ✅ Revenue: `revenue` → `total_revenue`
- ✅ Witel: `name` → `nama_witels`
- ✅ Region: Added `code` (ENUM) & `description`

### Removed Tables/Relations:
- ❌ Table `company_regions` (dropped)
- ❌ Model `CompanyRegion` (tidak digunakan lagi)
- ❌ Relations: Company.regions(), Company.witels()
- ❌ Relations: Region.companies(), Witel.companies() (direct)
- ❌ FK: company.primary_region_id, company.primary_witel_id
- ❌ FK: revenue.region_id, revenue.witel_id

### New Tables/Relations:
- ✨ Table `account_managers` (NEW)
- ✨ Table `lini_waktu` (NEW)
- ✨ Table `target_account_m` (NEW)
- ✨ Pivot `account_manager_company` (NEW)
- ✨ Pivot `lini_waktu_target` (NEW)

---

## 📝 **REMAINING TASKS (3/20)**

### 🔜 NEXT STEPS:

18. ⏳ **Update Controllers** - Adjust controllers untuk struktur baru
19. ⏳ **Create Seeders** - Seed data untuk regions, witels, account_managers
20. ⏳ **Testing & Validation** - Test semua fungsi dashboard

---

## ⚠️ **BEFORE RUNNING MIGRATIONS:**

### 1. Backup Database (WAJIB!)
```powershell
C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe -u root -P 3309 --databases telkomtws > database\backups\backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql
```

### 2. Run Migrations
```powershell
# Fresh migrate (HATI-HATI: reset semua!)
php artisan migrate:fresh

# Atau migrate normal
php artisan migrate
```

### 3. Verify Migration
```powershell
# Check tables created
php artisan migrate:status

# Check database structure
php artisan tinker
>>> \Schema::hasTable('account_managers')
>>> \Schema::hasTable('lini_waktu')
```

---

## 🎯 **USAGE EXAMPLES**

### Query dengan Relations Baru:

```php
// Get Account Manager with companies
$am = AccountManager::with('companies', 'witel')->find('1234567890');

// Get Company with account managers
$company = Company::with('accountManagers')->find('NIP123');

// Get revenues by company
$revenues = Revenue::byCompany('NIP123')->byYear(2024)->get();

// Get Region with witels and account managers
$region = Region::with('witels.accountManager')->find(1);

// Get current quarter lini waktu
$currentPeriod = LiniWaktu::currentQuarter()->first();

// Calculate achievement
$target = TargetAccountM::find(1);
$achievement = $target->calculateAchievement('revenue', 950000000);
```

---

## 🚀 **STATUS**

**Phase 1 (Migrations):** ✅ 100% COMPLETE (11/11 files)
**Phase 2 (Models):** ✅ 100% COMPLETE (7/7 files)
**Phase 3 (Controllers):** ⏳ PENDING
**Phase 4 (Seeders):** ⏳ PENDING
**Phase 5 (Testing):** ⏳ PENDING

**Overall Progress:** 17/20 (85%) ✅

---

**Next Command:** Ketik **"lanjut controllers"** untuk update controllers, atau **"lanjut seeders"** untuk create seeders terlebih dahulu!
