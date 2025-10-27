# Quick Installation Guide

## Step-by-Step Setup Instructions

### 1. Install XAMPP
- Download XAMPP from https://www.apachefriends.org/
- Install with default settings
- Make sure Apache and MySQL are installed

### 2. Copy Project Files
1. Copy the entire `campus-notes-hub` folder
2. Paste it into: `C:\xampp\htdocs\`
3. Final path should be: `C:\xampp\htdocs\campus-notes-hub\`

### 3. Start XAMPP Services
1. Open XAMPP Control Panel
2. Click **Start** next to Apache
3. Click **Start** next to MySQL
4. Wait for both to turn green

### 4. Create Database
**Option A: Using phpMyAdmin (Recommended)**
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click on **Import** tab at the top
3. Click **Choose File**
4. Navigate to: `C:\xampp\htdocs\campus-notes-hub\database\schema.sql`
5. Click **Go** button at the bottom
6. Wait for success message

**Option B: Manual SQL**
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar
3. Database name: `campus_notes_hub`
4. Click **Create**
5. Click on the new database name
6. Click **SQL** tab
7. Open `schema.sql` in a text editor, copy all contents
8. Paste into the SQL box
9. Click **Go**

### 5. Verify Installation
1. Open browser
2. Go to: `http://localhost/campus-notes-hub/`
3. You should see the home page with:
   - JU Notes Hub logo
   - Search bar
   - Login/Sign Up buttons
   - Statistics showing 0 resources (initially)

### 6. Create Your First Account
1. Click **Sign Up** button
2. Fill in the form with:
   - Your full name
   - Email ending with **@juniv.edu** (required!)
   - Select your faculty and department
   - Enter your batch (e.g., Batch 51)
3. Click **Create Account**
4. You'll be automatically logged in

### 7. Test the System
After logging in, try these features:
- ✅ Browse resources
- ✅ Upload a test file
- ✅ Search for resources
- ✅ View your dashboard
- ✅ Update your profile

## Common Issues & Solutions

### ❌ "Database connection failed"
**Solution:**
- Make sure MySQL is running in XAMPP (should be green)
- Check database name is `campus_notes_hub`
- Verify you imported the schema.sql file

### ❌ "Cannot find the file"
**Solution:**
- Check project is in: `C:\xampp\htdocs\campus-notes-hub\`
- Make sure Apache is running (green in XAMPP)
- Try: `http://localhost/campus-notes-hub/` (not `campus-notes-hub/index.php`)

### ❌ File upload doesn't work
**Solution:**
- Create `uploads` folder in project root if it doesn't exist
- Right-click folder → Properties → Security → Edit → Allow "Full control"

### ❌ Blank white page
**Solution:**
- Check Apache is running
- Look at XAMPP Apache error logs
- Make sure PHP version is 7.4 or higher

### ❌ Cannot register with email
**Solution:**
- Email MUST end with `@juniv.edu`
- Example: `yourname@juniv.edu`
- Check password meets requirements (8+ chars, uppercase, lowercase, numbers)

## System Requirements

✅ Windows 7/8/10/11  
✅ XAMPP with PHP 7.4+  
✅ MySQL 5.7+  
✅ 500MB free disk space  
✅ Modern web browser (Chrome, Firefox, Edge)

## Default Configuration

- **Database Host:** localhost
- **Database Name:** campus_notes_hub
- **Database User:** root
- **Database Password:** (empty)
- **Max Upload Size:** 50MB
- **Allowed File Types:** PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, ZIP

## Need Help?

Contact the admin team:
- 20220654965shimul1@juniv.edu
- 20220654976oywon@juniv.edu
- 20220654977ahad@juniv.edu
- 20220655000mymuna@juniv.edu

## Testing Checklist

After installation, test these features:

- [ ] Home page loads correctly
- [ ] Can register new account
- [ ] Can login with credentials
- [ ] Dashboard shows user stats
- [ ] Can upload a file
- [ ] Can browse resources
- [ ] Can search for resources
- [ ] Can view resource details
- [ ] Can download resources
- [ ] Can add comments
- [ ] Can bookmark resources
- [ ] Profile page works
- [ ] Settings page works

If all items work, installation is successful! 🎉
