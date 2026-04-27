# UB Online Admissions System
## CSI315 — Web Technology and Applications

---

## 📁 Project Structure

```
ub_admission/
├── index.php              ← Homepage
├── register.php           ← Student registration
├── login.php              ← Student login
├── dashboard.php          ← Student dashboard
├── application.php        ← Application form (BGCSE + programme)
├── documents.php          ← Document upload
├── profile.php            ← Update personal info
├── logout.php             ← Logout
│
├── admin/
│   ├── login.php          ← Admin login
│   ├── dashboard.php      ← Admin dashboard
│   ├── applications.php   ← Manage all applications
│   ├── view_application.php ← View individual application
│   ├── rankings.php       ← BGCSE rankings by programme
│   └── logout.php         ← Admin logout
│
├── includes/
│   └── db.php             ← Database connection config
│
├── css/
│   └── style.css          ← Main stylesheet
│
├── uploads/               ← Uploaded documents stored here
│   (auto-created)
│
└── database.sql           ← Run this to set up the database
```

---

## ⚙️ Setup Instructions

### Step 1: Set up the Database
1. Open **phpMyAdmin** (or MySQL command line)
2. Import / run the file `database.sql`
3. This creates the `ub_admission` database and all tables

### Step 2: Configure DB Connection
Open `includes/db.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Your MySQL username
define('DB_PASS', '');           // Your MySQL password
define('DB_NAME', 'ub_admission');
```

### Step 3: Upload to Server
- Upload the entire `ub_admission/` folder to your web server's public directory
- E.g., on XAMPP: `C:/xampp/htdocs/ub_admission/`
- Make sure the `uploads/` folder is writable (`chmod 755 uploads/`)

### Step 4: Access the System
- Student Portal: `http://localhost/ub_admission/`
- Admin Portal: `http://localhost/ub_admission/admin/login.php`

---

## 🔐 Default Admin Credentials
- **Username:** `admin`
- **Password:** `admin123`

> ⚠️ Change this password after first login for security!

---

## ✅ Features Implemented

| Feature | Status |
|---|---|
| Student registration | ✅ |
| Student login / logout | ✅ |
| Application form with BGCSE results | ✅ |
| Auto-calculation of BGCSE total points | ✅ |
| Document upload (PDF, JPG, PNG) | ✅ |
| Lock academic qualifications after submit | ✅ |
| Students can update personal info anytime | ✅ |
| Admin login | ✅ |
| Admin - view all applications | ✅ |
| Admin - update application status | ✅ |
| Admin - view individual application details | ✅ |
| Admin - BGCSE ranking (descending order) | ✅ |
| Rankings for BSc General (and all programmes) | ✅ |
| Responsive design | ✅ |
| University of Botswana branding | ✅ |

---

## 🎓 BGCSE Grade Points
| Grade | Points |
|---|---|
| A* | 9 |
| A | 8 |
| B | 7 |
| C | 6 |
| D | 5 |
| E | 4 |
| U | 0 |
