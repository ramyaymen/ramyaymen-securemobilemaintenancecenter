# STRIDE Threat Model

## System Overview

The Secure Mobile Maintenance Center Management System is a PHP Native and MySQL web application for managing customer mobile-device repair requests. The application uses a lightweight MVC-style layout with front-controller routing in `index.php`, configuration files in `config/`, database access objects in `models/`, request handling in `controllers/`, access-control middleware in `middleware/`, and separate user and administrator views under `views/user/` and `views/admin/`.

### Authentication System

Authentication is implemented through `AuthController`. Registration accepts a full name, email address, Egyptian-style mobile phone number, and password. The controller validates required fields, validates email format with `FILTER_VALIDATE_EMAIL`, validates the phone number with a regular expression, checks for an existing email address through `User::findUserByEmail()`, hashes the password with `password_hash()`, encrypts the phone number with the project AES helper, and inserts the user through a prepared statement. Login retrieves a user by email and verifies the submitted password with `password_verify()`. On successful login, the application regenerates the session identifier and stores `user_id`, `full_name`, and `role` in the session before redirecting the user to either the regular dashboard or the admin dashboard.

### User Dashboard

Authenticated users are protected by `middleware/auth.php`, which checks that `$_SESSION['user_id']` exists. The user dashboard displays the logged-in user's name and role from the session, links to the profile page, allows users to submit devices, lists the user's own devices, and provides logout functionality. User-facing device views use `htmlspecialchars()` for most database-backed values displayed in HTML tables and forms.

### Admin Dashboard

Administrative pages are protected by `middleware/admin.php`, which first requires an authenticated session and then checks that `$_SESSION['role']` is exactly `admin`. The admin dashboard displays aggregate counts for total users, total devices, pending devices, in-progress devices, and completed devices. Admin users can list all registered users, list all submitted devices across all customers, change device repair status, and add repair notes to device records.

### Device Management

Device management is implemented through `DeviceController`, `Device`, and the user/admin device views. Regular users can create a device record with a device name, device model, and problem description. Regular users can list only their own devices through `Device::getDevicesByUserId()`. Edit and delete views load records through `Device::getDeviceByIdAndUserId()`, which enforces owner-based filtering with both `id` and `user_id`. Administrators can view all devices through `Device::getAllDevices()` and update device statuses through `Device::updateDeviceStatus()`.

### Repair Notes

Repair notes are admin-created records associated with a device. The admin add-note view validates that the target device exists by calling `Device::getDeviceById()` and then inserts the note through `Device::addRepairNote()`. Regular users see repair notes for devices returned from their own device list; the note lookup is performed per owned device using `DeviceController::getRepairNotes()` and `Device::getRepairNotes()`.

### Database

The application connects to MySQL using `mysqli` in `config/database.php`, sets the connection charset to `utf8mb4`, and uses the database name `secure_mobile_center`. The implementation expects at least three tables: `users`, `devices`, and `repair_notes`. The `users` table stores credentials, role, and encrypted phone data; the `devices` table stores repair requests; and `repair_notes` stores administrator notes for devices. Most variable-input SQL operations use prepared statements and `bind_param()`. Some fixed aggregate/listing queries are executed directly with `$connection->query()` because they do not concatenate user-controlled input.

### Session Management

Session startup is centralized in `config/session.php`, which starts a PHP session if one is not already active. Login calls `session_regenerate_id(true)` after successful password verification to reduce session fixation risk. Logout calls `session_unset()` and `session_destroy()` before redirecting to login. Session authorization state is held in `$_SESSION['user_id']`, `$_SESSION['full_name']`, and `$_SESSION['role']`. The implementation does not currently configure cookie attributes such as `Secure`, `HttpOnly`, or `SameSite` in code.

## Attack Surface Analysis

### Login

- **Route/view:** `login` routes to `views/auth/login.php`.
- **Controller:** `AuthController::login()`.
- **Inputs:** `email` and `password` from `POST`.
- **Security-relevant behavior:** Email is trimmed; user lookup uses a prepared statement; passwords are verified with `password_verify()`; the session ID is regenerated after successful authentication.
- **Exposure:** No rate limiting, lockout, CAPTCHA, MFA, or audit logging is implemented. Error messages are generic for invalid credentials, which helps reduce account enumeration during login.

