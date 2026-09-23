# HabitFlow — Habit Tracking Web Application

HabitFlow is a web-based habit tracking application that allows users to create and manage habits, track daily progress, monitor streaks, and analyze completion history.

The application includes separate user and administrator functionality, with a focus on authentication, database relationships, CRUD operations, and a responsive user interface.

**Project Status:** Completed — Academic Project

---

## Features

### User Features

* User registration and login
* Password hashing and secure authentication
* Create, view, edit, archive, and delete habits
* Daily habit completion tracking
* Undo habit completions
* Current and longest streak tracking
* Dashboard with daily progress and habit statistics
* Weekly and monthly statistics
* Monthly completion calendar
* Profile management
* Password change functionality
* Habit categories
* Dark and light theme with persistent preference
* Responsive design for desktop, tablet, and mobile

### Admin Features

* Admin dashboard with system-level metrics
* User management
* Activate and deactivate user accounts
* Delete users with administrative safeguards
* Category management
* Create, edit, and delete habit categories
* System-wide habit monitoring
* System statistics
* Category popularity and habit status statistics

---

## Technology Stack

| Layer                   | Technology                             |
| ----------------------- | -------------------------------------- |
| Frontend                | HTML5, CSS3, Vanilla JavaScript (ES6+) |
| Backend                 | PHP 8+                                 |
| Database                | MySQL / MariaDB                        |
| Web Server              | Apache                                 |
| Development Environment | XAMPP                                  |
| Database Management     | phpMyAdmin                             |

The application is built without frontend frameworks, backend frameworks, npm packages, or third-party libraries.

---

## Key Implementation Details

* **Authentication:** PHP session-based authentication
* **Password Security:** `password_hash()` and `password_verify()`
* **Database Access:** PDO with prepared statements
* **Authorization:** Server-side role-based access control
* **Ownership Verification:** Users can only modify their own habits
* **Input Validation:** Client-side and server-side validation
* **XSS Protection:** Escaped HTML output using a reusable `e()` helper
* **Streak Calculation:** Streaks are calculated from completion records rather than stored as fixed values
* **Habit Archiving:** Habits can be archived instead of immediately being permanently removed
* **Theme Persistence:** Dark/light mode preference is stored using browser `localStorage`
* **Database Constraints:** Foreign keys and unique constraints maintain data integrity

---

## Application Workflow

```text
Register / Login
       ↓
Create Habit
       ↓
Set Habit Details
       ↓
Track Daily Completion
       ↓
Build Streaks
       ↓
Review Statistics
       ↓
View Monthly Calendar
       ↓
Archive or Continue Habit
```

---

## Database Structure

**Database:** `habit_tracker`

The application uses four primary tables:

```text
users
   │
   └──────< habits >────── categories
               │
               └──────< habit_completions
```

### Tables

| Table               | Purpose                                         |
| ------------------- | ----------------------------------------------- |
| `users`             | Stores user accounts, roles, and account status |
| `categories`        | Stores system-wide habit categories             |
| `habits`            | Stores user-created habits and their metadata   |
| `habit_completions` | Stores daily habit completion records           |

### Relationships

* `users` → `habits`: One user can have multiple habits
* `categories` → `habits`: Each habit belongs to a category
* `habits` → `habit_completions`: Each habit can have multiple completion records
* Foreign keys maintain referential integrity
* Cascading deletes are used where appropriate
* Category deletion is restricted when associated habits exist
* `UNIQUE(habit_id, completion_date)` prevents duplicate completion records for the same habit and date

### Design Decisions

**Dynamic statistics**

Streaks, completion counts, and completion rates are calculated from `habit_completions` rather than storing redundant statistics.

**Client-side theme preference**

Dark/light mode is stored in browser `localStorage`, so a separate settings table is not required.

**Habit archiving**

Habits can be archived by changing their status rather than immediately removing their historical data.

---

## Project Structure

```text
habit_tracker/
│
├── index.php
├── login.php
├── register.php
├── logout.php
├── README.md
│
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   ├── sidebar.php
│   ├── auth.php
│   └── functions.php
│
├── actions/
│   ├── login.php
│   ├── register.php
│   ├── add-habit.php
│   ├── update-habit.php
│   ├── delete-habit.php
│   ├── complete-habit.php
│   ├── undo-completion.php
│   ├── profile.php
│   ├── password.php
│   ├── user-actions.php
│   └── category-actions.php
│
├── user/
│   ├── dashboard.php
│   ├── habits.php
│   ├── add-habit.php
│   ├── edit-habit.php
│   ├── statistics.php
│   ├── calendar.php
│   └── profile.php
│
├── admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── habits.php
│   ├── categories.php
│   └── statistics.php
│
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   ├── auth.css
│   │   ├── dashboard.css
│   │   ├── habits.css
│   │   ├── admin.css
│   │   └── responsive.css
│   │
│   └── js/
│       ├── validation.js
│       ├── dashboard.js
│       ├── habits.js
│       ├── calendar.js
│       ├── admin.js
│       └── theme.js
│
└── database/
    └── habit_tracker.sql
```

---

## Requirements

* XAMPP with Apache, MySQL, and PHP 8+
* A modern web browser
* phpMyAdmin for database import

No additional dependency installation is required.

---

## Installation

### 1. Clone the Repository

Place the project inside the XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\habit_tracker
```

### 2. Start XAMPP

Open the XAMPP Control Panel and start:

```text
Apache
MySQL
```

### 3. Create the Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin/
```

Import the following SQL file:

```text
database/habit_tracker.sql
```

The SQL file contains the database schema and required seed data.

### 4. Configure the Database

Database configuration is located at:

```text
config/database.php
```

Example configuration:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'habit_tracker');
```

Update the credentials if your local MySQL configuration is different.

### 5. Run the Application

Open:

```text
http://localhost/habit_tracker/
```

---

## User Roles

| Role  | Access                                                                    |
| ----- | ------------------------------------------------------------------------- |
| User  | Manage personal habits, completions, statistics, calendar, and profile    |
| Admin | Manage users and categories and monitor system-wide habits and statistics |

The application performs role checks on the server before allowing access to protected functionality.

---

## Security

HabitFlow implements several standard web application security practices:

| Security Area            | Implementation                                          |
| ------------------------ | ------------------------------------------------------- |
| Password Security        | `password_hash()` and `password_verify()`               |
| SQL Injection Prevention | PDO prepared statements                                 |
| XSS Prevention           | `htmlspecialchars()` through the `e()` helper           |
| Session Security         | Session regeneration after login                        |
| Authorization            | Server-side role checks                                 |
| Resource Ownership       | Habit ownership verified before modifications           |
| Account Protection       | Admin self-deactivation/deletion safeguards             |
| Login Security           | Generic authentication error messages                   |
| Input Validation         | Client-side validation backed by server-side validation |

---

## Academic Scope

HabitFlow was developed as an individual college project to demonstrate practical web development and database concepts.

The project focuses on:

* CRUD operations
* Authentication and authorization
* Relational database design
* Session management
* Data validation
* Database constraints
* User-specific data access
* Administrative functionality
* Responsive frontend development

It intentionally does not attempt to provide production-scale features such as:

* Social login
* Mobile applications
* Real-time synchronization
* Third-party integrations
* AI-powered recommendations
* Cloud infrastructure
* Distributed architecture

---

## License

This project was developed for educational and academic purposes.
