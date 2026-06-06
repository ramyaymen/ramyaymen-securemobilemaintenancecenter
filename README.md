# Secure Mobile Maintenance Center Management System

## Project Overview

The **Secure Mobile Maintenance Center Management System** is a web-based application for managing mobile device repair requests in a maintenance center. It is developed using **PHP Native**, **MySQL**, **HTML**, **CSS**, and **JavaScript**, and is designed to demonstrate both practical software engineering principles and secure web application development practices.

From a business perspective, the system solves the common problem of manually tracking customer repair requests, device information, repair progress, and technician notes. Instead of relying on paper records or informal communication, customers can register, submit devices for maintenance, track repair status, and view repair notes, while administrators can manage users, review all devices, update repair states, and add technical notes.

From a cybersecurity perspective, the project demonstrates how a small PHP application can apply core security controls such as authentication, role-based authorization, password hashing, AES encryption for sensitive data, session management, input validation, output encoding, prepared statements, and HTTP security headers.


## Architecture Diagram Documentation

A complete source-code-aligned system architecture diagram for SSDLC review, academic defense, and cybersecurity presentation is available at [`docs/System_Architecture_Diagram.md`](docs/System_Architecture_Diagram.md).

## Project Objectives

The main objectives of this project are to:

- Provide a centralized system for managing mobile device repair requests.
- Allow customers to create accounts and securely access their own dashboard.
- Enable customers to add, view, edit, and delete their submitted devices.
- Allow administrators to view all users and all submitted devices.
- Support repair workflow tracking using device statuses such as `Pending`, `In Progress`, and `Completed`.
- Allow administrators to add repair notes for devices.
- Enforce role-based access control between regular users and administrators.
- Protect sensitive data such as passwords and phone numbers.
- Demonstrate secure coding practices suitable for academic evaluation.
- Provide a clear code organization based on configuration files, controllers, models, middleware, and views.

## Features

### User Registration

Users can create a new account by providing their full name, email address, phone number, and password. The registration process validates required fields, validates email format, validates phone number format, checks for duplicate email addresses, hashes the password, encrypts the phone number, and stores the user in the database.

### User Login

Registered users can authenticate using their email address and password. The system retrieves the user record by email and verifies the submitted password against the stored password hash. After successful login, a secure session is created and the user is redirected according to their role.

### Logout

Users can log out of the application. The logout process clears the current session data, destroys the session, and redirects the user back to the login page.

### User Dashboard

After authentication, regular users are redirected to their dashboard. The dashboard provides navigation to profile management, device submission, device listing, and logout functionality.

### Profile Management

Users can view their basic profile information, including their name and role. This helps users confirm the active account identity and access level.

### Add Device

Authenticated users can submit a mobile device for maintenance by entering the device name, device model, and problem description. The system validates that required device fields are provided and limits important text fields to reasonable lengths.

### View Devices

Users can view a list of devices they submitted. Each record displays device information, the current repair status, and any repair notes associated with the device.

### Edit Device

Users can edit their own submitted device information. Before editing, the system checks that the selected device belongs to the currently authenticated user.

### Delete Device

Users can delete their own submitted devices. The system verifies ownership before deletion so that one user cannot delete another user's repair request.

### Device Status Tracking

Each device includes a repair status that allows users and administrators to track the maintenance workflow. Typical statuses include:

- `Pending`
- `In Progress`
- `Completed`

### Repair Notes

Administrators can add repair notes to devices. Users can view repair notes related to their own devices from the device listing page. Repair notes provide a history of technical observations or maintenance updates.

### Admin Dashboard

Administrators have access to a dedicated dashboard that displays system-level statistics, including total users, total devices, pending devices, in-progress devices, and completed devices.

### Users Management

Administrators can view registered users, including user ID, full name, email address, and role. This feature supports administrative oversight of system accounts.

### Devices Management

Administrators can view all submitted devices across all users. This enables centralized repair management and allows staff to process all maintenance requests.

### Change Device Status

Administrators can update the repair status of any device. This supports the repair workflow from submission to completion.

### Role Based Access Control

The system separates regular users from administrators using a `role` value stored in the user session. Regular users can access user features, while administrators can access admin-only pages.

## System Architecture

The project follows a lightweight MVC-inspired PHP Native structure. The application is organized into folders that separate configuration, business logic, data access, request protection, and presentation.

