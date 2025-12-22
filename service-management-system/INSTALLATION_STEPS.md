# Worker Availability System - Installation Steps

## Quick Installation Guide

Follow these steps to set up the complete worker availability system:

### Step 1: Database Setup

1. **Start XAMPP**
   - Start Apache and MySQL from XAMPP Control Panel

2. **Import Database Schema**

   **Option A: Using phpMyAdmin (Recommended)**
   - Open http://localhost/phpmyadmin
   - Click "Import" tab
   - Upload and execute in this order:
     1. `improved_database.sql` (base schema)
     2. `availability_schema.sql` (availability features)

   **Option B: Using Command Line**
   ```bash
   cd C:\xampp\htdocs\chandu
   mysql -u root -p < improved_database.sql
   mysql -u root -p task_manager < availability_schema.sql
   ```

3. **Verify Installation**
   Run this query in phpMyAdmin SQL tab:
   ```sql
   USE task_manager;
   SHOW TABLES;
   SHOW FUNCTION STATUS WHERE Db = 'task_manager';
   SHOW PROCEDURE STATUS WHERE Db = 'task_manager';
   ```

   You should see:
   - Tables: `worker_default_availability`, `worker_unavailability`
   - Function: `get_worker_availability_status`
   - Procedure: `check_worker_availability`

### Step 2: Test the System

1. **Access the Application**
   - Open: http://localhost/chandu/

2. **Test Worker Flow**
   - Register as a worker at: `register_new.php`
   - Login with worker credentials
   - Click "Setup Default Hours" (you'll see a warning if not set up)
   - Use "Weekdays 9-5" quick fill
   - Save your default availability
   - Go to "Manage Availability"
   - Mark tomorrow as unavailable from 9 AM to 12 PM

3. **Test Admin Flow**
   - Login as admin (email: admin@taskmanager.com, password: password123)
   - Create a customer booking for tomorrow at 10 AM
   - Go to admin dashboard
   - Check "Pending Booking Requests" section
   - You should see:
     - Green dots for available workers
     - Red dot for the worker you marked unavailable
   - Click "Assign Worker"
   - Verify the dropdown shows:
     - 🟢 Available workers
     - 🔴 Unavailable workers

### Step 3: File Structure Verification

Verify these files exist in `C:\xampp\htdocs\chandu\`:

**New Files:**
- ✅ `availability_schema.sql`
- ✅ `setup_availability.php`
- ✅ `AVAILABILITY_SETUP_GUIDE.md`
- ✅ `INSTALLATION_STEPS.md`

**Modified Files:**
- ✅ `manage_availability.php`
- ✅ `admin_dashboard.php`
- ✅ `assign_booking.php`
- ✅ `worker_dashboard.php`
- ✅ `register_new.php`

## Default Admin/User Credentials

**Admin:**
- Email: admin@taskmanager.com
- Password: password123

**Sample Worker:**
- Email: robert.p@worker.com
- Password: password123

**Sample Customer:**
- Email: john.doe@email.com
- Password: password123

## Common Issues & Solutions

### Issue 1: "Function get_worker_availability_status does not exist"
**Cause:** availability_schema.sql not run
**Solution:**
```sql
USE task_manager;
SOURCE C:/xampp/htdocs/chandu/availability_schema.sql;
```

### Issue 2: No green/red dots showing
**Cause:** Workers haven't set default availability
**Solution:** Each worker must visit `setup_availability.php` first

### Issue 3: All workers showing red
**Possible causes:**
1. Workers haven't set up default hours
2. Booking time is outside worker's normal hours
3. Workers marked that time as unavailable

**Debug query:**
```sql
SELECT w.worker_id, w.worker_name,
       get_worker_availability_status(w.worker_id, '2025-12-15', '14:00:00') as status
FROM worker w;
```

### Issue 4: Page errors or white screen
**Check:**
1. PHP error logs: `C:\xampp\php\logs\php_error_log`
2. Apache error logs: `C:\xampp\apache\logs\error.log`
3. Enable error display in `config.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

## System URLs

- **Home/Login:** http://localhost/chandu/
- **Register:** http://localhost/chandu/register_new.php
- **Worker Dashboard:** http://localhost/chandu/dashboard.php (after login as worker)
- **Admin Dashboard:** http://localhost/chandu/dashboard.php (after login as admin)
- **Setup Availability:** http://localhost/chandu/setup_availability.php (worker only)
- **Manage Availability:** http://localhost/chandu/manage_availability.php (worker only)
- **Assign Booking:** http://localhost/chandu/assign_booking.php?id={booking_id} (admin only)

## Testing Checklist

- [ ] Database schema installed successfully
- [ ] Functions and procedures created
- [ ] Can register as new worker
- [ ] Worker sees "Setup Default Hours" prompt
- [ ] Worker can set default availability (Weekdays 9-5)
- [ ] Worker can mark specific dates unavailable
- [ ] Admin sees green/red dots in pending requests
- [ ] Admin can assign available workers
- [ ] Unavailable workers show red in assignment dropdown
- [ ] Available workers show green in assignment dropdown

## Next Steps After Installation

1. **For Workers:**
   - Login and setup default availability
   - Review and update weekly schedule as needed
   - Mark vacation days or unavailable times in advance

2. **For Admins:**
   - Test the assignment flow with sample bookings
   - Verify availability indicators are working
   - Train staff on the new availability system

3. **Optional Customizations:**
   - Modify time slots (currently any time)
   - Add email notifications
   - Customize availability display
   - Add recurring unavailability patterns

## Support & Documentation

For detailed information, see:
- `AVAILABILITY_SETUP_GUIDE.md` - Complete feature documentation
- Database schema: `availability_schema.sql`
- Original database: `improved_database.sql`

## Backup Recommendation

Before making changes, backup your database:
```bash
mysqldump -u root -p task_manager > backup_$(date +%Y%m%d).sql
```

Or through phpMyAdmin:
1. Select `task_manager` database
2. Click "Export" tab
3. Click "Go" to download backup
