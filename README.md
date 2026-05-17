# ⚡ SaaS Client & Subscription Manager

A PHP/MySQL web application for managing SaaS clients and their subscriptions, built with user authentication, activity tracking, and XSS security protection.

---

## Act 1 – Client & Subscription Management

### Features
- **User Registration & Login** — secure authentication with bcrypt-hashed passwords
- **Session Protection** — all pages redirect to login if the user is not authenticated
- **Client Management** — add, edit, and delete clients
- **Subscription Management** — manage subscriptions per client (plans, pricing, status)
- **Activity Tracking** — every record shows who added it and who last updated it (`added_by`, `updated_by`)
- **Who Did It Page** — dedicated audit log showing all user activity across the system
- **Responsive UI** — styled with Bootstrap 5

### Tech Stack
- PHP
- MySQL
- Bootstrap 5
- XAMPP (local development)

### Database Structure

| Table | Description |
|---|---|
| `users` | Stores registered user accounts |
| `clients` | Stores client records (one side of 1:M) |
| `subscriptions` | Stores subscriptions per client (many side of 1:M) |

### Setup Instructions
1. Clone or download this repository
2. Copy the `saas_system` folder into your XAMPP `htdocs` directory
3. Start Apache and MySQL in XAMPP
4. Open `http://localhost/phpmyadmin`
5. Run the `database.sql` file — paste it into the Console and press `Ctrl+Enter`
6. Open `http://localhost/saas_system/` in your browser

### Usage
- Register a new account at `/register.php`
- Log in at `/login.php`
- Add clients from the homepage
- Click the eye icon on a client to manage their subscriptions
- View the full activity log at `/who_did_it.php`
- Click Logout when done

---

## Act 2 – XSS Security & Password Hardening

### What Was Added
- **XSS Prevention** — all user input is sanitized with `sanitize_input()` and all output is encoded with `htmlspecialchars()` before being rendered in the browser
- **Content-Security-Policy Header** — blocks unauthorized inline scripts from executing
- **HttpOnly + SameSite Cookies** — session cookie cannot be accessed by JavaScript, protecting against cookie theft attacks
- **Session Fixation Protection** — `session_regenerate_id()` is called after every successful login
- **Stronger Password Rules** — passwords now require a minimum of 8 characters with at least one uppercase letter, one lowercase letter, and one number
- **XSS Demo Page** — `xss_vulnerable_demo.php` demonstrates a cookie theft attack and the fix side by side

### Security Techniques Used

| Technique | Where Applied |
|---|---|
| `sanitize_input()` with `htmlspecialchars()` | `login.php`, `register.php` |
| Output encoding on all echoed values | All PHP pages |
| `Content-Security-Policy` header | `login.php`, `register.php`, `who_did_it.php` |
| `HttpOnly` + `SameSite=Strict` cookies | `login.php`, `register.php` |
| `session_regenerate_id(true)` | `login.php` (after login) |
| `validate_password()` — 8+ chars, upper, lower, number | `register.php` |
| `password_hash()` / `password_verify()` | `register.php` / `login.php` |

---

## Project Structure

```
saas_system/
├── config/
│   └── db.php                  # Database connection
├── clients/
│   ├── add.php                 # Add a client
│   ├── edit.php                # Edit a client
│   ├── delete.php              # Delete a client
│   └── view.php                # View client's subscriptions
├── subscriptions/
│   ├── add.php                 # Add a subscription
│   ├── edit.php                # Edit a subscription
│   └── delete.php              # Delete a subscription
├── index.php                   # Homepage / dashboard (protected)
├── login.php                   # Login page (Act 2: XSS protection + secure cookies)
├── register.php                # Registration page (Act 2: password rules + XSS protection)
├── logout.php                  # Logout handler (Act 2: proper session destruction)
├── who_did_it.php              # Audit log — who added/updated what (Act 1 + Act 2)
├── xss_vulnerable_demo.php     # XSS attack demo page (Act 2)
└── database.sql                # Database schema
```

---

## Author
Jerome Nazario
