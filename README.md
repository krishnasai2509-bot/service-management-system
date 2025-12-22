# Task Manager - PHP & MySQL Web Application

A modern, full-featured task management web application built with PHP and MySQL. This project demonstrates CRUD operations, responsive design, and clean code architecture.

## Features

- **Complete CRUD Operations**: Create, Read, Update, and Delete tasks
- **Task Management**: Organize tasks with titles, descriptions, status, and priority levels
- **Filtering System**: Filter tasks by status (Pending, In Progress, Completed) and priority (Low, Medium, High)
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Modern UI**: Beautiful gradient design with smooth animations and transitions
- **Form Validation**: Client and server-side validation for data integrity
- **Secure Database Operations**: Uses prepared statements to prevent SQL injection
- **User-Friendly Interface**: Intuitive navigation and clear visual feedback

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3
- **Server**: Apache (XAMPP)

## Project Structure

```
chandu/
├── config.php          # Database configuration and connection
├── index.php           # Main page - displays all tasks with filters
├── add_task.php        # Add new task form and handler
├── edit_task.php       # Edit existing task form and handler
├── delete_task.php     # Delete task handler
├── style.css           # Complete CSS styling
├── database.sql        # Database schema and sample data
└── README.md           # This file
```

## Installation & Setup

### Prerequisites

- XAMPP (or any Apache + MySQL + PHP stack)
- Web browser (Chrome, Firefox, Safari, Edge)

### Step-by-Step Installation

1. **Install XAMPP**
   - Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install and launch XAMPP Control Panel
   - Start Apache and MySQL services

2. **Copy Project Files**
   - The project is already in: `C:\xampp\htdocs\chandu`
   - All files are ready to use

3. **Create Database**
   - Open your web browser and go to: `http://localhost/phpmyadmin`
   - Click on "Import" tab
   - Click "Choose File" and select `database.sql` from the project folder
   - Click "Go" to import the database

   **Alternative Method (SQL Tab)**:
   - Click on "SQL" tab in phpMyAdmin
   - Copy and paste the contents of `database.sql`
   - Click "Go" to execute

4. **Configure Database Connection** (if needed)
   - Open `config.php`
   - Update database credentials if different from defaults:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');  // Leave empty for XAMPP default
     define('DB_NAME', 'task_manager');
     ```

5. **Access the Application**
   - Open your web browser
   - Navigate to: `http://localhost/chandu`
   - You should see the Task Manager application with sample tasks

## Usage Guide

### Viewing Tasks
- The main page (`index.php`) displays all tasks in a responsive grid layout
- Each task card shows:
  - Task title
  - Description
  - Status badge (Pending, In Progress, Completed)
  - Priority badge (Low, Medium, High)
  - Creation date
  - Edit and Delete buttons

### Filtering Tasks
- Use the dropdown filters to view specific tasks:
  - Filter by Status: All, Pending, In Progress, Completed
  - Filter by Priority: All, Low, Medium, High
- Filters update automatically when changed

### Adding a New Task
1. Click the "+ Add New Task" button
2. Fill in the form:
   - **Title**: Required - Enter task name
   - **Description**: Optional - Add task details
   - **Status**: Select current status
   - **Priority**: Select priority level
3. Click "Add Task" to save
4. You'll be redirected to the main page

### Editing a Task
1. Click the "Edit" button on any task card
2. Modify the task details in the form
3. Click "Update Task" to save changes
4. Click "Cancel" to discard changes

### Deleting a Task
1. Click the "Delete" button on any task card
2. Confirm the deletion in the popup dialog
3. Task will be permanently removed from the database

## Database Schema

### tasks Table

| Column      | Type                                    | Description                    |
|-------------|-----------------------------------------|--------------------------------|
| id          | INT (Primary Key, Auto Increment)       | Unique task identifier         |
| title       | VARCHAR(255)                            | Task title                     |
| description | TEXT                                    | Task description               |
| status      | ENUM('pending','in_progress','completed')| Task status                   |
| priority    | ENUM('low','medium','high')             | Task priority                  |
| created_at  | TIMESTAMP                               | Creation timestamp             |
| updated_at  | TIMESTAMP                               | Last update timestamp          |

## Features Breakdown

### Security Features
- **Prepared Statements**: All database queries use prepared statements to prevent SQL injection
- **Input Sanitization**: User input is sanitized using `htmlspecialchars()`
- **XSS Protection**: Output encoding prevents cross-site scripting attacks
- **Delete Confirmation**: JavaScript confirmation prevents accidental deletions

### User Experience Features
- **Responsive Grid Layout**: Automatically adjusts to screen size
- **Hover Effects**: Visual feedback on interactive elements
- **Status Color Coding**: Different colors for different statuses and priorities
- **Loading States**: Smooth transitions and animations
- **Form Validation**: Required field validation
- **Error Messages**: Clear error and success messages

### Code Quality Features
- **Separation of Concerns**: Database logic separated in `config.php`
- **Reusable Functions**: Connection management functions
- **Clean Code**: Well-commented and organized
- **No Inline Styles**: All CSS in external stylesheet
- **Mobile-First Design**: Responsive breakpoints for all devices

## Customization

### Changing Colors
Edit `style.css` to customize the color scheme:
- Line 8-9: Main gradient background
- Line 138-141: Button colors
- Line 214-227: Status colors
- Line 248-263: Priority colors

### Adding New Fields
1. Modify `database.sql` to add new columns
2. Update forms in `add_task.php` and `edit_task.php`
3. Update display in `index.php`
4. Add corresponding CSS styling in `style.css`

### Changing Database Credentials
Edit `config.php` and update the constants:
```php
define('DB_HOST', 'your_host');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
```

## Troubleshooting

### Common Issues

**Issue**: "Connection failed" error
- **Solution**: Make sure MySQL is running in XAMPP Control Panel
- **Solution**: Verify database credentials in `config.php`
- **Solution**: Ensure the database exists (import `database.sql`)

**Issue**: Blank page or PHP errors
- **Solution**: Check if Apache is running in XAMPP
- **Solution**: Verify PHP is enabled in Apache configuration
- **Solution**: Check file permissions (should be readable)

**Issue**: Database import fails
- **Solution**: Make sure you're using phpMyAdmin at `http://localhost/phpmyadmin`
- **Solution**: Drop existing `task_manager` database if it exists
- **Solution**: Check MySQL version compatibility

**Issue**: CSS not loading
- **Solution**: Verify `style.css` is in the same directory as `index.php`
- **Solution**: Clear browser cache (Ctrl+F5)
- **Solution**: Check browser console for 404 errors

**Issue**: Changes not showing
- **Solution**: Hard refresh the page (Ctrl+F5)
- **Solution**: Clear browser cache
- **Solution**: Check if you're editing the correct file

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Opera 76+

## Future Enhancements

Possible features to add:
- User authentication and login system
- Task assignment to multiple users
- Due dates and reminders
- Task categories/tags
- Search functionality
- Export tasks to PDF/CSV
- Dark mode toggle
- Task sorting options
- Pagination for large task lists
- File attachments
- Comments on tasks

## License

This project is open source and available for educational purposes.

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Verify all installation steps were followed
3. Ensure XAMPP services are running
4. Check browser console for JavaScript errors
5. Check Apache error logs in XAMPP

## Credits

Developed as a demonstration of PHP and MySQL integration with modern web design principles.

---

**Happy Task Managing!**
