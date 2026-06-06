# Security Testing Report

# Objective

The objective of this security testing report is to validate the security controls implemented in the **Secure Mobile Maintenance Center Management System** and assess the application's resistance against common web application attacks through Secure Software Development Life Cycle (SSDLC) security validation.

This report is **not a penetration testing report**. No external exploitation tools, scanners, or live attack infrastructure were used. The assessment is based on:

- Source code review
- Architecture review
- Threat modeling review
- Security control verification
- Review of implemented authentication, authorization, session, validation, data-access, and output-handling controls

The purpose is to determine whether the implemented controls reduce security risks commonly associated with PHP/MySQL web applications, including SQL injection, broken authentication, broken access control, cross-site scripting, session fixation, and sensitive data exposure.

# Scope

The review scope includes the following project areas and security-relevant workflows:

- **Authentication System**
  - User registration
  - User login
  - Password hashing and password verification
  - Duplicate email validation

- **Authorization System**
  - Authenticated-user route protection
  - Admin-only route protection
  - Role-based access control (RBAC)
  - User-owned device access checks

- **Session Management**
  - Session initialization
  - Session identity storage
  - Session ID regeneration after login
  - Logout and session destruction

- **Device Management**
  - Device creation
  - User device listing
  - Device editing
  - Device deletion
  - Device status tracking

- **Repair Notes**
  - Admin repair-note creation
  - User viewing of repair notes for owned devices
  - Output encoding of repair-note content

- **Database Access Layer**
  - User model database queries
  - Device model database queries
  - Use of prepared statements and parameter binding
  - Use of direct queries for non-parameterized aggregate/list operations

- **Admin Functions**
  - Admin dashboard
  - User management listing
  - Device management listing
  - Device status updates
  - Repair note creation

# Security Control Verification

| Security Control | Implementation Status | Evidence Found | Result |
|---|---|---|---|
| Password Hashing | Implemented | Registration uses `password_hash($password, PASSWORD_DEFAULT)`, and login uses `password_verify()` before creating a session. | Passed |
| AES Encryption | Implemented with limitations | Phone numbers are encrypted using `openssl_encrypt()` with `AES-256-CBC`, a random initialization vector, and Base64 encoding. The encryption key is hard-coded in source code and CBC mode does not provide built-in authentication. | Partially Passed |
| RBAC | Implemented | Admin middleware checks that an authenticated session exists and that `$_SESSION['role'] === 'admin'` before allowing access to admin pages. | Passed |
| Session Management | Implemented with limitations | Sessions are started through a central session configuration, login calls `session_regenerate_id(true)`, and logout clears and destroys the session. Cookie flags such as `HttpOnly`, `Secure`, and `SameSite` are not explicitly configured. | Partially Passed |
| Prepared Statements | Implemented for parameterized operations | User lookup, user creation, device creation, device owner lookup, update, delete, status update, repair-note insertion, and parameterized count queries use `prepare()` and `bind_param()`. Some non-parameterized read-only list/count queries use direct `query()` without user input. | Passed |
| Authorization Checks | Implemented with one important weakness | Auth middleware protects user pages, admin middleware protects admin pages, and edit/delete device pages verify ownership with `getDeviceByIdAndUserId()`. However, the edit form's POST update trusts a hidden `device_id` and calls `updateDevice()` by device ID only, so ownership is not revalidated on submission. | Partially Passed |
| Input Validation | Partially implemented | Registration validates required fields, email format, phone number format, and duplicate email. Device creation validates required fields and enforces length limits on device name/model. Some update/status/note inputs have limited server-side validation and no allowlist enforcement for status values. | Partially Passed |
| Output Sanitization | Mostly implemented with inconsistency | Most device, note, dashboard, and admin listing values are displayed through `htmlspecialchars()`. The user profile displays session values without `htmlspecialchars()`. | Partially Passed |
| Security Headers | Implemented with limitations | The front controller sends `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, and legacy `X-XSS-Protection` headers. A modern Content Security Policy is not present. | Partially Passed |

# Security Test Cases

The following test cases are SSDLC security validation test cases derived from source code and architecture review. The “Actual Result” describes the result expected from the implemented source code, not from external penetration testing.

### 1. SQL Injection Attempt

#### Test Name
SQL Injection Attempt Against Login and Device Inputs

#### Objective
Verify whether user-supplied fields used in database operations are protected against SQL injection.

#### Attack Scenario
An attacker submits payloads such as `' OR '1'='1`, `admin@example.com' --`, or malicious SQL fragments in login email, device name, device model, problem description, device ID, status, or repair note fields.

#### Expected Result
The application should treat malicious SQL input as data and should not alter SQL query logic, bypass authentication, disclose records, or modify unauthorized rows.

