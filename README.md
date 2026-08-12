# 🎯 Habit Tracker — Web Application

A complete, responsive, college-level **Habit Tracker Web Application** built with pure native web technologies.

Track daily habits, build streaks, visualize progress on a monthly calendar, and manage your goals with a clean, modern UI.

---

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Requirements](#-requirements)
- [Installation & Setup](#-installation--setup)
- [Default URL](#-default-url)
- [User Roles](#-user-roles)
- [Default Admin Account](#-default-admin-account)
- [Database Structure](#-database-structure)
- [Folder Structure](#-folder-structure)
- [Security Features](#-security-features)

---

## ✨ Features

### User Features
- **User Registration & Login** — Secure authentication with password hashing (BCRYPT)
- **Habit CRUD** — Create, view, edit, archive, and delete personal habits
- **Daily Habit Completion** — Mark habits as complete and undo completions
- **Streak Tracking** — Current streak and longest streak dynamically calculated from completion records
- **Dashboard** — Daily progress bar, today's habit checklist, quick stats at a glance
- **Statistics & Analytics** — Weekly/monthly breakdowns, best-performing habit, completion counts
- **Monthly Calendar** — Visual month-by-month completion history with navigation
- **Profile Management** — Edit full name and change password securely
- **Dark / Light Mode** — Theme toggle persisted via localStorage
- **Responsive Design** — Works on desktop, tablet, and mobile devices

### Admin Features
- **Admin Dashboard** — System metrics (total users, habits, completions), popular categories, recent signups
- **User Management** — View all users, activate/deactivate accounts, delete users (with self-protection guard)
- **Category CRUD** — Create, edit, and delete habit categories with foreign key safety
- **System Habits Monitor** — View all habits across all users
- **System Statistics** — Habit status distribution, category popularity

---

## 🛠 Technology Stack

| Layer        | Technology              |
|--------------|-------------------------|
| Frontend     | HTML5, CSS3, Vanilla JavaScript (ES6+) |
| Backend      | Vanilla PHP 8+          |
| Database     | MySQL (MariaDB)         |
| Server       | XAMPP Apache             |

**No frameworks, libraries, or third-party dependencies are used.**

---

## 📦 Requirements

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.x)
- A modern web browser (Chrome, Firefox, Edge, Safari)

---

## 🚀 Installation & Setup

### Step 1: Install XAMPP
Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/).

### Step 2: Start Apache and MySQL
Open the XAMPP Control Panel and start both **Apache** and **MySQL** services.

### Step 3: Place the Project
Copy the entire `habit_tracker` folder into your XAMPP web root:

```
C:\xampp\htdocs\habit_tracker\
```

### Step 4: Import the Database
1. Open **phpMyAdmin** at [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
2. Click the **Import** tab.
3. Select the file: `database/habit_tracker.sql`
4. Click **Go** to import.

This will create the `habit_tracker` database with all required tables, indexes, seed categories, and a default admin account.

### Step 5: Configure Database Connection (if needed)
The database connection is configured in `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'habit_tracker');
```

Update `DB_USER` and `DB_PASS` if your XAMPP MySQL uses different credentials.

### Step 6: Visit the Application
Open your browser and navigate to:

```
http://localhost/habit_tracker/
```

---

## 🌐 Default URL

```
http://localhost/habit_tracker/
```

---

## 👥 User Roles

| Role  | Description | Dashboard URL |
|-------|-------------|---------------|
| User  | Normal registered user who creates and tracks habits | `/user/dashboard.php` |
| Admin | System administrator who manages users and categories | `/admin/dashboard.php` |

---

## 🔑 Default Admin Account

| Field    | Value                        |
|----------|------------------------------|
| Username | `admin`                      |
| Email    | `admin@habit-tracker.local`  |
| Password | `admin123`                   |

> ⚠️ **Change the default admin password after first login.**

---

## 🗄 Database Structure

**Database Name:** `habit_tracker`

### Tables

| Table               | Purpose                                      |
|---------------------|----------------------------------------------|
| `users`             | User accounts, authentication, roles, status |
| `categories`        | Habit categories (system-wide)               |
| `habits`            | User-created habits with metadata            |
| `habit_completions` | Daily completion logs per habit               |

### Entity Relationships

```
USERS (1) ────< (N) HABITS (N) >──── (1) CATEGORIES
                     │
                     │ 1:N
                     ▼
              HABIT_COMPLETIONS
```

- `users` 1:N `habits` — One user can have many habits (`ON DELETE CASCADE`)
- `categories` 1:N `habits` — Each habit belongs to one category (`ON DELETE RESTRICT`)
- `habits` 1:N `habit_completions` — Each habit has many completion entries (`ON DELETE CASCADE`)
- `UNIQUE(habit_id, completion_date)` prevents duplicate completions on the same day

### Key Design Decisions
- **No stored statistics** — Streaks, completion rates, and counts are calculated dynamically from `habit_completions`
- **No `user_settings` table** — Dark mode preference uses JavaScript `localStorage`
- **Soft-delete preferred** — Habits are archived (`status = 'archived'`) rather than permanently deleted

---

## 📁 Folder Structure

```
habit_tracker/
├── index.php                  # Public landing page
├── login.php                  # Login page
├── register.php               # Registration page
├── logout.php                 # Session destruction
├── README.md                  # This file
│
├── config/
│   └── database.php           # PDO database connection
│
├── includes/
│   ├── header.php             # HTML head & CSS imports
│   ├── footer.php             # Footer & JS imports
│   ├── navbar.php             # Top navigation header
│   ├── sidebar.php            # Dashboard sidebar navigation
│   ├── auth.php               # Authentication & authorization guards
│   └── functions.php          # Validation, streak calc, flash messages
│
├── actions/
│   ├── login.php              # Login processing
│   ├── register.php           # Registration processing
│   ├── add-habit.php          # Create habit
│   ├── update-habit.php       # Update habit (ownership verified)
│   ├── delete-habit.php       # Archive/delete habit
│   ├── complete-habit.php     # Mark habit complete
│   ├── undo-completion.php    # Remove completion record
│   ├── profile.php            # Update profile name
│   ├── password.php           # Change password
│   ├── user-actions.php       # Admin: user management
│   └── category-actions.php   # Admin: category CRUD
│
├── user/
│   ├── dashboard.php          # User main dashboard
│   ├── habits.php             # Habit management list
│   ├── add-habit.php          # Add habit form
│   ├── edit-habit.php         # Edit habit form
│   ├── statistics.php         # Analytics & streaks
│   ├── calendar.php           # Monthly completion calendar
│   └── profile.php            # Profile & password settings
│
├── admin/
│   ├── dashboard.php          # Admin overview dashboard
│   ├── users.php              # User management
│   ├── habits.php             # System habits monitor
│   ├── categories.php         # Category CRUD
│   └── statistics.php         # System analytics
│
├── assets/
│   ├── css/
│   │   ├── style.css          # Design system & base styles
│   │   ├── auth.css           # Login/register page styles
│   │   ├── dashboard.css      # Layout, cards, stat grids
│   │   ├── habits.css         # Habit cards, tables, badges
│   │   ├── admin.css          # Admin-specific styles
│   │   └── responsive.css     # Mobile/tablet breakpoints
│   └── js/
│       ├── validation.js      # Client-side form validation
│       ├── dashboard.js       # Dashboard interactions
│       ├── habits.js          # Habit action confirmations
│       ├── calendar.js        # Calendar day interactions
│       ├── admin.js           # Admin confirmation dialogs
│       └── theme.js           # Dark/light mode toggle
│
└── database/
    └── habit_tracker.sql      # Full schema + seed data
```

---

## 🔒 Security Features

| Feature | Implementation |
|---------|---------------|
| **Password Hashing** | `password_hash(PASSWORD_BCRYPT)` and `password_verify()` |
| **SQL Injection Prevention** | PDO prepared statements on all queries |
| **XSS Protection** | `htmlspecialchars()` via `e()` helper on all output |
| **Session Authentication** | PHP sessions with `session_regenerate_id()` on login |
| **Role-Based Authorization** | Server-side role checks on every protected page and action |
| **Resource Ownership** | Habit operations verify `habit.user_id === session_user_id` |
| **Admin Self-Protection** | Admins cannot deactivate or delete their own account |
| **Generic Login Errors** | "Invalid username/email or password" prevents user enumeration |
| **Client + Server Validation** | JavaScript validation for UX, PHP validation as authority |

---

## 📄 License

This project is a college-level academic demonstration built for educational purposes.

---

**Built with ❤️ using HTML5 + CSS3 + Vanilla JavaScript + Vanilla PHP + MySQL**