```text
SecureMobileMaintenanceCenter/
├── config/
├── controllers/
├── database/
├── docs/
├── middleware/
├── models/
├── views/
├── index.php
├── logout.php
├── test-db.php
└── README.md
```

### `config`

The `config` directory contains application configuration and reusable security configuration files.

- `database.php` creates the MySQL connection and sets the database character set to `utf8mb4`.
- `encryption.php` defines AES encryption settings and helper functions for encrypting and decrypting sensitive data.
- `session.php` starts PHP sessions when no active session exists.

### `controllers`

The `controllers` directory contains request-handling logic and coordinates communication between forms, models, sessions, and redirects.

- `AuthController.php` handles registration and login.
- `DeviceController.php` handles device creation, listing, updating, deletion, status changes, and repair note retrieval.

### `models`

The `models` directory contains database access classes. These classes encapsulate SQL queries and use prepared statements for user-controlled input.

- `User.php` manages user creation, email lookup, user listing, and user counts.
- `Device.php` manages device records, ownership-based retrieval, admin device listing, status updates, repair notes, and device statistics.

### `views`

The `views` directory contains the HTML/PHP presentation layer. It is divided into authentication, user, admin, and error views.

- `views/auth/` contains registration and login screens.
- `views/user/` contains dashboard, profile, device submission, device listing, device editing, and device deletion pages.
- `views/admin/` contains administrative dashboard, users management, devices management, status changing, and repair note pages.
- `views/errors/` contains error pages such as the 404 page.

### `middleware`

The `middleware` directory contains access-control checks that protect restricted pages.

- `auth.php` ensures that only authenticated users can access user pages.
- `admin.php` ensures that only authenticated users with the `admin` role can access administrator pages.

### `docs`

The `docs` directory is reserved for project documentation artifacts such as diagrams, security notes, database design documents, threat modeling files, screenshots, or academic reports. If additional documentation is added later, it should be placed in this directory to keep the repository organized.

## Database Design

The application uses a MySQL database named:

```sql
secure_mobile_center
```

The core database design is based on three main tables: `users`, `devices`, and `repair_notes`.

### Entity Relationship Summary

```text
users (1) ──── (many) devices (1) ──── (many) repair_notes
```

- One user can submit many devices.
- Each device belongs to exactly one user.
- One device can have many repair notes.
- Each repair note belongs to exactly one device.

### `users` Table

The `users` table stores customer and administrator accounts.

| Column | Purpose |
| --- | --- |
| `id` | Primary key for each user. |
| `full_name` | User's full name. |
| `email` | User login identifier; should be unique. |
| `phone` | Encrypted phone number. |
| `password` | Hashed password generated using `password_hash()`. |
| `role` | Access role, typically `user` or `admin`. |
| `created_at` | Account creation timestamp, if defined in the schema. |

### `devices` Table

The `devices` table stores mobile devices submitted for maintenance.

| Column | Purpose |
| --- | --- |
| `id` | Primary key for each device. |
| `user_id` | Foreign key linking the device to the owner in the `users` table. |
| `device_name` | Name or type of device. |
| `device_model` | Model information for the submitted device. |
| `problem_description` | Customer-provided description of the device issue. |
| `status` | Repair workflow state such as `Pending`, `In Progress`, or `Completed`. |
| `created_at` | Device submission timestamp, if defined in the schema. |

### `repair_notes` Table

The `repair_notes` table stores technical notes added by administrators or repair staff.

| Column | Purpose |
| --- | --- |
| `id` | Primary key for each repair note. |
| `device_id` | Foreign key linking the note to a device in the `devices` table. |
| `note` | Repair note content. |
| `created_at` | Note creation timestamp, if defined in the schema. |

### Suggested SQL Schema

If the database schema file is not already populated, the following schema can be used as a reference for academic setup:

```sql
CREATE DATABASE IF NOT EXISTS secure_mobile_center
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE secure_mobile_center;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_name VARCHAR(100) NOT NULL,
    device_model VARCHAR(100) NOT NULL,
    problem_description TEXT NOT NULL,
    status ENUM('Pending', 'In Progress', 'Completed') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_devices_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE repair_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_repair_notes_device
        FOREIGN KEY (device_id)
        REFERENCES devices(id)
        ON DELETE CASCADE
);
```