#### Actual Result
The reviewed user and device database methods consistently use `prepare()` and `bind_param()` for parameterized operations involving external values. This mitigates SQL injection in authentication, registration, device creation, user-owned device lookup, edit/delete operations, status updates, repair-note insertion, and repair-note retrieval. Direct `query()` calls exist for read-only operations that do not concatenate user input, such as listing users/devices and aggregate counts.

#### Status
Passed

### 2. Authentication Bypass Attempt

#### Test Name
Authentication Bypass Attempt with Invalid Credentials

#### Objective
Verify that users cannot authenticate without a valid email and password combination.

#### Attack Scenario
An attacker attempts to log in using an existing email with an incorrect password, a non-existent email, blank credentials, or SQL-like input intended to bypass password verification.

#### Expected Result
The system should reject invalid credentials and should not create a valid authenticated session.

#### Actual Result
Login requires both email and password, retrieves the user by email with a prepared statement, rejects missing users, and verifies the supplied password with `password_verify()` before assigning `user_id`, `full_name`, and `role` into the session. SQL-based bypass is mitigated by prepared statements, and password-based bypass is mitigated by hash verification.

#### Status
Passed

### 3. Unauthorized Device Access

#### Test Name
Unauthorized Access to Another User's Device

#### Objective
Verify that a regular user cannot view, edit, or delete another user's device record through the normal user routes.

#### Attack Scenario
A logged-in user changes the `id` parameter in the edit or delete URL to reference a device belonging to another user.

#### Expected Result
The application should deny access when the target device does not belong to the authenticated user.

#### Actual Result
The edit and delete pages retrieve devices using both `device_id` and `$_SESSION['user_id']` through `getDeviceByIdAndUserId()`. If the record is not found, the application terminates with `Access Denied`. The user's device list also retrieves devices by the current session user ID only.

#### Status
Passed for GET-based edit/delete access and user device listing; see IDOR test for POST-submission weakness.

### 4. Privilege Escalation Attempt

#### Test Name
Regular User Attempts to Access Admin Functions

#### Objective
Verify that regular users cannot access administrator-only functions such as user listing, all-device listing, status changes, or repair-note creation.

#### Attack Scenario
A user with role `user` directly requests `/admin`, `/admin/users`, `/admin/devices`, `/admin/change-device-status`, or `/admin/add-repair-note`.

#### Expected Result
Admin functionality should be restricted to authenticated users with the `admin` role.

#### Actual Result
Admin pages require `middleware/admin.php`, which first checks for an authenticated `user_id` and then requires `$_SESSION['role'] === 'admin'`. Non-admin users are denied with `Access Denied`.

#### Status
Passed

### 5. Session Fixation Attempt

#### Test Name
Session Fixation Attempt Before Login

#### Objective
Verify that the application does not continue using a pre-authentication session identifier after successful login.

#### Attack Scenario
An attacker attempts to force or predict a session ID before the victim logs in, hoping the same session ID remains valid after authentication.

#### Expected Result
The application should regenerate the session ID after successful authentication.

#### Actual Result
After password verification succeeds, the login flow calls `session_regenerate_id(true)` before storing user identity and role values in the session. Logout also clears and destroys the session. However, cookie hardening flags are not explicitly set in the session configuration.

#### Status
Partially Passed

### 6. Cross-Site Scripting (XSS) Attempt

#### Test Name
Stored XSS Attempt in Device or Repair-Note Fields

#### Objective
Verify whether user-controlled values are safely encoded before being rendered in HTML.

#### Attack Scenario
A user or administrator submits payloads such as `<script>alert(1)</script>` in a device name, device model, problem description, or repair note.

#### Expected Result
The application should display submitted payloads as harmless text instead of executing them as JavaScript.

#### Actual Result
Device lists, device edit form values, admin device listing, user dashboard session values, admin dashboard session values, user list fields, and repair notes generally use `htmlspecialchars()` before output. This mitigates stored XSS in the main device and repair-note rendering paths. One inconsistency was identified in the profile page, where `$_SESSION['full_name']` and `$_SESSION['role']` are output without `htmlspecialchars()`.

#### Status
Partially Passed

### 7. Information Disclosure Attempt

#### Test Name
Sensitive Data Exposure Review

#### Objective
Verify that sensitive data such as passwords, phone numbers, and internal errors are not directly exposed through normal application views.

#### Attack Scenario
An attacker attempts to retrieve sensitive fields by viewing profile, dashboard, admin users, device pages, or error outputs.

#### Expected Result
Passwords should never be displayed, sensitive personal data should be protected, and database connection details should not be disclosed in runtime errors.