### Registration

- **Route/view:** `register` routes to `views/auth/register.php`.
- **Controller:** `AuthController::register()`.
- **Inputs:** `full_name`, `email`, `phone`, and `password` from `POST`.
- **Security-relevant behavior:** Required-field checks, email validation, phone regex validation, duplicate email check, password hashing, phone encryption, and prepared insert.
- **Exposure:** No CSRF token, rate limiting, password-strength policy, email verification, or automated-abuse control is implemented. Duplicate email registration returns a distinct message, which can disclose whether an email is already registered.

### Session Handling

- **Files:** `config/session.php`, `middleware/auth.php`, `middleware/admin.php`, and `logout.php`.
- **Security-relevant behavior:** Sessions are started centrally; authentication middleware requires `user_id`; admin middleware requires the `admin` role; login regenerates the session ID; logout clears and destroys the session.
- **Exposure:** Cookie security flags are not explicitly set; there is no inactivity timeout, absolute session lifetime, session user-agent/IP binding, concurrent-session control, or forced session regeneration during privilege-sensitive operations beyond login.

### Device CRUD Operations

- **User create:** `views/user/add-device.php` and `DeviceController::addDevice()`.
- **User read:** `views/user/my-devices.php` and `Device::getDevicesByUserId()`.
- **User update:** `views/user/edit-device.php` and `Device::updateDevice()`.
- **User delete:** `views/user/delete-device.php` and `Device::deleteDevice()`.
- **Security-relevant behavior:** Add validates required fields and caps device name/model length. Read operations are filtered by session user ID. Edit and delete initially check ownership by loading the device with both device ID and user ID.
- **Exposure:** Edit submission trusts hidden `device_id` and calls `updateDevice()` without including `user_id` in the update condition, so a tampered hidden field could update another record after the initial page-load ownership check. Delete is performed by `GET`, which is CSRF-prone. Update/delete have limited validation for numeric IDs and field lengths compared with add-device.

### Admin Operations

- **Routes/views:** `admin`, `admin/users`, `admin/devices`, `admin/change-device-status`, `admin/add-repair-note`, and `admin/notes`.
- **Middleware:** `middleware/admin.php`.
- **Security-relevant behavior:** Admin pages require a valid session and the session role `admin`. Admin dashboard statistics use fixed queries or prepared status-count queries. User/device management output is mostly escaped with `htmlspecialchars()`.
- **Exposure:** Admin status changes and repair-note creation lack CSRF protection. Status values are accepted from `POST` and written to the database without server-side allow-list validation. Admin actions are not audited.

### Repair Notes

- **Admin write:** `views/admin/add-repair-note.php` and `Device::addRepairNote()`.
- **User read:** `views/user/my-devices.php`.
- **Security-relevant behavior:** Admin access is enforced before notes are added. Note insertion uses a prepared statement. Notes displayed to users are escaped with `htmlspecialchars()`.
- **Exposure:** No server-side note length limit, content policy, audit trail, or CSRF token is implemented. The note form trusts a hidden `device_id`; although the page checks that the device exists, the submitted hidden value can be changed to another existing device ID by an administrator's browser or by CSRF.

### Database Queries

- **Security-relevant behavior:** User lookups, user creation, device creation, owner-filtered reads, single-device reads, updates, deletes, status updates, repair-note insertion, note retrieval, and status counts use prepared statements. The database charset is set to `utf8mb4`.
- **Exposure:** Direct `query()` calls exist for fixed SQL statements that do not concatenate request parameters, such as all-user listing, all-device listing, and aggregate counts. The database credentials are hard-coded for a local root account with an empty password in `config/database.php`, which is unsafe for production deployment.

### HTTP Requests

- **Routing:** `index.php` routes based on the `url` query parameter and includes a fixed set of PHP views.
- **Methods:** Login, registration, add-device, edit-device, change-status, and add-note are `POST`; delete-device is a state-changing `GET` request.
- **Security headers:** `index.php` sets `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: no-referrer`, and `X-XSS-Protection: 1; mode=block`.
- **Exposure:** Security headers only run when requests flow through `index.php`; direct requests to view files may not receive those headers depending on Apache routing. No Content Security Policy, HSTS, or CSRF protection is present in the source code.