## Security Features

This section explains the cybersecurity controls implemented in the project and how they support secure software development.

### Authentication

Authentication is implemented through the login flow. A user submits an email address and password. The application searches for the user by email and then verifies the submitted password using PHP's `password_verify()` function. If verification succeeds, user identity information is stored in the session, including:

- `user_id`
- `full_name`
- `role`

The authenticated session is then used to authorize access to protected pages.

### Authorization

Authorization is implemented through **Role Based Access Control (RBAC)**.

The project uses two primary roles:

| Role | Permissions |
| --- | --- |
| `user` | Access user dashboard, manage own profile, add own devices, view own devices, edit own devices, delete own devices, and view repair notes for own devices. |
| `admin` | Access admin dashboard, view users, view all devices, change device status, and add repair notes. |

RBAC is enforced through middleware:

- `middleware/auth.php` protects authenticated user pages.
- `middleware/admin.php` protects administrator-only pages and denies access if the session role is not `admin`.

### Password Security

Passwords are never stored in plaintext. During registration, the password is hashed using:

```php
password_hash($password, PASSWORD_DEFAULT)
```

During login, the submitted password is verified using:

```php
password_verify($password, $user['password'])
```

This approach allows PHP to use a secure password hashing algorithm and makes the stored password resistant to direct disclosure if the database is compromised.

### Data Encryption

The system encrypts phone numbers before storing them in the database. This protects sensitive user contact data at rest.

The encryption logic uses:

- `AES-256-CBC` as the encryption method.
- A random initialization vector for each encryption operation.
- Base64 encoding to store the encrypted value safely as text.

The phone number is encrypted during registration before insertion into the `users` table. The project also includes a decryption helper for retrieving the original value when needed by authorized functionality.

> Academic note: In production, encryption keys should be stored outside the source code, such as in environment variables or a secrets manager.

### Session Management

The application uses PHP sessions to maintain authenticated user state. Session handling is centralized in `config/session.php`, which starts a session only when no session is already active.

After successful login, the application calls:

```php
session_regenerate_id(true)
```

This helps mitigate session fixation attacks by replacing the previous session identifier with a new one after authentication.

During logout, the system clears and destroys the session before redirecting the user to the login page.

### Input Validation

Input validation reduces malformed data, accidental errors, and attack payloads.

#### Email Validation

Email addresses are validated using PHP's `FILTER_VALIDATE_EMAIL`. This ensures that the submitted email follows a valid email format before registration continues.

#### Password Validation

Passwords are required during registration and login. The current implementation verifies that a password field is not empty before processing.

Recommended future enhancement: enforce a stronger password policy with minimum length, uppercase and lowercase letters, numbers, symbols, and breached-password checks.

#### Phone Number Validation

Phone numbers are validated using a regular expression before encryption and storage. This prevents invalid phone formats from being stored in the database.

#### Device Data Validation

Device name, device model, and problem description are required. Device name and model also include length restrictions to prevent overly long input.

### Output Sanitization

The application uses `htmlspecialchars()` when displaying user-controlled or database-driven values in many views. This converts special HTML characters into safe entities and helps mitigate Cross-Site Scripting (XSS) attacks.

Examples of values that should be escaped before display include:

- Full names
- Device names
- Device models
- Problem descriptions
- Device statuses
- Repair notes
- User roles

### Access Control

Access control is applied at two levels:

1. **Page-level access control** using middleware.
2. **Record-level access control** using ownership checks.

For user device operations, the system checks the selected device against both the device ID and the current user's session ID. This prevents users from editing or deleting devices that belong to another account.

Administrators are allowed to view and manage all devices because their role grants system-wide maintenance privileges.

### Security Headers

The application sends HTTP security headers in the front controller to reduce browser-based attack risk.

| Header | Purpose |
| --- | --- |
| `X-Frame-Options: DENY` | Helps prevent clickjacking by blocking framing. |
| `X-Content-Type-Options: nosniff` | Prevents MIME type sniffing. |
| `Referrer-Policy: no-referrer` | Prevents referrer information from being sent to other sites. |
| `X-XSS-Protection: 1; mode=block` | Enables legacy browser XSS filtering where supported. |

> Academic note: Modern production systems should also consider `Content-Security-Policy`, `Strict-Transport-Security`, secure cookie flags, and HTTPS enforcement.

