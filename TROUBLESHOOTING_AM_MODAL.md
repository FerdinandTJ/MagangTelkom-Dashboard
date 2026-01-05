# Troubleshooting: AM Performance Detail Modal

## Problem
"Error loading AM data" when clicking NIK AM or NAMA AM in the Account Manager Performance Details table.

## Debugging Steps

### 1. Check Browser Console
Open browser developer tools (F12) and check the Console tab for:
- API request details
- Error responses
- Network errors

The modal now logs detailed information:
```
AM Details Response: {...}
Fetch error: {...}
Error response: {...}
```

### 2. Check Network Tab
In the Network tab, look for:
- Request URL: `/api/dashboard/am-performance-detail`
- Request Parameters: `nik_am`, `quarter`, `year`, `segment`
- Response Status Code (200, 404, 500, etc.)
- Response Body

### 3. Common Issues & Solutions

#### Issue 1: 404 Not Found
**Cause**: Route not registered or Laravel route cache
**Solution**:
```bash
php artisan route:clear
php artisan route:cache
```

#### Issue 2: 500 Internal Server Error
**Possible Causes**:
- Database connection issue
- Missing data in account_managers table
- Null pointer exception in controller

**Check Laravel logs**:
```bash
tail -f storage/logs/laravel.log
```

#### Issue 3: Empty Response or No Data
**Possible Causes**:
- No matching data for the NIK AM
- Incorrect quarter/year parameters
- Missing relationships (Witel, Region)

**Solution**: Check database:
```sql
-- Check if AM exists
SELECT * FROM account_managers WHERE nik = 'YOUR_NIK_AM';

-- Check if AM has witel
SELECT am.*, w.nama_witels, r.name as region_name 
FROM account_managers am
LEFT JOIN witels w ON am.idwitels = w.idwitels
LEFT JOIN regions r ON w.region_id = r.id
WHERE am.nik = 'YOUR_NIK_AM';

-- Check if AM has performance data
SELECT * FROM account_manager_companies 
WHERE nik_am = 'YOUR_NIK_AM'
ORDER BY year DESC, quarter DESC;
```

#### Issue 4: CSRF Token Mismatch (419)
**Solution**:
```bash
# Clear cache and restart server
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### 4. Test API Endpoint Directly

You can test the API endpoint using browser or Postman:

```
GET http://localhost/api/dashboard/am-performance-detail?nik_am=12345678&quarter=1&year=2025&segment=HQ-TWS
```

**Expected Response**:
```json
{
    "success": true,
    "data": {
        "am_info": {
            "nik_am": "12345678",
            "nama_am": "John Doe",
            "posisi": "AM",
            "no_gsm": "081234567890",
            "witel_name": "Jakarta Timur",
            "region_name": "Regional 1 Jakarta"
        },
        "current_period": {
            "quarter": 1,
            "year": 2025,
            "period_display": "Q1 2025"
        },
        "summary": {
            "target_proses": 1000,
            "realisasi_proses": 850,
            "target_result": 2000,
            "realisasi_result": 1800
        },
        "historical_data": [...],
        "best_period": {
            "period_display": "Q1 2025",
            "nki_adjustment": 95.75
        }
    }
}
```

### 5. Verify Data Flow

**Frontend → Backend Flow**:
1. User clicks NIK AM or NAMA AM cell in WitelNkiDetailModal
2. `handleAMClick(nikAm)` is called
3. State updates: `setSelectedAM(nikAm)`, `setIsAMModalOpen(true)`
4. AmPerformanceDetailModal opens
5. `useEffect` triggers `fetchAMDetails()`
6. Axios GET request to `/api/dashboard/am-performance-detail`
7. Backend controller processes request
8. Returns JSON response
9. Frontend displays data or error

### 6. Check Required Data

Ensure the following data exists in your database:

**Account Managers Table**:
- Must have records with valid `nik` (Primary Key)
- Should have `nama`, `posisi`, `idwitels`
- Optional: `no_gsm`

**Witels Table**:
- Must have matching `idwitels` for the AM
- Should have `nama_witels` and `region_id`

**Regions Table**:
- Must have matching `id` for the witel's `region_id`
- Should have `name`

**Account Manager Companies Table**:
- Should have performance data for the AM
- Must have `nik_am`, `quarter`, `year`
- Should have all target and realisasi columns

## Quick Fix Checklist

✅ Run Laravel commands:
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

✅ Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

✅ Verify database has data:
```sql
SELECT COUNT(*) FROM account_managers;
SELECT COUNT(*) FROM account_manager_companies;
```

✅ Test API endpoint directly in browser

✅ Check browser console for detailed errors

✅ Verify CSRF token is present

## Files Modified

1. **Backend**:
   - `app/Http/Controllers/AmPerformanceDetailController.php`
   - `routes/web.php`

2. **Frontend**:
   - `resources/js/components/modals/AmPerformanceDetailModal.tsx`
   - `resources/js/components/modals/WitelNkiDetailModal.tsx`

## Contact
If the issue persists, provide:
- Browser console error logs
- Laravel log entries
- Network tab request/response details
- Database query results