## STRIDE Analysis Table

| STRIDE Category | Threat Description | Affected Component | Potential Impact | Existing Mitigation | Residual Risk |
| --- | --- | --- | --- | --- | --- |
| Spoofing | Account impersonation through stolen or guessed credentials. | Login form, `AuthController::login()`, `users` table. | Unauthorized access to a customer's repair requests or administrator functions if an admin account is compromised. | Passwords are verified with `password_verify()` against `password_hash()` output; invalid login message is generic. | No MFA, rate limiting, lockout, breached-password screening, or password policy enforcement. |
| Spoofing | Session hijacking using a stolen PHP session cookie. | `config/session.php`, `middleware/auth.php`, `middleware/admin.php`. | Attacker can act as the victim until session expiration or logout. | `session_regenerate_id(true)` is called after successful login; protected pages require `user_id`. | Cookie attributes (`Secure`, `HttpOnly`, `SameSite`) and session timeout controls are not configured in code. |
| Spoofing | Role spoofing by manipulating server-side authorization assumptions. | `$_SESSION['role']`, `middleware/admin.php`. | Unauthorized user may attempt to reach admin pages or admin operations. | Admin middleware checks that the session role equals `admin`. | If session storage is compromised or fixation/hijacking occurs, role is trusted from session without additional revalidation from the database. |
| Tampering | Unauthorized device record modification by changing hidden form parameters. | `views/user/edit-device.php`, `Device::updateDevice()`. | A user may modify another user's device record if they tamper with `device_id` during POST after loading an authorized edit page. | Initial edit page loads the device using `getDeviceByIdAndUserId()`. SQL update uses a prepared statement. | Update query does not include `user_id` in the `WHERE` clause and does not re-check ownership on POST. |
| Tampering | Unauthorized or unintended device deletion through CSRF or crafted link. | `views/user/delete-device.php`, `Device::deleteDevice()`. | A user could be tricked into deleting one of their own repair requests. | Delete checks ownership with `getDeviceByIdAndUserId()` before deletion. | Delete uses a state-changing `GET` request and has no CSRF token. |
| Tampering | Unauthorized status value injection or workflow manipulation. | `views/admin/change-device-status.php`, `Device::updateDeviceStatus()`. | Device status may be changed to an unexpected value, disrupting workflow integrity and reporting. | Admin middleware restricts the page to administrators; SQL uses a prepared statement; UI offers Pending/In Progress/Completed options. | Server-side status allow-list validation is missing; hidden `device_id` can be altered in POST. |
| Tampering | Repair note manipulation by changing submitted device IDs or note content. | `views/admin/add-repair-note.php`, `Device::addRepairNote()`. | Notes may be attached to unintended devices or contain misleading maintenance history. | Admin middleware protects note creation; SQL uses a prepared statement; user display escapes note output. | No CSRF token, no submitted device revalidation tied to original page state, no length limit, and no audit trail. |
| Repudiation | Users can deny editing or deleting devices because actions are not logged. | User device edit/delete operations. | Difficulty proving who changed or removed repair information during investigations or academic defense. | Authentication identifies a session user before user operations. | No audit log table, timestamped action history, IP/user-agent capture, or immutable activity records. |
| Repudiation | Administrators can deny status changes or repair-note additions. | Admin change-status and add-note views. | Inability to attribute operational changes to a specific administrator. | Admin operations require an authenticated admin session. | No per-admin action logging, old/new status capture, or repair-note author field is implemented. |
| Repudiation | Login, failed login, registration, and logout events are not tracked. | Authentication flow and session lifecycle. | Security incidents such as credential stuffing or account takeover are harder to investigate. | Basic session creation and destruction are implemented. | No authentication event log or alerting exists. |
| Information Disclosure | Exposure of phone numbers if encrypted database values or encryption key are compromised together. | `config/encryption.php`, `users.phone`. | Customer phone numbers can be recovered. | Phone numbers are encrypted with AES-256-CBC and a random IV before storage. | Encryption key is hard-coded in source; encryption lacks authenticated encryption/HMAC; no key rotation. |
| Information Disclosure | Display of user emails and roles to administrators. | `views/admin/users.php`, `User::getAllUsers()`. | Admin compromise exposes account metadata for all users. | Admin middleware restricts user listing to `admin` role; output is escaped. | No field-level access controls, audit logging, or masking of email addresses. |
| Information Disclosure | Sensitive repair details are visible to users and admins. | Device listings and repair-note display. | Problem descriptions and notes may contain personal or device-sensitive details. | User device list is filtered by `user_id`; admin access is role-protected; output is escaped. | No data classification, retention policy, or encryption for device descriptions/repair notes. |
| Information Disclosure | Session data leakage through insecure transport or weak cookie settings. | PHP session cookie and session-backed pages. | An attacker who obtains the session cookie can access protected pages. | Session ID regeneration after login reduces fixation. | Source code does not enforce HTTPS, HSTS, `Secure`, `HttpOnly`, or `SameSite` cookie attributes. |
| Information Disclosure | Application error messages reveal implementation behavior. | `die()` messages in controllers/views. | Attackers may learn whether an email exists, whether a device ID exists, or whether access was denied. | Login failure is generic for invalid email/password. | Registration reveals duplicate email; direct `die()` messages expose validation and authorization outcomes. |
| Denial of Service | Excessive login or registration attempts. | `views/auth/login.php`, `views/auth/register.php`, `AuthController`. | Credential stuffing, database load, account enumeration pressure, or storage growth. | Required fields and basic validation reject malformed registration data. | No rate limiting, lockout, CAPTCHA, request throttling, or abuse monitoring. |
| Denial of Service | Large problem descriptions or repair notes consume storage and rendering resources. | Device creation/editing, repair-note creation, MySQL tables. | Database growth and slow pages, especially because notes are loaded per device. | Device name and model are limited to 100 characters during add-device. | No length limit for problem descriptions, edit fields, or repair notes; no pagination for device or note listings. |
| Denial of Service | Repeated admin/user listing and dashboard count queries. | Admin dashboard, users list, devices list, user device list. | Large datasets may produce slow responses and high database load. | Simple queries and indexed primary-key relationships are expected by schema design. | No pagination, caching, query limits, or operational monitoring in the code. |
| Elevation of Privilege | Regular user attempts to access admin URLs directly. | `views/admin/*`, `middleware/admin.php`. | Unauthorized access to global user/device data and administrative actions. | Admin middleware checks for authenticated session and `role === 'admin'`. | Session role is trusted without database revalidation; no centralized policy layer beyond included middleware. |
| Elevation of Privilege | User manipulates device IDs to access another user's records. | Edit/delete/read paths for devices. | Cross-user data access or modification. | User device listing uses `getDevicesByUserId()`; edit/delete initially use `getDeviceByIdAndUserId()`. | Edit POST updates by `device_id` only, leaving an authorization bypass risk for tampered hidden fields. |
| Elevation of Privilege | CSRF causes an authenticated admin to perform privileged operations unintentionally. | Admin status changes and repair-note creation. | Attacker may force an admin browser to update statuses or create notes. | Admin session is required. | No anti-CSRF tokens or SameSite cookie configuration are implemented. |

