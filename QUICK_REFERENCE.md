# 🚀 Quick Reference Guide

## Critical Field & FK Changes

### **Use This, Not That:**

| ❌ OLD (Don't Use) | ✅ NEW (Use This) | Context |
|-------------------|------------------|---------|
| `companies.id` | `companies.nip_nas` | Primary Key |
| `witels.id` | `witels.idwitels` | Primary Key |
| `revenues.company_id` | `revenues.nip_nas` | Foreign Key |
| `revenues.revenue` | `revenues.total_revenue` | Field Name |
| `witels.name` | `witels.nama_witels` | Field Name |
| `regions.name` | `regions.description` | Field Name |
| `regions.code = 'HQ'` | `regions.code = 'HQ TREG2'` | ENUM Value |
| `regions.code = 'REG1'` | `regions.code = 'TREG1'` | ENUM Value |
| `company_regions` table | `account_manager_company` | Pivot Table |

---

## Common Query Patterns

### **Get Company with Regions (via Account Managers)**
```php
// OLD ❌
$company = Company::with('primaryRegion', 'primaryWitel')->find($id);

// NEW ✅
$company = Company::where('nip_nas', $nipNas)
    ->with('accountManagers.witel.region')
    ->first();
```

### **Get Revenue Data**
```php
// OLD ❌
Revenue::where('company_id', $id)->sum('revenue');

// NEW ✅
Revenue::where('nip_nas', $nipNas)->sum('total_revenue');
```

### **Get Regional Breakdown**
```php
// OLD ❌
DB::table('revenues')
    ->join('companies', 'revenues.company_id', '=', 'companies.id')
    ->join('company_regions', 'companies.id', '=', 'company_regions.company_id')
    ->join('regions', 'company_regions.region_id', '=', 'regions.id')
    ->groupBy('regions.id')
    ->get();

// NEW ✅
DB::table('revenues')
    ->join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
    ->join('account_manager_company', 'companies.nip_nas', '=', 'account_manager_company.nip_nas')
    ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
    ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
    ->join('regions', 'witels.region_id', '=', 'regions.id')
    ->groupBy('regions.id')
    ->get();
```

---

## Model Relationships Quick Reference

### **Company Model**
```php
// Relations available:
$company->accountManagers;     // BelongsToMany
$company->revenues;            // HasMany (via nip_nas)

// REMOVED:
// $company->primaryRegion()
// $company->primaryWitel()
// $company->regions()
// $company->witels()
```

### **AccountManager Model**
```php
// Relations available:
$am->witel;               // BelongsTo
$am->companies;           // BelongsToMany (with pivot: proporsi, pembagian, segment)
$am->liniWaktu;          // HasMany

// Pivot data access:
$am->companies->first()->pivot->proporsi;  // e.g., 50.00
$am->companies->first()->pivot->pembagian; // 'SINGLE' or 'MULTI'
```

### **Witel Model**
```php
// Relations available:
$witel->region;           // BelongsTo
$witel->accountManager;   // HasOne
$witel->companies;        // HasManyThrough AccountManager
```

### **Region Model**
```php
// Relations available:
$region->witels;          // HasMany
$region->accountManagers; // HasManyThrough Witel
```

### **Revenue Model**
```php
// Relations available:
$revenue->company;        // BelongsTo (via nip_nas)

// Helper methods:
$revenue->getAchievementPercentageAttribute();
$revenue->isTargetAchieved();
```

---

## Seeding Order (IMPORTANT!)

```bash
# 1. Regions (auto-seeded in migration, no action needed)
# 2. Witels (requires regions)
php artisan db:seed --class=WitelSeeder

# 3. Account Managers (requires witels)
php artisan db:seed --class=AccountManagerSeeder

# 4. All seeders
php artisan db:seed
```

---

## Region ENUM Codes

| Old Code | New Code | Description |
|----------|----------|-------------|
| HQ | `HQ TREG2` | Headquarters TREG 2 |
| REG1 | `TREG1` | Telkom Regional 1 (Sumatera) |
| REG2 | `TREG2` | Telkom Regional 2 (Jakarta, Banten, Jabar) |
| REG3 | `TREG3` | Telkom Regional 3 (Jateng & DIY) |
| REG4 | `TREG4` | Telkom Regional 4 (Jawa Timur) |
| REG5 | `TREG5` | Telkom Regional 5 (Indonesia Timur) |

---

## Witel ID Pattern

| Region | ID Range | Example |
|--------|----------|---------|
| TREG1 | 1001-1008 | 1001 = Aceh, 1002 = Medan |
| TREG2 | 2001-2010 | 2001 = Jakarta, 2008 = Bandung |
| TREG3 | 3001-3005 | 3001 = Semarang, 3005 = Yogyakarta |
| TREG4 | 4001-4005 | 4001 = Surabaya, 4002 = Malang |
| TREG5 | 5001-5012 | 5001 = Bali, 5012 = Papua |

---

## Testing Commands

```bash
# Full reset and test
php artisan migrate:fresh && php artisan db:seed && php test-migration.php

# Individual tests
php artisan migrate:fresh       # Reset database
php artisan db:seed             # Seed all data
php test-migration.php          # Run test suite

# Debug
php artisan tinker              # Interactive shell
php artisan route:list          # List all routes
tail -f storage/logs/laravel.log # Watch logs
```

---

## Common Errors & Fixes

### **Error: "nip_nas not found"**
```bash
# Fix: Make sure you're using nip_nas, not id
Company::where('nip_nas', $value)->first();  // ✅
Company::find($value);  // ❌ (tries to use id)
```

### **Error: "company_id doesn't exist"**
```bash
# Fix: Update FK name in query
Revenue::where('nip_nas', $value)  // ✅
Revenue::where('company_id', $value)  // ❌
```

### **Error: "company_regions table not found"**
```bash
# Fix: Use new pivot table
DB::table('account_manager_company')  // ✅
DB::table('company_regions')  // ❌ (dropped)
```

### **Error: "Column 'revenue' not found"**
```bash
# Fix: Use new field name
->sum('total_revenue')  // ✅
->sum('revenue')  // ❌
```

### **Error: "Region code 'REG1' not found"**
```bash
# Fix: Use new ENUM codes
Region::where('code', 'TREG1')  // ✅
Region::where('code', 'REG1')  // ❌
```

---

## File Locations

```
📁 Migrations:    database/migrations/2025_11_12_*.php
📁 Models:        app/Models/
📁 Seeders:       database/seeders/
📁 Controllers:   app/Http/Controllers/
📁 Services:      app/Services/
📁 Tests:         test-migration.php
📁 Docs:          *.md files in root
```

---

## Support Resources

1. **COMPLETION_SUMMARY.md** - Full project overview
2. **CONTROLLER_UPDATE_SUMMARY.md** - Controller changes detail
3. **DATABASE_MIGRATIONS_SUMMARY.md** - Migration details
4. **test-migration.php** - Automated testing script
5. **Laravel Logs** - `storage/logs/laravel.log`

---

## Quick Validation

```php
// Test in tinker (php artisan tinker)

// 1. Check table structure
Schema::hasTable('account_managers');  // Should be true
Schema::hasTable('company_regions');   // Should be false

// 2. Check company PK
Company::first()->getKeyName();  // Should return 'nip_nas'

// 3. Check relationships
Company::first()->accountManagers;  // Should return collection
AccountManager::first()->witel->region;  // Should return region

// 4. Check field names
Revenue::first()->total_revenue;  // Should have value
Witel::first()->nama_witels;  // Should have value
```

---

## Remember:

✅ Always use `nip_nas` instead of `company.id`  
✅ Always use `total_revenue` instead of `revenue`  
✅ Regional data flows through Account Managers now  
✅ Region codes use TREG prefix (not REG)  
✅ Witels use custom IDs (1001-5012), not auto-increment  
✅ Run seeders in order: Regions → Witels → AccountManagers  
✅ Clear cache after changes: `php artisan config:clear`  

---

**📌 Keep this guide handy during development!**
