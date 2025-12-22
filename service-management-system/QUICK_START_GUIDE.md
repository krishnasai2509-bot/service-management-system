# Worker Availability System - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Step 1: Install Database (2 minutes)
```bash
# Option 1: Command Line
cd C:\xampp\htdocs\chandu
mysql -u root -p task_manager < availability_schema.sql

# Option 2: phpMyAdmin
# 1. Open http://localhost/phpmyadmin
# 2. Select 'task_manager' database
# 3. Import > Choose 'availability_schema.sql'
# 4. Click 'Go'
```

### Step 2: Test as Worker (2 minutes)
```
1. Go to: http://localhost/chandu/
2. Login as worker:
   Email: robert.p@worker.com
   Password: password123
3. Click "Setup Default Hours"
4. Click "Weekdays 9-5" button
5. Click "Save Default Availability"
6. Go to "Manage Availability"
7. Mark tomorrow 9 AM - 12 PM as unavailable
```

### Step 3: Test as Admin (1 minute)
```
1. Logout and login as admin:
   Email: admin@taskmanager.com
   Password: password123
2. Go to admin dashboard
3. Check "Pending Booking Requests"
4. See green/red dots for worker availability
5. Click "Assign Worker" on any request
6. See available workers marked with 🟢
```

## 📋 Feature Checklist

### For Workers:
- [ ] Set default weekly hours (Monday-Sunday)
- [ ] Mark vacation days as unavailable
- [ ] Mark specific time slots as unavailable
- [ ] View availability calendar
- [ ] Update schedule anytime

### For Admins:
- [ ] See real-time availability (green/red dots)
- [ ] Filter workers by availability
- [ ] Assign only available workers
- [ ] View worker schedules
- [ ] Track assignment history

## 🎯 Common Tasks

### Worker: Set Vacation (All Day Unavailable)
```
1. Go to: Manage Availability
2. Click: Mark Unavailable Time
3. Select: Date (e.g., Dec 25, 2025)
4. Time: 00:00 to 23:59
5. Reason: "Christmas Vacation"
6. Click: Mark as Unavailable
```

### Worker: Set Morning Appointment
```
1. Go to: Manage Availability
2. Click: Mark Unavailable Time
3. Select: Date (e.g., Dec 15, 2025)
4. Time: 09:00 to 12:00
5. Reason: "Doctor Appointment"
6. Click: Mark as Unavailable
```

### Admin: Check Who's Available for Specific Time
```
1. Go to: Admin Dashboard
2. Find booking request in table
3. Look at "Available Workers" column
4. Count green dots = available workers
5. Hover over dots to see worker names
```

### Admin: Assign Available Worker
```
1. Click: "Assign Worker" button
2. Dropdown shows workers (available first)
3. Look for: 🟢 Green circle = Available
4. Select worker from dropdown
5. Enter service amount
6. Click: "Assign Worker"
```

## 🔧 Troubleshooting (30 seconds each)

### "Function not found" error
```sql
-- Run this in phpMyAdmin SQL tab:
USE task_manager;
SOURCE C:/xampp/htdocs/chandu/availability_schema.sql;
```

### No green dots showing
```
Workers must set default availability first!
→ Each worker: Login → Setup Default Hours → Save
```

### All workers show red
```
Check if booking time matches worker's default hours:
→ Booking at 8 AM, but worker starts at 9 AM = RED
→ Booking on Sunday, but worker only Mon-Fri = RED
```

### Worker can't see setup page
```
Add this to worker dashboard sidebar:
<a href="setup_availability.php">Setup Default Hours</a>
```

## 📊 Quick Database Checks

### Check which workers have setup availability:
```sql
SELECT w.worker_name, COUNT(wda.default_availability_id) as days_setup
FROM worker w
LEFT JOIN worker_default_availability wda ON w.worker_id = wda.worker_id
GROUP BY w.worker_id;
```

### Check worker status for specific date/time:
```sql
SELECT w.worker_name,
       get_worker_availability_status(w.worker_id, '2025-12-15', '14:00:00') as status
FROM worker w;
```

### View all unavailability exceptions:
```sql
SELECT w.worker_name, wu.unavailable_date, wu.unavailable_start_time,
       wu.unavailable_end_time, wu.reason
FROM worker_unavailability wu
JOIN worker w ON wu.worker_id = w.worker_id
WHERE wu.unavailable_date >= CURDATE()
ORDER BY wu.unavailable_date;
```

## 🎨 Visual Reference

### Admin Dashboard - Availability Indicators
```
┌─────────────────────────────────────────────────────────┐
│ Pending Booking Requests                                │
├─────┬──────────┬──────────┬───────────┬────────────────┤
│ ID  │ Customer │ Category │ Date/Time │ Avail Workers  │
├─────┼──────────┼──────────┼───────────┼────────────────┤
│ 101 │ John Doe │ Plumbing │ Dec 15    │ 🟢🟢🔴🟢       │
│     │          │          │ 2:00 PM   │ (3 available)  │
└─────┴──────────┴──────────┴───────────┴────────────────┘

Legend:
🟢 = Available (worker can take job)
🔴 = Unavailable (marked unavailable or booked)
```

### Worker Assignment - Dropdown
```
Select Worker:
┌────────────────────────────────────────────────┐
│ 🟢 Robert Plumber (Plumbing, ⭐4.5, Available)│
│ 🟢 Tom Electric (Electrical, ⭐4.8, Available)│
│ 🔴 Chris Carpenter (Carpentry, ⭐4.9, Unavail)│
│ 🔵 Lisa Cleaner (Cleaning, ⭐4.6, Booked)    │
└────────────────────────────────────────────────┘
```

## 📱 URL Quick Access

| Page | URL | Who Can Access |
|------|-----|----------------|
| Setup Default Hours | `/setup_availability.php` | Workers only |
| Manage Availability | `/manage_availability.php` | Workers only |
| Admin Dashboard | `/admin_dashboard.php` | Admins only |
| Assign Booking | `/assign_booking.php?id=X` | Admins only |
| Worker Dashboard | `/worker_dashboard.php` | Workers only |

## 🔑 Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@taskmanager.com | password123 |
| Worker | robert.p@worker.com | password123 |
| Worker | tom.e@worker.com | password123 |
| Customer | john.doe@email.com | password123 |

## ✅ Success Criteria

After setup, you should see:
- ✅ Workers can set weekly schedule
- ✅ Workers can mark days unavailable
- ✅ Admin sees green dots for available workers
- ✅ Admin sees red dots for unavailable workers
- ✅ Worker assignment dropdown shows status
- ✅ Available workers appear first in list

## 📚 More Information

- **Full Guide:** `AVAILABILITY_SETUP_GUIDE.md`
- **Installation:** `INSTALLATION_STEPS.md`
- **Summary:** `IMPLEMENTATION_SUMMARY.md`
- **Database:** `availability_schema.sql`
- **Styles:** `availability_styles.css`

## 🆘 Need Help?

1. Check `AVAILABILITY_SETUP_GUIDE.md` for detailed docs
2. Run database verification queries above
3. Check PHP/MySQL error logs
4. Verify all files are in place
5. Test with sample data provided

## 🎉 You're Done!

The system is ready to use. Workers can now manage their availability, and admins can see real-time availability when assigning jobs.

**Next Steps:**
1. Train workers to set their schedules
2. Train admins to use availability indicators
3. Monitor usage and gather feedback
4. Consider adding optional features (calendar view, notifications, etc.)