## Security Controls Implemented

### Password Hashing

The project uses PHP's `password_hash()` during registration and `password_verify()` during login. This is an appropriate modern password-storage pattern because it avoids storing plaintext passwords and allows PHP to use a secure default password hashing algorithm.

### AES Encryption

Phone numbers are encrypted before storage using `openssl_encrypt()` with `AES-256-CBC`. The encryption helper generates a fresh 16-byte initialization vector for each encryption operation and stores the IV together with the ciphertext in a base64-encoded value. This reduces direct exposure of phone numbers in the database compared with plaintext storage.

### RBAC

Role-based access control is implemented through the `role` value stored in the session. Regular authenticated pages include `middleware/auth.php`, while administrative pages include `middleware/admin.php`. The admin middleware requires both a logged-in user and a session role equal to `admin`, preventing normal users from directly loading admin views under normal session conditions.

### Session Security

The project centralizes `session_start()` in `config/session.php`. On successful login, `session_regenerate_id(true)` is called before session identity fields are assigned, which mitigates session fixation. Logout clears session variables and destroys the current session.

### Prepared Statements

Most database operations that include user-controlled or request-controlled values use `mysqli::prepare()` and `bind_param()`. This includes user lookup, user creation, device creation, owner-filtered device retrieval, single device retrieval, device updates, device deletion, status updates, repair-note insertion, repair-note retrieval, and status counts. This is a meaningful defense against SQL injection in the implemented data access layer.

