# Worker Availability System - Setup Guide

## Overview
This system allows workers to set their default weekly availability and mark specific dates as unavailable. Admins can see worker availability in real-time with green/red indicators when assigning bookings.

## Database Setup

### Step 1: Run the Availability Schema
First, make sure you've run the `improved_database.sql` file. Then run:

```bash
mysql -u root -p task_manager < availability_schema.sql
```

Or through phpMyAdmin:
1. Open phpMyAdmin
2. Select the `task_manager` database
3. Click "Import" tab
4. Choose the file `availability_schema.sql`
5. Click "Go"

This creates:
- `worker_default_availability` table (weekly schedule)
- `worker_unavailability` table (exception dates)
- `check_worker_availability()` stored procedure
- `get_worker_availability_status()` function

### Step 2: Verify Tables Created
Run this query to verify:

```sql
SHOW TABLES LIKE 'worker_%';
```

You should see:
- `worker`
- `worker_default_availability`
- `worker_unavailability`

## Features

### 1. Worker Registration with Default Availability
When a worker registers (using `register_new.php`), they should then:
1. Login to their dashboard
2. Click "Setup Default Hours" or go to `setup_availability.php`
3. Set their regular working hours for each day of the week

**Quick Fill Options:**
- **Weekdays 9-5**: Automatically fills Monday-Friday, 9 AM to 5 PM
- **All Days 9-5**: Fills all 7 days, 9 AM to 5 PM
- **8 AM - 6 PM**: Custom time range for selected days
- **Clear All**: Removes all selections

### 2. Manage Availability (Worker)
URL: `manage_availability.php`

Workers can:
- **View Default Schedule**: See their regular weekly hours
- **Mark Unavailable Times**: Block specific dates/times
  - Vacation days
  - Personal appointments
  - Training sessions
  - Any other unavailability
- **Add Specific Slots** (Legacy): Add individual available time slots

**Example Use Case:**
- Worker has default: Monday-Friday 9 AM - 5 PM
- Worker marks December 25, 2025 as unavailable (Christmas)
- Worker marks December 31, 2025, 2 PM - 5 PM as unavailable (early leave)

### 3. Admin Dashboard with Availability Indicators
URL: `admin_dashboard.php`

Features:
- **Pending Booking Requests** table shows:
  - Customer request details
  - **Available Workers column** with colored dots:
    - 🟢 **Green dot** = Worker is available at requested time
    - 🔴 **Red dot** = Worker is unavailable or booked
  - Hover over dots to see worker names and status
  - Count of available workers displayed

### 4. Smart Worker Assignment
URL: `assign_booking.php?id={booking_id}`

Features:
- Workers sorted by availability status:
  1. ✅ Available workers first
  2. ❌ Unavailable workers
  3. 📅 Already booked workers
- Color-coded dropdown:
  - 🟢 **Available** = Worker can take the job
  - 🔴 **Unavailable** = Worker marked time as unavailable
  - 🔵 **Booked** = Worker already has a booking at that time
- Rating displayed for each worker

## How the Availability System Works

### Availability Logic
A worker is **AVAILABLE** at a specific date/time if:
1. ✅ They have default availability for that day/time (from `worker_default_availability`)
2. ✅ They have NOT marked that specific date/time as unavailable (in `worker_unavailability`)
3. ✅ They are NOT already booked at that time (in `booking` table)

### Example Scenarios

**Scenario 1: Simple Default Availability**
- Worker sets: Monday-Friday, 9 AM - 5 PM
- Customer requests: Tuesday, 2 PM
- **Result**: ✅ Available (falls within default hours)

**Scenario 2: Marked Unavailable**
- Worker sets: Monday-Friday, 9 AM - 5 PM
- Worker marks unavailable: Tuesday Dec 10, 9 AM - 5 PM (doctor appointment)
- Customer requests: Tuesday Dec 10, 2 PM
- **Result**: ❌ Unavailable (marked as exception)

**Scenario 3: Already Booked**
- Worker sets: Monday-Friday, 9 AM - 5 PM
- Worker has booking: Wednesday, 10 AM
- Customer requests: Wednesday, 10 AM
- **Result**: 🔵 Booked (already assigned)

**Scenario 4: Outside Default Hours**
- Worker sets: Monday-Friday, 9 AM - 5 PM
- Customer requests: Saturday, 2 PM
- **Result**: ❌ Unavailable (no default availability on Saturday)

## Worker Workflow

### Initial Setup (After Registration)
1. Worker registers using `register_new.php`
2. Worker logs in
3. Worker navigates to `setup_availability.php` or clicks "Setup Default Hours"
4. Worker selects days and times they normally work
5. Worker saves their default schedule

