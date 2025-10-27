# JU Campus Notes & Resource Hub

A comprehensive web-based platform for students at Jahangirnagar University to collaboratively share, organize, and access academic resources including notes, assignments, past papers, and study materials.

## Features

- **User Authentication**: Secure registration and login with @juniv.edu email validation
- **Resource Management**: Upload, browse, search, and download academic materials
- **Advanced Search**: Filter by faculty, department, course, resource type, and more
- **Social Features**: Comments, bookmarks, and resource ratings
- **User Dashboard**: Track uploads, downloads, bookmarks, and activity
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Analytics**: View trending resources and platform statistics

## Technologies Used

- **Frontend**: HTML5, CSS3, Tailwind CSS, JavaScript, jQuery
- **Backend**: PHP 8.0+
- **Database**: MySQL
- **Server**: Apache (via XAMPP)
- **Icons**: Font Awesome 6.4

## Installation Guide

### Prerequisites

1. **XAMPP** (or any Apache + MySQL + PHP stack)
   - Download from: https://www.apachefriends.org/
   - Ensure PHP 8.0+ and MySQL are installed

### Step 1: Clone/Copy Project

1. Copy the `campus-notes-hub` folder to your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\campus-notes-hub\
   ```

### Step 2: Database Setup

1. Start XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Open phpMyAdmin: http://localhost/phpmyadmin
4. Click on "Import" tab
5. Select the file: `campus-notes-hub/database/schema.sql`
6. Click "Go" to import the database

**OR** Run the SQL manually:
- Click "SQL" tab in phpMyAdmin
- Copy and paste contents of `schema.sql`
- Click "Go"

### Step 3: Configure Database Connection

The default configuration should work with standard XAMPP setup. If you need to change database credentials:

Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campus_notes_hub');
```

### Step 4: Create Upload Directory

The system will attempt to create the upload directory automatically. If it fails:

1. Create a folder named `uploads` in the project root:
   ```
   campus-notes-hub/uploads/
   ```
2. Set write permissions (777 on Linux/Mac)

### Step 5: Access the Application

Open your browser and navigate to:
```
http://localhost/campus-notes-hub/
```

## Default Test Account

After installation, you can register a new account using any @juniv.edu email format.

**Note**: Email verification is automatically set to TRUE for demonstration purposes.

## Project Structure

```
campus-notes-hub/
├── index.php                 # Home page
├── contact.php              # Contact admin page
├── auth/                    # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── dashboard/               # User dashboard
│   ├── user-dashboard.php
│   ├── profile.php
│   └── settings.php
├── resources/               # Resource management
│   ├── browse.php
│   ├── upload.php
│   └── view.php
├── api/                     # API handlers
│   ├── comment-handler.php
│   ├── bookmark-handler.php
│   └── download-handler.php
├── config/                  # Configuration files
│   ├── database.php
│   └── init.php
├── database/                # Database schema
│   └── schema.sql
└── uploads/                 # Uploaded files storage
```

## Usage Guide

### For Students

1. **Register**: Create an account with your @juniv.edu email
2. **Browse**: Explore resources by faculty, department, or course
3. **Search**: Use advanced filters to find specific materials
4. **Download**: Access and download resources you need
5. **Upload**: Share your notes and study materials
6. **Bookmark**: Save resources for quick access later
7. **Comment**: Provide feedback and ask questions

### For Admins

Admin emails are configured in:
- 20220654965shimul1@juniv.edu
- 20220654976oywon@juniv.edu
- 20220654977ahad@juniv.edu
- 20220655000nusaiba@juniv.edu

## Features Detail

### Resource Types
- Notes
- Assignments
- Past Papers
- Books
- Other

### Supported File Formats
- Documents: PDF, DOC, DOCX
- Presentations: PPT, PPTX
- Spreadsheets: XLS, XLSX
- Images: JPG, JPEG, PNG
- Archives: ZIP

### Maximum File Size
- 50 MB per file

## Security Features

- Password hashing using PHP's password_hash()
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session-based authentication
- Email validation for @juniv.edu domain

## Troubleshooting

### Database Connection Error
- Verify MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Ensure database `campus_notes_hub` exists

### File Upload Fails
- Check `uploads/` directory exists and has write permissions
- Verify file size is under 50MB
- Ensure file type is in allowed extensions list

### Page Not Loading
- Verify Apache is running in XAMPP
- Check you're accessing: http://localhost/campus-notes-hub/
- Clear browser cache

### Session Issues
- Ensure PHP session support is enabled
- Check folder permissions for PHP session directory

## Browser Support

- Chrome (Recommended)
- Firefox
- Safari
- Edge
- Opera

## Contributing

This is an academic project. For improvements or issues, contact the admin team.

## License

© 2025 JU Campus Notes Hub. Academic project for Jahangirnagar University.

## Contact

For support or inquiries, contact the admin team through the contact page or via email.

## Credits

Developed by the JU Notes Hub Team
- Shimul (20220654965shimul1@juniv.edu)
- Oywon (20220654976oywon@juniv.edu)
- Ahad (20220654977ahad@juniv.edu)
- Nusaiba (20220655000nusaiba@juniv.edu)