#### Actual Result
Passwords are stored as hashes and are not rendered in the reviewed views. Phone numbers are encrypted before storage during registration, and the admin user listing does not display phone values. Database connection failure returns a generic `Database Connection Failed` message. However, encryption and database credentials are hard-coded in source configuration, which increases exposure risk if the repository or server files are compromised.

#### Status
Partially Passed

### 8. Broken Access Control Attempt

#### Test Name
Unauthenticated Access to Protected Pages

#### Objective
Verify that protected user and admin pages cannot be accessed without a valid session.

#### Attack Scenario
An unauthenticated visitor directly requests `/dashboard`, `/profile`, `/add-device`, `/my-devices`, `/edit-device`, `/delete-device`, `/admin`, `/admin/users`, or `/admin/devices`.

#### Expected Result
The application should redirect unauthenticated users to login or deny access.

#### Actual Result
User pages include authentication middleware that redirects unauthenticated users to the login page. Admin pages include admin middleware that also requires authentication before role validation. This provides baseline protection against unauthenticated access to protected functions.

#### Status
Passed

### 9. Direct Object Reference Attempt (IDOR)

#### Test Name
Direct Object Reference Through Device ID Manipulation

#### Objective
Verify whether device operations enforce ownership checks consistently across both initial page access and state-changing submissions.

#### Attack Scenario
A user accesses their own edit page, then modifies the hidden `device_id` in the POST body to target another user's device before submitting the edit form.

#### Expected Result
The application should revalidate that the submitted `device_id` belongs to the current authenticated user before performing the update.

#### Actual Result
The initial edit page load checks ownership using `getDeviceByIdAndUserId()`. However, the POST branch passes `$_POST['device_id']` directly to `updateDevice()`, and the model update query filters only by `id`, not by both `id` and `user_id`. Therefore, a tampered hidden field could update another user's device if the attacker has an authenticated session and a valid target device ID.

#### Status
Failed / Finding Identified

### 10. Password Storage Verification

#### Test Name
Password Storage Verification

#### Objective
Verify that plaintext passwords are not stored by the registration flow.

#### Attack Scenario
A registered user's password is reviewed at the source and database-storage design level to determine whether it is stored as plaintext, encrypted reversibly, or hashed.

#### Expected Result
Passwords should be hashed with a modern password hashing function before storage and verified using a password verification function.

#### Actual Result
The registration flow hashes passwords with `password_hash()` using `PASSWORD_DEFAULT`, and the login flow verifies submitted passwords with `password_verify()`. No reviewed application view displays password values.

#### Status
Passed

# Findings

### Passed Controls

- Passwords are hashed using PHP's `password_hash()` and verified using `password_verify()`.
- SQL injection risk is significantly reduced because user-controlled database operations use prepared statements and parameter binding.
- Admin access is protected by RBAC middleware that requires the `admin` role.
- Authenticated user pages are protected by authentication middleware.
- User device listing and initial edit/delete access are scoped to the authenticated user's `user_id`.
- Login regenerates the session identifier after successful authentication, reducing session fixation risk.
- Logout clears and destroys session state.
- Most user-controlled output in device, repair-note, dashboard, and admin listing views is encoded with `htmlspecialchars()`.
- Basic HTTP security headers are set in the front controller.
- Phone numbers are encrypted before database storage during registration.

### Partially Implemented Controls

- **AES Encryption:** AES-256-CBC with a random IV is implemented, but the encryption key is hard-coded and there is no authenticated encryption tag or separate integrity check.
- **Session Management:** Session start, regeneration, and destruction are implemented, but session cookie flags are not explicitly configured.
- **Authorization:** Admin middleware and device ownership checks exist, but the device edit POST workflow does not revalidate ownership of the submitted device ID.
- **Input Validation:** Registration and device creation contain meaningful validation, but update/status/note workflows need stronger allowlists, length checks, and type checks.
- **Output Sanitization:** Most outputs are encoded, but the profile page outputs session values without `htmlspecialchars()`.
- **Security Headers:** Several useful headers are configured, but there is no Content Security Policy and no explicit HSTS configuration.

### Missing Controls

The following controls were not found in the reviewed source code and should be considered for future hardening:

- CSRF protection tokens for state-changing forms and links
- Audit logging for login attempts, device changes, status changes, repair-note creation, and administrative actions
- Login rate limiting or throttling
- Account lockout or progressive delays after repeated failed login attempts
- Multi-factor authentication for administrator accounts
- Explicit session cookie hardening (`HttpOnly`, `Secure`, `SameSite`)
- Content Security Policy (CSP)
- Centralized error handling and security event monitoring
- Environment-based secret management for database credentials and encryption keys
- Strong database schema constraints, foreign keys, and indexes in the repository schema file