### Authorization Checks

Authorization is enforced at multiple levels:

- `middleware/auth.php` requires authentication for user dashboards and user device pages.
- `middleware/admin.php` requires authentication and the `admin` role for administrative pages.
- `Device::getDevicesByUserId()` restricts regular user device listings to the current session user.
- `Device::getDeviceByIdAndUserId()` is used by edit and delete pages to verify that the target device belongs to the current user before displaying or deleting it.

### Input Validation

The registration flow checks required fields, validates email format, validates phone number format, and checks duplicate emails. Add-device checks required fields and limits device name/model to 100 characters. HTML forms also use `required` fields for basic client-side validation, although server-side validation remains the more important control.

### Output Sanitization

The application uses `htmlspecialchars()` for many values rendered from session data or the database, including dashboard names/roles, device names, device models, problem descriptions, statuses, repair notes, admin-visible customer names, admin-visible emails, and admin-visible roles. This helps reduce stored and reflected cross-site scripting risk in those views.

### HTTP Security Headers

The front controller sets `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: no-referrer`, and `X-XSS-Protection: 1; mode=block`. These headers provide baseline browser-side protections when traffic is routed through `index.php`.

## Security Gaps

The following gaps are based on the current implementation and are not generic assumptions:

1. **Missing CSRF protection.** Forms and state-changing routes do not include anti-CSRF tokens. This affects registration, login, add-device, edit-device, delete-device, admin status changes, and repair-note creation. The delete operation is especially exposed because it uses `GET`.
2. **State-changing delete uses `GET`.** `views/user/delete-device.php` deletes a device when visited with an `id` query parameter, making accidental or malicious link-triggered deletion possible.
3. **Edit-device authorization is incomplete on POST.** The edit page verifies ownership when loading the form, but the submitted hidden `device_id` is passed to `Device::updateDevice()` without adding `user_id` to the update condition or re-checking ownership.
4. **No rate limiting or account lockout.** Login and registration can be submitted repeatedly without throttling, lockout, CAPTCHA, or monitoring.
5. **No audit logging.** The system does not record login attempts, logout events, registration events, device edits/deletions, admin status changes, or repair-note creation.
6. **No MFA.** Administrator and user accounts rely only on a password.
7. **Hard-coded encryption key.** `ENCRYPTION_KEY` is stored directly in `config/encryption.php`, so source disclosure compromises encrypted phone numbers.
8. **AES-CBC lacks integrity protection.** The encryption helper uses AES-256-CBC but does not include an HMAC or authenticated encryption mode such as AES-GCM, so ciphertext tampering is not explicitly detected.
9. **Session cookie attributes are not configured.** The source does not set `Secure`, `HttpOnly`, or `SameSite` attributes, nor does it implement inactivity timeout or absolute session expiration.
10. **No server-side status allow-list.** The admin status form displays only three valid statuses, but `views/admin/change-device-status.php` accepts the posted status value directly.
11. **Limited length validation.** Add-device limits only device name/model. Edit-device, problem descriptions, repair notes, full names, emails beyond format, and passwords do not have comprehensive server-side length or complexity controls.
12. **Profile output is not escaped.** `views/user/profile.php` prints `$_SESSION['full_name']` and `$_SESSION['role']` directly, unlike the dashboard pages that use `htmlspecialchars()`.
13. **Production database configuration is unsafe.** The database connection uses local `root` with an empty password in source code. This may be acceptable for a local academic lab but is not appropriate for deployment.
14. **Security headers are incomplete and routing-dependent.** The app sets several headers in `index.php`, but it does not set Content Security Policy or HSTS, and direct view access may bypass the front-controller headers depending on web-server configuration.
15. **No pagination or resource limits.** User and admin listing pages load all matching records, and repair notes are queried per device in the user device list.
16. **Database schema file is empty.** The repository contains an empty SQL file, so database constraints such as unique email, foreign keys, cascade behavior, indexes, and role/status constraints cannot be verified from the committed schema.