### Managing Ongoing Availability
1. Worker goes to `manage_availability.php` from dashboard
2. Views current default schedule
3. Marks specific dates/times as unavailable when needed
4. Can remove unavailability exceptions if plans change

### Before a Known Absence
1. Go to "Manage Availability"
2. Scroll to "Mark Unavailable Time"
3. Select the date
4. Enter start and end time
5. Optionally add reason (e.g., "Vacation")
6. Click "Mark as Unavailable"

## Admin Workflow

### When Booking Requests Come In
1. Admin sees pending requests on dashboard
2. Admin checks the "Available Workers" column
3. Green dots = workers available at that time
4. Red dots = workers unavailable or booked
5. Admin clicks "Assign Worker" button

### Assigning a Worker
1. Worker dropdown shows all workers for that category
2. Workers sorted: Available first, then unavailable, then booked
3. Available workers have 🟢 green circle
4. Unavailable workers have 🔴 red circle
5. Booked workers have 🔵 blue circle
6. Admin selects available worker
7. Admin enters service amount
8. Admin clicks "Assign Worker"

## Database Functions

### `get_worker_availability_status(worker_id, date, time)`
Returns the availability status of a worker at a specific date/time.

**Returns:**
- `'available'` - Worker is available
- `'unavailable'` - Worker is not available
- `'booked'` - Worker already has a booking

**Usage:**
```sql
SELECT get_worker_availability_status(1, '2025-12-15', '14:00:00');
```

### `check_worker_availability(worker_id, date, time, @is_available)`
Stored procedure that checks if a worker is available.

**Usage:**
```sql
CALL check_worker_availability(1, '2025-12-15', '14:00:00', @is_available);
SELECT @is_available;
```

## File Structure

### New Files Created
- `availability_schema.sql` - Database schema for availability system
- `setup_availability.php` - Worker default availability setup page
- `AVAILABILITY_SETUP_GUIDE.md` - This guide

### Modified Files
- `manage_availability.php` - Enhanced with unavailability marking
- `admin_dashboard.php` - Added availability indicators
- `assign_booking.php` - Added availability status in worker selection

## Testing the System

### Test 1: Worker Setup
1. Login as a worker
2. Go to `setup_availability.php`
3. Click "Weekdays 9-5" button
4. Verify Monday-Friday show 09:00-17:00
5. Save and confirm success message

### Test 2: Mark Unavailable
1. Login as worker
2. Go to "Manage Availability"
3. Select tomorrow's date
4. Set time: 09:00 to 12:00
5. Reason: "Morning appointment"
6. Save and verify it appears in the unavailable slots table

### Test 3: Admin View
1. Create a booking request for tomorrow at 10:00 AM
2. Login as admin
3. Check pending requests table
4. Verify the worker you marked unavailable shows red dot
5. Other workers should show green dot (if they have default availability)

### Test 4: Worker Assignment
1. As admin, click "Assign Worker" for the booking
2. Verify workers are sorted: available first
3. Verify green/red/blue indicators
4. Try assigning an available worker
5. Confirm assignment succeeds

## Troubleshooting

### Issue: Function not found error
**Solution:** Make sure you ran `availability_schema.sql` after `improved_database.sql`

### Issue: No workers showing as available
**Possible causes:**
1. Workers haven't set up their default availability
   - **Fix:** Workers need to visit `setup_availability.php`
2. Booking time is outside worker's default hours
   - **Check:** Verify the booking time matches worker's schedule
3. Workers marked that time as unavailable
   - **Check:** Look at `worker_unavailability` table

### Issue: Green dots not showing in admin dashboard
**Check:**
```sql
-- Test the function manually
SELECT worker_id, worker_name,
       get_worker_availability_status(worker_id, '2025-12-15', '14:00:00') as status
FROM worker;
```

### Issue: Worker can't see "Setup Default Hours" button
**Solution:** Add link to worker dashboard:
```php
<a href="setup_availability.php" class="btn btn-primary">Setup Default Hours</a>
```

## Support

For issues or questions:
1. Check the database is properly set up
2. Verify all tables exist: `worker_default_availability`, `worker_unavailability`
3. Verify functions exist: `get_worker_availability_status`
4. Check worker has set default availability
5. Check for conflicting unavailability entries

## Future Enhancements

Possible improvements:
- Email notifications when availability changes
- Recurring unavailability patterns (e.g., every Saturday)
- Bulk availability import/export
- Calendar view of availability
- Mobile-friendly availability management
- Worker availability analytics