# Security Assessment

Overall security risk is assessed as: **Medium Risk**.

The project demonstrates several important secure development practices appropriate for a PHP Native and MySQL academic web application. The strongest implemented controls are password hashing, prepared statements, authentication checks, RBAC for admin routes, session ID regeneration after login, owner-based lookup for user device pages, output encoding in most views, basic security headers, and encryption of phone numbers before storage.

The overall rating is not Low Risk because several common production-grade controls are missing or incomplete. The most significant issue identified during source review is the IDOR/broken access-control weakness in the device edit POST workflow, where ownership is checked during page load but not revalidated during the update query. Additional medium-risk gaps include missing CSRF protection, missing rate limiting/account lockout, inconsistent output encoding on the profile page, hard-coded secrets, and lack of audit logging.

The overall rating is not High Risk because core authentication, password storage, SQL injection mitigation, admin RBAC, and major output encoding controls are present and substantially reduce several common web application threats.

# Recommendations

### High Priority

1. **Fix the device edit authorization weakness.**
   - Revalidate ownership during POST submission.
   - Update the database query to require both `id = ?` and `user_id = ?` for user device updates.
   - Do not trust hidden form fields as authorization evidence.

2. **Add CSRF protection to all state-changing actions.**
   - Generate per-session CSRF tokens.
   - Include tokens in registration, login, device add/edit/delete, admin status update, and repair-note forms.
   - Validate tokens server-side before performing actions.

3. **Harden session cookie configuration.**
   - Configure `HttpOnly`, `Secure`, and `SameSite` flags before `session_start()`.
   - Use HTTPS in deployment and consider session timeout controls.

4. **Move secrets out of source code.**
   - Store database credentials and encryption keys in environment variables or protected server configuration.
   - Add key rotation procedures for encrypted sensitive data.

### Medium Priority

1. **Implement login rate limiting and account lockout/progressive delay.**
   - Throttle repeated failed login attempts by account and IP address.
   - Log suspicious authentication activity.

2. **Improve input validation across all workflows.**
   - Validate device IDs as integers.
   - Enforce length limits on problem descriptions and repair notes.
   - Add an allowlist for status values: `Pending`, `In Progress`, and `Completed`.

3. **Add audit logging.**
   - Record login attempts, registration, device creation, device edits, device deletion, admin status changes, repair-note creation, and logout.
   - Include user ID, action, timestamp, and relevant record ID.

4. **Use authenticated encryption for sensitive data.**
   - Prefer AES-256-GCM or add an HMAC if CBC mode remains in use.
   - Separate encryption keys from application code.

5. **Add a Content Security Policy.**
   - Implement a restrictive CSP to reduce XSS impact.
   - Continue using contextual output encoding.

### Low Priority

1. **Standardize output encoding.**
   - Apply `htmlspecialchars()` consistently to profile session values and any future dynamic views.

2. **Improve database schema documentation and constraints.**
   - Include a complete schema with primary keys, foreign keys, indexes, unique constraints, role/status constraints, and default values.

3. **Improve error handling consistency.**
   - Replace direct `die()` statements with user-friendly messages and centralized logging.

4. **Consider administrator multi-factor authentication.**
   - MFA would strengthen privileged access, especially for administrative actions.

5. **Add automated security regression tests.**
   - Test authorization boundaries, CSRF validation, output encoding, session behavior, and prepared-statement usage.

# Conclusion

Based on source code review, architecture review, threat modeling review, and security control verification, the **Secure Mobile Maintenance Center Management System** implements several meaningful SSDLC security controls. The project demonstrates secure password handling, prepared database access for user-controlled values, role-based administrative authorization, authenticated user route protection, session ID regeneration after login, logout session destruction, phone-number encryption, output encoding in most key views, and baseline HTTP security headers.

These controls provide a solid academic demonstration of secure web application development practices and mitigate many common threats, especially SQL injection, plaintext password exposure, unauthenticated access, basic privilege escalation, and stored XSS in the main device and repair-note views.

However, the project still requires additional hardening before it would be suitable for a production environment. The most important improvement is to enforce authorization during the device edit POST operation, because the current workflow checks ownership when loading the edit page but does not bind the update query to the authenticated user's ID. The application should also add CSRF protection, rate limiting, account lockout or progressive delays, audit logging, hardened session cookies, secret management, and stronger encryption/key-management practices.

Overall, the reviewed implementation is assessed as **Medium Risk**. It shows clear evidence of security-aware design and implementation, while also presenting realistic areas for future improvement that align with secure software development and university-level security evaluation expectations.
