# Secure Login & Authentication System
**Academic Project: University Security & Authentication Implementation**

---

## 📌 Project Overview
This project provides two flexible ways to run and demonstrate the system:
1. 🌟 **Standalone Desktop Web Page (`SECURE_LOGIN_SYSTEM.html`)**: **Zero-Setup & Zero-Software!** Double-click directly on your Desktop to open in any web browser without needing XAMPP, Apache, or MySQL.
2. 🛠️ **Full PHP/MySQL Stack (`SIMPLE_LOGIN_SYSTEM_PUIT24110007`)**: The complete backend implementation for university code submission and defense.

The system demonstrates **4 Core Security Pillars**:
1. **User Identification**: Unique usernames and emails, input validation, and secure session management.
2. **Two-Factor Authentication (2FA)**: Time-based 6-digit OTP with a 5-minute expiration window, single-use invalidation, and timing-attack resistant verification (`hash_equals`).
3. **Role-Based Authorization (RBAC)**: Distinct permissions for **Administrator** and **Normal User**, protected dashboards, and tamper-resistant URL routing (`access_denied.php`).
4. **Password Security (Salt + Pepper)**:
   - Unique 128-bit random salt per user (stored in the database).
   - Secret server-side pepper key (stored outside the database in `config.php`).
   - Combined via `hash_hmac('sha256', ...)` and hashed using PHP's native `password_hash()` (bcrypt).
   - Zero plain-text passwords stored or logged.

---

## 📂 Project Directory Structure

```text
SIMPLE_LOGIN_SYSTEM_PUIT24110007/
├── config.php            # Security constants, Server Pepper, Session configuration & Crypto helpers
├── db.php                # Secure PDO connection instance with native prepared statements
├── database.sql          # Complete MySQL database and users table schema
├── setup_db.php          # 1-Click Browser Installer: creates DB, tables & seeds demo accounts
├── index.php             # Intelligent entry point / router
├── register.php          # User registration with validation, uniqueness checks & Salt+Pepper
├── login.php             # First factor (Username/Email + Password), triggers 2FA OTP generation
├── verify_otp.php        # Second factor (OTP entry, 5-min expiry check, single-use invalidation)
├── admin_dashboard.php   # Protected Administrator Portal (role: admin)
├── user_dashboard.php    # Protected User Portal (role: user) with interactive RBAC test
├── access_denied.php     # HTTP 403 Forbidden page for unauthorized role attempts
├── logout.php            # Complete session destruction & cookie clearance
├── style.css             # Modern, responsive UI with security badges & OTP demonstration banners
└── README.md             # Complete documentation and defense guide
```

---

## 🚀 Quick Setup & Installation Guide

### Step 1: Start XAMPP
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL** (configured on port **3307** to avoid conflict with standard Windows MySQL services).

---

### Step 2: Make the Project Accessible to XAMPP

Apache serves files from `C:\xampp\htdocs\`. You have two easy options:

#### Option A: Create a Directory Junction (Recommended - No file copying needed)
Open PowerShell or Command Prompt as Administrator and run:
```cmd
mklink /J "C:\xampp\htdocs\simple_auth" "C:\Users\HP\Desktop\SIMPLE_LOGIN_SYSTEM_PUIT24110007"
```
*Now, any changes in your desktop folder immediately reflect in XAMPP!*

#### Option B: Copy Folder
Copy the folder `SIMPLE_LOGIN_SYSTEM_PUIT24110007` directly into:
`C:\xampp\htdocs\`

---

### Step 3: Initialize the Database (1-Click Setup)
1. Open your browser and navigate to:
   ```text
   http://localhost/simple_auth/setup_db.php
   ```
   *(or `http://localhost/SIMPLE_LOGIN_SYSTEM_PUIT24110007/setup_db.php` if you copied the folder with its original name)*
2. The script will automatically:
   - Create the `authentication_system` database.
   - Create the `users` table with all indexes.
   - Seed two pre-configured demo accounts.

*(Alternatively, you can manually import `database.sql` inside **phpMyAdmin** at `http://localhost/phpmyadmin`)*

