# Habit Tracker AI

A PHP-based web application for tracking daily habits, generating analytical reports, and providing AI-driven insights. The system includes user and admin modules, authentication, analytics, and structured habit management.

---

## Overview

Habit Tracker AI enables users to:

- Create and manage personal habits  
- Log daily progress  
- Generate performance reports  
- View analytical insights  
- Receive AI-based habit feedback  

An admin panel allows centralized management of users and categories.

---

## Features

### User Module
- User registration and login (session-based authentication)
- Add, edit, delete habits
- Daily habit logging
- Dashboard with summaries
- Report generation
- AI-generated insights
- Profile management
- Notifications
- Basic social interaction

### Admin Module
- Admin dashboard
- Manage users
- Manage habit categories

### AI Module
- AI-powered habit analysis
- Configurable AI settings (`config/ai_config.php`)

---

## Tech Stack

| Layer      | Technology |
|------------|------------|
| Backend    | PHP |
| Database   | MySQL |
| Frontend   | HTML, CSS, JavaScript |
| Styling    | Custom CSS |
| Server     | Apache (XAMPP/WAMP/MAMP compatible) |

---

## Project Structure

```

habit-tracker-ai/
│
├── admin/
│   ├── admin_dashboard.php
│   ├── manage_users.php
│   └── manage_categories.php
│
├── ai/
│   └── ai_insights.php
│
├── assets/
│   ├── css/style.css
│   └── js/main.js
│
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── config/
│   ├── db.php
│   └── ai_config.php
│
├── includes/
│   └── user_navbar.php
│
├── user/
│   ├── dashboard.php
│   ├── habits.php
│   ├── add_habit.php
│   ├── edit_habit.php
│   ├── log_habit.php
│   ├── reports.php
│   ├── generate_report.php
│   ├── insights.php
│   ├── notifications.php
│   ├── social.php
│   └── profile.php
│
├── index.php
├── about.php
├── contact.php
└── faq.php

```

---

## Installation

### 1. Clone Repository

```

git clone 'https://github.com/R-Dhawale/Habit-Tracker-AI.git'
cd habit-tracker-ai

```

### 2. Setup Local Environment

- Install XAMPP / WAMP / MAMP
- Move project folder into `htdocs`
- Start Apache and MySQL

### 3. Create Database

Create a database named:

```

habit_tracker

````

Import the SQL schema if available.

### 4. Configure Database

Edit `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'habit_tracker');
````

### 5. Run Application

Open in browser:

```
http://localhost/habit-tracker-ai/
```

---

## Authentication Flow

1. User registers → credentials stored in MySQL
2. Login validates credentials
3. PHP sessions manage access control
4. Protected routes verify active session
5. Logout destroys session

---

## AI Configuration

`config/ai_config.php` controls:

* API configuration
* Model parameters
* Insight generation logic

Ensure API keys are secured before production deployment.

---

## Deployment Considerations

* Enable HTTPS
* Move credentials to environment variables
* Restrict access to `/config`
* Use prepared statements
* Sanitize user inputs
* Disable `display_errors` in production
* Implement proper password hashing (`password_hash()`)

---

## Future Enhancements

* Role-Based Access Control (RBAC)
* REST API architecture
* JWT-based authentication
* Chart-based analytics
* Email notifications
* Docker support
* Automated testing

---

## License

For educational and portfolio use. Modify as required.