## Recommended Improvements

### High Priority

1. **Add CSRF protection to every state-changing request.** Generate a per-session CSRF token, include it in all forms, validate it on POST, and convert delete-device from `GET` to `POST`.
2. **Fix edit-device authorization on POST.** Re-check ownership before updating and change the update query to include both `id` and `user_id` in the `WHERE` clause for regular users.
3. **Configure secure session cookies.** Set `session.cookie_httponly=1`, `session.cookie_secure=1` in HTTPS environments, and `session.cookie_samesite=Strict` or `Lax`; add inactivity and absolute session timeouts.
4. **Move encryption secrets out of source code.** Load encryption keys from environment variables or a protected server-side secret store, and document key rotation procedures.
5. **Implement authentication throttling.** Add rate limiting and failed-login tracking per account and per IP address, with lockout or progressive delay for repeated failures.
6. **Add audit logging for security-sensitive events.** Record login success/failure, logout, registration, device create/edit/delete, status changes, and repair-note creation with user ID, timestamp, IP address, and old/new values where appropriate.

### Medium Priority

1. **Use authenticated encryption.** Replace AES-CBC-only encryption with an authenticated encryption approach such as AES-256-GCM or encrypt-then-MAC using HMAC.
2. **Add server-side allow-lists.** Validate admin status updates against `Pending`, `In Progress`, and `Completed`; validate roles and other enumerated values server-side.
3. **Strengthen validation.** Add length limits for full names, emails, passwords, problem descriptions, edit fields, and repair notes; add a password-strength policy.
4. **Escape all output consistently.** Update `views/user/profile.php` and any future views to use `htmlspecialchars()` for all session and database values.
5. **Add security headers at the Apache level.** Apply headers consistently for all PHP routes, including CSP and HSTS for HTTPS deployments.
6. **Create and enforce a complete database schema.** Populate the SQL file with primary keys, unique constraints on email, foreign keys, indexes, default values, and constraints or application-enforced checks for roles/statuses.

### Low Priority

1. **Add pagination and query limits.** Paginate admin users, admin devices, user devices, and repair notes to reduce resource usage.
2. **Add administrator MFA.** Prioritize MFA for admin users, then optionally offer MFA for customers.
3. **Improve error handling.** Replace raw `die()` messages with controlled error pages and server-side logging.
4. **Add data retention rules.** Define how long repair notes, devices, and inactive user accounts are retained.
5. **Mask sensitive admin fields.** Consider partially masking email addresses or phone numbers in administrative views unless full values are required.
6. **Add automated security tests.** Include tests for authorization boundaries, CSRF validation, status allow-listing, and output escaping.

## Conclusion

The Secure Mobile Maintenance Center Management System demonstrates several important security controls for a PHP Native academic project. The most significant implemented strengths are password hashing, prepared SQL statements, role-based admin middleware, owner-filtered user device retrieval, session ID regeneration after login, AES-based phone-number encryption, and repeated use of output encoding in list and dashboard views.

The overall security posture is stronger than a purely functional CRUD application because the code intentionally addresses authentication, authorization, SQL injection resistance, sensitive-field encryption, and browser security headers. However, the current implementation still has material residual risks that should be addressed before production use or a high-assurance deployment. The highest-risk issues are missing CSRF protection, a state-changing delete operation over `GET`, incomplete ownership enforcement during edit-device POST handling, lack of login rate limiting, absence of audit logging, hard-coded encryption secrets, and incomplete session cookie hardening.

For academic review and project defense, the system can be described as a security-aware maintenance-management prototype with meaningful baseline protections and clearly identifiable improvement paths. Implementing the high-priority recommendations would substantially improve resistance to common web threats across the STRIDE categories, especially tampering, spoofing, repudiation, and elevation of privilege.
