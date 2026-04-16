# 📋 Customer Service System
### Complete PHP + MySQL + phpMyAdmin Solution

---

## 📁 FILES INCLUDED

```
css_system/
├── index.php          ← Main system UI (all records, search, filter)
├── api.php            ← AJAX backend (CRUD + archive + stats)
├── export.php         ← Export handler (PDF / PNG / Excel / CSV)
├── setup.php          ← Web-based installer (run this first!)
├── setup_run.php      ← Setup backend
├── database.sql       ← Full database schema + seed data
└── includes/
    └── config.php     ← Database credentials
```

---

## 🚀 INSTALLATION (Step-by-Step)

### Step 1 — Requirements
- PHP 8.0+ with MySQLi extension
- MySQL 5.7+ or MariaDB 10.4+
- Web server: XAMPP / WAMP / LARAGON / any phpMyAdmin stack

### Step 2 — Copy Files
Copy the entire `css_system/` folder into your web root:
- **XAMPP** → `C:\xampp\htdocs\css_system\`
- **WAMP**  → `C:\wamp64\www\css_system\`
- **Linux** → `/var/www/html/css_system/`

### Step 3 — Run the Installer
Open your browser and go to:
```
http://localhost/css_system/setup.php
```
Enter your database credentials and click **Install**. This will:
- Create the database automatically
- Create all tables (cs_records, cs_records_archive, cs_dropdown_options)
- Insert dropdown options and 5 sample records
- Update config.php with your credentials

### Step 4 — Open the System
```
http://localhost/css_system/index.php
```

### Alternative: Manual SQL Import
1. Open **phpMyAdmin** → create a database named `customer_service_db`
2. Click **Import** → choose `database.sql` → click **Go**
3. Edit `includes/config.php` with your DB credentials

---

## 🗃️ DATABASE TABLES

### `cs_records` — Main records table
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| reference_no | VARCHAR(30) UNIQUE | Auto-generated CS-YYYY-NNNNN |
| account_number | VARCHAR(50) | Account number |
| account_name | VARCHAR(150) | Full account name |
| address | TEXT | Complete address |
| landmark | VARCHAR(255) | Optional landmark |
| contact_no | VARCHAR(20) | Phone number |
| messenger_caller | VARCHAR(150) | Who reported |
| concern | VARCHAR(100) | Type of concern (dropdown) |
| area_dept | VARCHAR(100) | Department endorsed to (dropdown) |
| date_forwarded | DATE | Date concern was forwarded |
| notes | TEXT | Additional notes |
| status | ENUM | Open / In Progress / Resolved / Closed |
| created_at | TIMESTAMP | Auto timestamp |
| updated_at | TIMESTAMP | Auto updated |

### `cs_records_archive` — Archived (soft-deleted) records
Same columns as cs_records plus:
- `original_id` — original record ID
- `archived_at` — when archived
- `archived_by` — who archived

### `cs_dropdown_options` — Configurable dropdown options
- Add/edit concerns, departments, statuses directly in phpMyAdmin

---

## ✨ FEATURES

- ✅ **Full CRUD** — Create, Read, Update, Delete records
- ✅ **Archive System** — Deleted records go to archive, not permanently deleted
- ✅ **Restore** — Restore archived records back to active
- ✅ **Smart Search** — Search by ref no, account number, account name, contact
- ✅ **Filters** — Filter by concern, department, status, date range
- ✅ **Dashboard Stats** — Total, Open, In Progress, Resolved, Today, Archived
- ✅ **Auto Reference No** — CS-YYYY-NNNNN format, auto-generated
- ✅ **Dropdowns** — All configurable from database (concern types, departments)
- ✅ **Export to PDF** — Opens print dialog for PDF save
- ✅ **Export to PNG** — Screenshot using html2canvas, click to download
- ✅ **Export to Excel** — CSV file with UTF-8 BOM (opens perfectly in Excel)
- ✅ **Export to CSV** — Spreadsheet-compatible CSV
- ✅ **Pagination** — 15 records per page
- ✅ **Responsive UI** — Works on desktop, tablet, mobile

---

## 🔧 CUSTOMIZATION

### Add dropdown options
In phpMyAdmin, insert into `cs_dropdown_options`:
```sql
INSERT INTO cs_dropdown_options (category, value, sort_order)
VALUES ('concern', 'Your New Concern', 13);
```
Categories: `concern`, `area_dept`, `status`

### Change records per page
In `includes/config.php`:
```php
define('RECORDS_PER_PAGE', 15);  // change to any number
```

---

## 📞 SUPPORT
For issues, check:
1. PHP error logs
2. Browser console (F12) for JS errors
3. Ensure MySQLi extension is enabled in php.ini
