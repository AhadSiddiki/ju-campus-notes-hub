# 🚀 Quick Start Guide

Get the JU Campus Notes Hub running in **5 minutes**!

## ⚡ Fast Setup (5 Steps)

### Step 1: Install XAMPP (If not installed)
Download from: https://www.apachefriends.org/
- Click "Download" for Windows
- Run installer with default settings
- Complete installation

### Step 2: Copy Project
1. Locate this folder: `campus-notes-hub`
2. Copy the entire folder
3. Paste into: `C:\xampp\htdocs\`

### Step 3: Start Services
1. Open **XAMPP Control Panel** (search in Windows)
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Wait until both show green "Running"

### Step 4: Import Database
1. Open browser
2. Go to: `http://localhost/phpmyadmin`
3. Click **Import** (top menu)
4. Click **Choose File**
5. Select: `C:\xampp\htdocs\campus-notes-hub\database\schema.sql`
6. Click **Go** (bottom of page)
7. Wait for "Import has been successfully finished"

### Step 5: Access Application
1. Open browser
2. Go to: `http://localhost/campus-notes-hub/`
3. You should see the home page!

## ✅ Verify It's Working

You should see:
- ✅ JU Notes Hub logo and header
- ✅ Search bar in the middle
- ✅ Login and Sign Up buttons
- ✅ Statistics showing (0s initially)
- ✅ Features section at bottom

## 🎯 First Actions

### Create Your Account:
1. Click **Sign Up** button
2. Fill in the form:
   - **Name**: Your full name
   - **Email**: MUST end with `@juniv.edu` (e.g., john2023@juniv.edu)
   - **Faculty**: Select your faculty
   - **Department**: Select department
   - **Batch**: Enter your batch (e.g., 2020-2021)
   - **Password**: Min 8 chars, must have:
     - At least one uppercase letter (A-Z)
     - At least one lowercase letter (a-z)
     - At least one number (0-9)
3. Click **Create Account**
4. You'll be logged in automatically!

### Upload Your First Resource:
1. After login, click **Upload** in navigation
2. Fill in:
   - **Title**: Name of your resource
   - **Description**: What it's about
   - **Course**: Select the course
   - **Type**: Notes/Assignment/Past Paper/Book/Other
   - **File**: Click to select file (or drag & drop)
3. Click **Upload Resource**
4. Success! View it in Browse section

### Explore Features:
- 📚 **Browse**: See all resources
- 🔍 **Search**: Find specific materials
- 📊 **Dashboard**: View your stats
- 👤 **Profile**: See your uploads
- ⚙️ **Settings**: Update your info
- 📈 **Analytics**: Platform statistics

## 🎨 Page URLs

Once running, access these pages:

| Page | URL |
|------|-----|
| Home | `http://localhost/campus-notes-hub/` |
| Sign Up | `http://localhost/campus-notes-hub/auth/register.php` |
| Login | `http://localhost/campus-notes-hub/auth/login.php` |
| Browse | `http://localhost/campus-notes-hub/resources/browse.php` |
| Upload | `http://localhost/campus-notes-hub/resources/upload.php` |
| Dashboard | `http://localhost/campus-notes-hub/dashboard/user-dashboard.php` |
| Profile | `http://localhost/campus-notes-hub/dashboard/profile.php` |
| Settings | `http://localhost/campus-notes-hub/dashboard/settings.php` |
| Analytics | `http://localhost/campus-notes-hub/dashboard/analytics.php` |
| Contact | `http://localhost/campus-notes-hub/contact.php` |

## ❌ Common Issues & Quick Fixes

### Issue: "Database connection failed"
**Fix:**
- Make sure MySQL is running (green in XAMPP)
- Check you imported `schema.sql`
- Database name should be: `campus_notes_hub`

### Issue: "Page not found" or "404"
**Fix:**
- Verify folder is in: `C:\xampp\htdocs\campus-notes-hub\`
- Check Apache is running (green in XAMPP)
- Try closing and reopening browser
- Clear browser cache (Ctrl+F5)

### Issue: Can't upload files
**Fix:**
- Create `uploads` folder inside `campus-notes-hub`
- Make sure folder has write permissions
- Check file size is under 50MB

### Issue: Can't register
**Fix:**
- Email MUST end with `@juniv.edu`
- Password must meet requirements (8+ chars, uppercase, lowercase, number)
- All required fields must be filled

### Issue: Blank white page
**Fix:**
- Stop and restart Apache in XAMPP
- Check PHP errors in: `C:\xampp\apache\logs\error.log`
- Make sure PHP version is 7.4+

## 🔥 Pro Tips

1. **Use Chrome or Firefox** for best experience
2. **Test with multiple accounts** to see collaboration features
3. **Upload sample files** to test all features
4. **Check analytics page** to see platform statistics
5. **Bookmark frequently used pages**

## 📱 Test on Mobile

1. Find your computer's IP address:
   - Open Command Prompt
   - Type: `ipaddress`
   - Look for IPv4 Address (e.g., 192.168.1.100)

2. On mobile browser, go to:
   - `http://YOUR-IP/campus-notes-hub/`
   - Example: `http://192.168.1.100/campus-notes-hub/`

## 🎓 Sample Test Data

### Test User:
- **Email**: test2023@juniv.edu
- **Password**: Test1234
- **Faculty**: Faculty of Mathematical and Physical Sciences
- **Department**: Department of Computer Science & Engineering

### Sample Upload:
- **Title**: Data Structures Lecture Notes - Chapter 1
- **Description**: Comprehensive notes on arrays, linked lists, and basic data structures
- **Course**: CSE-201 Data Structures and Algorithms
- **Type**: Notes
- **File**: Any PDF file

## 🆘 Need Help?

1. **Check INSTALLATION.md** for detailed setup
2. **Read README.md** for full documentation
3. **Contact admins** via contact page
4. **Review PROJECT_SUMMARY.md** for features list

## 🎉 Success!

If you can:
- ✅ See the home page
- ✅ Register an account
- ✅ Login successfully
- ✅ Upload a file
- ✅ Browse and download resources

**Congratulations! The system is fully functional!** 🎊

---

**Enjoy using JU Campus Notes Hub!**

Built for Jahangirnagar University Students 💙