---

## 🔑 Pre-Seeded Test Accounts

| Role | Username | Password | Email |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `Admin@1234` | `admin@system.local` |
| **Normal User** | `student` | `Student@1234` | `student@university.edu` |

---

## 🛡️ Security Pillars Explained (For Academic Defense)

### 1. User Identification
- **Dual Identifier Login**: Users can sign in using either their **Username** or their **Email address**.
- **Prepared Statements**: All SQL queries use PDO parameter binding (`:username`, `:email`, etc.) with `PDO::ATTR_EMULATE_PREPARES => false`. This guarantees immunity to SQL Injection.
- **Anti-Enumeration Error Messages**: Login displays generic error messages (*"Invalid credentials"*) so attackers cannot deduce whether a username exists.

### 2. Password Security (Salt + Pepper)
```text
[Plain Password] + [Unique User Salt] ──(HMAC-SHA256 with Server PEPPER)──> [Fixed 64-char Digest] ──(password_hash)──> [Bcrypt Hash in DB]
```
- **Why Salt?** A unique 128-bit random salt (`bin2hex(random_bytes(16))`) is generated for each user and stored in the database. It prevents Rainbow Table attacks and ensures identical passwords result in completely different hashes.
- **Why Pepper?** The pepper key is defined in `config.php` and **never stored in the database**. If an attacker obtains a database dump, they cannot crack the passwords without also acquiring the server configuration file.
- **Why HMAC before Bcrypt?** Bcrypt truncates passwords longer than 72 bytes. Passing the password and salt through `hash_hmac('sha256', ...)` normalizes any length password into a 64-character hex string, eliminating the truncation vulnerability.

### 3. Two-Factor Authentication (OTP 2FA)
- After password verification, a cryptographically secure 6-digit code is generated via `random_int(0, 999999)`.
- The OTP is stored in the database with an expiration timestamp (`CURRENT_TIME + 300 SECONDS` = 5 minutes).
- **Single-Use Enforcement**: The moment an OTP is validated, it is set to `NULL` in the database to prevent replay attacks.
- **Academic Demonstration Banner**: Because real SMS/Email gateways require external paid APIs, the generated OTP is surfaced in a high-visibility demo banner on `verify_otp.php`, along with a **"Request New OTP"** button.

### 4. Role-Based Access Control (RBAC)
- **Session Regeneration**: Upon successful 2FA verification, `session_regenerate_id(true)` is invoked to eliminate **Session Fixation attacks**.
- **Authorization Enforcement**: 
  - `admin_dashboard.php` enforces: `if ($_SESSION['role'] !== 'admin') { header('Location: access_denied.php'); exit; }`
  - Normal users attempting to bypass access control by typing the URL directly are intercepted and routed to `access_denied.php` with an **HTTP 403 Forbidden** status code.
- **Interactive RBAC Test**: The Normal User dashboard features a dedicated button to test the RBAC defense mechanism live for your instructor.

---

## 🧪 Testing the Complete Workflow

1. **Test Registration**:
   - Go to `register.php`.
   - Register a user `alice` with role `Normal User`.
   - Try registering again with the same username/email to verify uniqueness validation.

2. **Test Login & 2FA**:
   - Go to `login.php` and sign in with `alice` or `admin`.
   - You will be redirected to `verify_otp.php`.
   - Check the demo banner for the 6-digit OTP.
   - Enter an incorrect OTP to confirm validation fails.
   - Click "Request New OTP" to test re-issuance.
   - Enter the correct OTP to gain access.

3. **Test RBAC & URL Protection**:
   - Log in as `student` (Normal User).
   - Click **"Test RBAC Defense (Try Accessing Admin Dashboard)"**.
   - Confirm you are blocked and taken to `access_denied.php`.
   - Log out and sign in as `admin`.
   - Confirm you now have access to `admin_dashboard.php` and can view all registered users.

4. **Test Logout**:
   - Click **Log Out** on any dashboard.
   - Verify that your session is destroyed and you cannot access the dashboards using the browser's Back button.
