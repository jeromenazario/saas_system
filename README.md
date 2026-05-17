# ⚡ SaaS Client & Subscription Manager

A PHP/MySQL web application for managing SaaS clients and their subscriptions, built with user authentication and activity tracking.

## Features

- **User Registration & Login** — secure authentication with hashed passwords
- **Session Protection** — all pages redirect to login if the user is not authenticated
- **Client Management** — add, edit, and delete clients
- **Subscription Management** — manage subscriptions per client (plans, pricing, status)
- **Activity Tracking** — every record shows who added it and who last updated it (`added_by`, `last_updated`)
- **Responsive UI** — styled with Bootstrap 5

## Tech Stack

- PHP
- MySQL
- Bootstrap 5
- XAMPP (local development)

## Database Structure

| Table | Description |
|-------|-------------|
| `users` | Stores registered user accounts |
| `clients` | Stores client records (one side of 1:M) |
| `subscriptions` | Stores subscriptions per client (many side of 1:M) |

## Setup Instructions

1. Clone or download this repository
2. Copy the `saas_system` folder into your XAMPP `htdocs` directory
3. Start **Apache** and **MySQL** in XAMPP
4. Open `http://localhost/phpmyadmin`
5. Run the `database.sql` file — paste it into the Console and press **Ctrl+Enter**
6. Open `http://localhost/saas_system/` in your browser

## Usage

1. Register a new account at `/register.php`
2. Log in at `/login.php`
3. Add clients from the homepage
4. Click the eye icon on a client to manage their subscriptions
5. All records display who created/updated them and when
6. Click **Logout** when done

## Project Structure

```
saas_system/
├── config/
│   └── db.php              # Database connection
├── clients/
│   ├── add.php             # Add a client
│   ├── edit.php            # Edit a client
│   ├── delete.php          # Delete a client
│   └── view.php            # View client's subscriptions
├── subscriptions/
│   ├── add.php             # Add a subscription
│   ├── edit.php            # Edit a subscription
│   └── delete.php          # Delete a subscription
├── index.php               # Homepage (protected)
├── login.php               # Login page
├── register.php            # Registration page
├── logout.php              # Logout handler
└── database.sql            # Database schema
```

## Author

Jerome Nazario