## Threat Modeling (STRIDE)

| STRIDE Category | Description | Possible Impact | Mitigation Implemented |
| --- | --- | --- | --- |
| Spoofing | An attacker attempts to impersonate a legitimate user or administrator. | Unauthorized access to accounts, device records, or administrative functions. | Email/password authentication, password hashing, `password_verify()`, PHP sessions, and `session_regenerate_id(true)` after login. |
| Tampering | An attacker attempts to modify device records, status values, repair notes, or request parameters. | Incorrect repair records, unauthorized device modification, or corrupted workflow state. | Prepared statements, authenticated routes, admin middleware, device ownership checks, and server-side validation. |
| Repudiation | A user or administrator denies performing an action such as deleting a device or changing a status. | Difficulty investigating disputes or unauthorized changes. | Session-based identity provides basic accountability; future improvement should add audit logs with user ID, action, timestamp, and IP address. |
| Information Disclosure | Sensitive data such as passwords, phone numbers, session data, or device problems are exposed. | Privacy loss, account compromise, or exposure of customer repair details. | Password hashing, AES encryption for phone numbers, output sanitization using `htmlspecialchars()`, role-based access checks, and owner-based device access. |
| Denial of Service | An attacker submits excessive requests, very large input, or repeated login attempts. | Application slowdown, database load, or reduced availability. | Required field validation and length restrictions on device name/model; future improvement should add rate limiting, request throttling, and account lockout policies. |
| Elevation of Privilege | A regular user attempts to access administrator pages or perform admin actions. | Unauthorized access to user management, all device records, status changes, or repair notes. | `middleware/admin.php` checks that the authenticated session role is `admin`; admin routes are separated from user routes. |

## Risk Assessment (DREAD)

Risk scoring uses a 1-10 scale for each DREAD factor:

- **1** = very low risk
- **10** = very high risk

The final risk score is the average of the five DREAD values.

| Threat | Damage | Reproducibility | Exploitability | Affected Users | Discoverability | Risk Score |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| SQL Injection attempt against login or device forms | 8 | 4 | 3 | 8 | 6 | 5.8 |
| Session fixation attack | 7 | 4 | 4 | 6 | 5 | 5.2 |
| Cross-Site Scripting through device or repair note fields | 7 | 5 | 5 | 7 | 6 | 6.0 |
| Unauthorized user editing another user's device | 8 | 4 | 4 | 6 | 5 | 5.4 |
| Unauthorized access to admin pages | 9 | 3 | 3 | 9 | 5 | 5.8 |
| Disclosure of phone numbers from database compromise | 7 | 3 | 4 | 8 | 4 | 5.2 |
| Brute-force login attack | 8 | 7 | 6 | 8 | 7 | 7.2 |
| CSRF request causing unwanted delete or status change | 7 | 6 | 6 | 6 | 6 | 6.2 |

### DREAD Interpretation

- The highest current risk is **brute-force login**, because rate limiting and lockout controls are not yet implemented.
- **CSRF** is also a meaningful future risk because state-changing requests should include CSRF tokens.
- SQL injection risk is reduced by prepared statements, but continued secure query construction is required.
- Sensitive phone number disclosure risk is reduced through AES encryption.

## Secure Coding Practices

### Prepared Statements

The project uses MySQLi prepared statements for database operations that include user-controlled input. Prepared statements separate SQL commands from user input, reducing the risk that malicious input will be interpreted as executable SQL.

### SQL Injection Prevention

SQL injection is mitigated by:

- Using `$connection->prepare()`.
- Binding parameters with `bind_param()`.
- Avoiding direct concatenation of user input into SQL queries.
- Using typed parameters such as `s` for strings and `i` for integers.

### Password Hashing

User passwords are hashed using PHP's `password_hash()` and verified using `password_verify()`. This is a core security requirement because plaintext passwords should never be stored.

### Encryption

Phone numbers are encrypted before database storage using AES encryption. This protects sensitive personal information at rest and demonstrates the confidentiality principle.

### Session Security

The application starts sessions through a central session configuration file and regenerates the session ID after successful login. This helps reduce the risk of session fixation.

Recommended production enhancements include:

- `session.cookie_httponly = true`
- `session.cookie_secure = true` when HTTPS is enabled
- `session.cookie_samesite = Strict` or `Lax`
- Session timeout and inactivity expiration

### Validation

The project validates required fields and important formats, including email addresses, phone numbers, and device fields. Validation helps improve data quality and reduces attack surface.

### Authorization Checks

Authorization checks are implemented using middleware and ownership validation. Admin pages require the `admin` role, while device edit/delete operations verify that the device belongs to the current user.

## Installation Guide

Follow these steps to run the project locally using XAMPP.

### 1. Clone Repository

Clone the repository into the XAMPP `htdocs` directory:

```bash
git clone <repository-url> SecureMobileMaintenanceCenter
```

Then move into the project directory:

```bash
cd SecureMobileMaintenanceCenter
```

### 2. Create Database

Open **phpMyAdmin** or the MySQL command line and create the database:

```sql
CREATE DATABASE secure_mobile_center CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import SQL Schema

Import the project schema file if available:

```bash
mysql -u root -p secure_mobile_center < database/secure_mobile_center.sql
```

If the schema file is empty or unavailable, use the suggested SQL schema provided in the **Database Design** section of this README.

### 4. Configure `database.php`

Update the database configuration file:

```php
$database_host = 'localhost';
$database_username = 'root';
$database_password = '';
$database_name = 'secure_mobile_center';
```

For a default XAMPP installation, the username is usually `root` and the password is usually empty.

### 5. Run Using XAMPP

Start the following XAMPP services:

- Apache
- MySQL

Place the project folder under:

```text
xampp/htdocs/SecureMobileMaintenanceCenter
```

### 6. Access the Application

Open the application in a browser:

```text
http://localhost/SecureMobileMaintenanceCenter/
```

Common routes include:

| Route | Purpose |
| --- | --- |
| `/register` | User registration page. |
| `/login` | User login page. |
| `/dashboard` | User dashboard. |
| `/my-devices` | User device list. |
| `/add-device` | Device submission page. |
| `/admin` | Administrator dashboard. |
| `/admin/users` | Users management page. |
| `/admin/devices` | Devices management page. |

## Future Improvements

The following improvements would strengthen the project for production use and advanced academic evaluation:

### CSRF Protection

Add CSRF tokens to all state-changing forms, including registration, login, add device, edit device, delete device, status changes, and repair note creation.

### Audit Logs

Create an audit logging system that records security-relevant events such as:

- Login attempts
- Logout events
- Device creation
- Device update
- Device deletion
- Admin status changes
- Repair note additions
- Failed authorization attempts

### Two-Factor Authentication

Add two-factor authentication for administrators and optionally for all users. This would reduce the risk of account compromise from stolen passwords.

### Email Notifications

Send email notifications when:

- A device is submitted.
- A device status changes.
- A repair note is added.
- A repair is completed.

### Security Monitoring

Add monitoring for suspicious behavior such as repeated failed logins, unusual admin activity, high request volume, and repeated access-denied events.

### Advanced Reporting

Add reporting features for:

- Number of devices repaired per month
- Average repair time
- Most common device issues
- Repair status distribution
- User activity statistics

## Technologies Used

| Technology | Purpose |
| --- | --- |
| PHP Native | Server-side application logic. |
| MySQL | Relational database for users, devices, and repair notes. |
| HTML | Page structure and forms. |
| CSS | Styling and layout. |
| JavaScript | Client-side interaction where needed. |
| Apache | Web server used by XAMPP. |
| XAMPP | Local development environment for Apache, PHP, and MySQL. |

## Conclusion

The **Secure Mobile Maintenance Center Management System** demonstrates how a PHP Native web application can be designed with both software engineering and cybersecurity principles in mind. From a software engineering perspective, the project separates responsibilities across configuration files, controllers, models, middleware, and views, making the codebase easier to understand, maintain, and evaluate.

From a cybersecurity perspective, the system implements several important security requirements: user authentication, role-based authorization, password hashing, AES encryption for phone numbers, session regeneration after login, prepared statements for SQL injection prevention, validation of user input, output sanitization, record-level access control, and browser security headers.

The project also includes a structured threat modeling and risk assessment discussion using STRIDE and DREAD. These sections help demonstrate how security threats were considered during development and how implemented controls reduce risk. Overall, the project is suitable for academic evaluation because it connects practical implementation details with secure software development principles, access control, encryption, authentication, authorization, and threat mitigation.
