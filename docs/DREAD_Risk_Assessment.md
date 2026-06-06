# DREAD Risk Assessment

## Introduction

DREAD is a structured risk-rating methodology used to quantify security risk by scoring threats across five factors: **Damage Potential**, **Reproducibility**, **Exploitability**, **Affected Users**, and **Discoverability**. Each factor is scored on a 1-10 scale, then averaged to produce a total risk score. This document applies DREAD to the actual Secure Mobile Maintenance Center Management System source code rather than to a generic web application model.

Risk assessment is an important activity in the Secure Software Development Lifecycle (SSDLC) because it converts discovered threats into prioritized engineering decisions. A project may identify many vulnerabilities or weaknesses, but SSDLC planning requires teams to determine which risks should be fixed immediately, which should be scheduled for the next iteration, and which can be accepted temporarily with documented justification. DREAD supports this prioritization by measuring both the likely operational impact and the practical ease of attack.

DREAD complements STRIDE threat modeling. STRIDE helps identify the type of threat, such as spoofing, tampering, repudiation, information disclosure, denial of service, or elevation of privilege. DREAD then helps rank the identified threats by severity. In this project, STRIDE identifies security issues such as missing CSRF protection, authorization weaknesses, session risks, and information disclosure; DREAD assigns those issues measurable risk scores to support remediation planning and academic security evaluation.

## System Scope

This assessment covers the source code and functional components present in the repository for the Secure Mobile Maintenance Center Management System, a PHP Native and MySQL web application intended to run behind Apache.

### Authentication Module

The authentication module is implemented through `views/auth/login.php`, `views/auth/register.php`, `controllers/AuthController.php`, and `models/User.php`. Registration validates required fields, validates email format, validates phone number format, checks duplicate email addresses, hashes passwords with `password_hash()`, encrypts phone numbers, and inserts users through prepared statements. Login retrieves users by email and verifies passwords using `password_verify()`.

### Authorization Module

Authorization is implemented primarily through `middleware/auth.php` and `middleware/admin.php`. Authenticated user routes require `$_SESSION['user_id']`. Administrative routes require both an authenticated session and `$_SESSION['role'] === 'admin'`. User device access is additionally constrained in selected paths by owner-based queries such as `Device::getDevicesByUserId()` and `Device::getDeviceByIdAndUserId()`.

### Session Management

Session management is centralized in `config/session.php`, which calls `session_start()` when needed. Login calls `session_regenerate_id(true)` after successful password verification. Logout uses `session_unset()` and `session_destroy()`. Session state includes `user_id`, `full_name`, and `role`. The code does not explicitly configure secure session cookie attributes, inactivity timeout, absolute lifetime, or concurrent-session controls.

### User Device Management

User device management includes `views/user/add-device.php`, `views/user/my-devices.php`, `views/user/edit-device.php`, `views/user/delete-device.php`, `controllers/DeviceController.php`, and `models/Device.php`. Users can add, view, edit, and delete device repair requests. Device listing is filtered by the current session user. Edit and delete pages initially verify ownership through `getDeviceByIdAndUserId()`, but the edit POST operation ultimately updates by `device_id` only.

### Admin Dashboard

The admin dashboard is implemented in `views/admin/dashboard.php`, `views/admin/users.php`, `views/admin/devices.php`, and related admin views. It displays aggregate statistics, user listings, and all submitted devices. Admin access is controlled by `middleware/admin.php`. Admins can change device status and add repair notes.

### Repair Notes System

Repair notes are handled through `views/admin/add-repair-note.php`, `views/user/my-devices.php`, and `Device::addRepairNote()` / `Device::getRepairNotes()`. Administrators can create repair notes for devices, and users can view notes attached to their own devices. Repair-note insertion uses prepared statements, and note display in the user device list is escaped with `htmlspecialchars()`.

### Database Layer

The database layer uses `mysqli` in `config/database.php` and model classes in `models/User.php` and `models/Device.php`. The database is named `secure_mobile_center`; expected core tables are `users`, `devices`, and `repair_notes`. The implementation uses prepared statements for most request-driven queries, including authentication lookup, user creation, device creation, device lookup, device updates, deletion, status updates, and repair-note operations. The database schema file in the repository is empty, so database-level constraints cannot be verified from the committed SQL file.

### Encryption Layer

The encryption layer is implemented in `config/encryption.php`. Phone numbers are encrypted with `openssl_encrypt()` using `AES-256-CBC`, a random 16-byte initialization vector, and a hard-coded application key. The IV is prepended to the ciphertext and base64 encoded. Decryption reverses this process with `openssl_decrypt()`. The implementation provides confidentiality for phone numbers at rest, but it does not use authenticated encryption or external key management.

## DREAD Methodology

Each risk is scored from **1** to **10** for every DREAD factor:

- **1-3:** Low concern or difficult to exploit.
- **4-6:** Moderate concern or context-dependent exploitability.
- **7-8:** High concern, likely practical exploitation, or broad operational impact.
- **9-10:** Critical concern, severe impact, very easy repetition, or system-wide exposure.

The total score is calculated as:

```text
Total Score = (Damage + Reproducibility + Exploitability + Affected Users + Discoverability) / 5
```

Risk levels used in this assessment are:

| Total Score Range | Risk Level |
| --- | --- |
| 1.0-3.9 | Low |
| 4.0-6.4 | Medium |
| 6.5-7.9 | High |
| 8.0-10.0 | Critical |

### Damage Potential (D)

Damage Potential measures how severe the result would be if the threat were successfully exploited. High damage includes unauthorized administrative actions, cross-user device manipulation, account compromise, sensitive information exposure, or loss of repair-record integrity.

### Reproducibility (R)

Reproducibility measures how consistently an attacker can repeat the attack after learning the required steps. Attacks involving missing CSRF tokens, unauthenticated repeated submissions, or predictable request parameters generally score high.

### Exploitability (E)

Exploitability measures how difficult it is to execute the attack. Attacks requiring only a browser, crafted request, or automated form submission score higher than attacks requiring privileged network access or server compromise.

### Affected Users (A)

Affected Users measures the number or class of users impacted. A vulnerability affecting all customers or all administrators scores higher than one limited to a single user's own records.

### Discoverability (D)

Discoverability measures how easy it is to identify the vulnerability from normal application behavior, URL structure, forms, or source review. Exposed query parameters, hidden form fields, and visible error messages increase discoverability.

## Risk Assessment Table

| Threat | Category | Damage | Reproducibility | Exploitability | Affected Users | Discoverability | Total Score | Risk Level |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | --- |
| Missing CSRF protection on state-changing actions | Tampering / Elevation of Privilege | 8 | 9 | 8 | 7 | 9 | 8.2 | Critical |
| Brute force login attempts | Spoofing / Denial of Service | 8 | 9 | 8 | 6 | 9 | 8.0 | Critical |
| Unauthorized device modification through hidden `device_id` tampering | Tampering / IDOR | 8 | 8 | 7 | 6 | 8 | 7.4 | High |
| User enumeration through duplicate registration response | Information Disclosure | 5 | 9 | 8 | 7 | 8 | 7.4 | High |
| Repair note manipulation | Tampering / Repudiation | 7 | 8 | 7 | 6 | 8 | 7.2 | High |
| Resource exhaustion through unthrottled forms and unbounded text fields | Denial of Service | 6 | 8 | 7 | 7 | 8 | 7.2 | High |
| Session hijacking | Spoofing / Information Disclosure | 9 | 6 | 5 | 7 | 8 | 7.0 | High |
| Stored or reflected XSS through inconsistent output encoding | Information Disclosure / Tampering | 7 | 7 | 7 | 6 | 8 | 7.0 | High |
| Privilege escalation through compromised or trusted session role | Elevation of Privilege | 9 | 5 | 5 | 8 | 7 | 6.8 | High |
| Information disclosure from hard-coded encryption key and sensitive views | Information Disclosure | 7 | 6 | 5 | 7 | 8 | 6.6 | High |
| SQL injection attempts against database operations | Tampering / Information Disclosure | 8 | 3 | 3 | 8 | 6 | 5.6 | Medium |
| Session fixation | Spoofing | 7 | 3 | 3 | 6 | 6 | 5.0 | Medium |

## Risk Analysis

### 1. Missing CSRF Protection on State-Changing Actions

#### Threat Description

The application does not implement anti-CSRF tokens in forms or state-changing routes. Affected actions include registration, login, add-device, edit-device, delete-device, admin status changes, and repair-note creation. The delete-device action is particularly exposed because it performs deletion through a `GET` request with an `id` query parameter.

#### Impact

An attacker could cause an authenticated user to submit unwanted device changes or deletions. If the victim is an administrator, a forged request could change device status or add unauthorized repair notes. This directly affects repair workflow integrity.

#### Existing Security Controls

- User routes require authentication through `middleware/auth.php`.
- Admin routes require `middleware/admin.php` and the `admin` role.
- Database writes use prepared statements.
- The delete route checks device ownership before deleting.

#### Remaining Risk

Authentication alone does not prevent CSRF because the browser automatically sends valid session cookies with forged requests. The absence of CSRF tokens leaves authenticated workflows vulnerable to cross-site request abuse.

#### Recommended Mitigation

Implement synchronized CSRF tokens stored in the session. Include tokens in every state-changing form and validate them server-side before processing. Convert delete-device from `GET` to `POST`, and reject requests missing a valid token.

### 2. Brute Force Login Attempts

#### Threat Description

The login form accepts repeated email/password submissions without rate limiting, progressive delays, IP throttling, CAPTCHA, or account lockout. Password verification uses `password_verify()`, but no control restricts automated guessing attempts.

#### Impact

An attacker could perform credential stuffing or password guessing against user and administrator accounts. Successful compromise of an administrator account would expose all devices, users, statuses, and repair-note functionality.

#### Existing Security Controls

- Passwords are stored with `password_hash()`.
- Login verifies passwords with `password_verify()`.
- Invalid login responses are generic: `Invalid Email Or Password`.
- Successful login regenerates the session ID.

#### Remaining Risk

Password hashing protects stored credentials but does not stop online guessing. Lack of throttling makes repeated attempts highly reproducible and easy to automate.

#### Recommended Mitigation

Add rate limiting by IP address and account identifier, implement temporary account lockout or progressive delays after repeated failures, log failed attempts, and consider CAPTCHA after suspicious activity. Administrator accounts should use stronger password requirements and two-factor authentication.

### 3. Unauthorized Device Modification Through Hidden `device_id` Tampering

#### Threat Description

The edit-device view loads the initial device using `getDeviceByIdAndUserId()`, but after form submission it passes the hidden `device_id` to `Device::updateDevice()`. The update query uses only `WHERE id = ?` and does not include `user_id`, so a user may attempt to alter the hidden field before submitting.

#### Impact

A successful exploit could allow one authenticated user to modify another user's device name, model, or problem description. This is an insecure direct object reference (IDOR) and broken object-level authorization issue.

#### Existing Security Controls

- Edit page load checks ownership with both device ID and session user ID.
- User device listing is filtered by the current session user.
- SQL updates use prepared statements.
- Output is escaped when device details are displayed in most views.

#### Remaining Risk

Authorization is not consistently enforced at the final write operation. The prepared statement prevents SQL injection but does not prevent unauthorized object access.

#### Recommended Mitigation

Re-check ownership during POST processing and update records with both `id` and `user_id` in the `WHERE` clause. Ignore client-controlled hidden IDs where possible and derive authorization context from server-side session data.

### 4. User Enumeration Through Duplicate Registration Response

#### Threat Description

During registration, the system checks whether an email already exists and returns `Email Already Exists` when a duplicate is found. This reveals which email addresses are registered.

#### Impact

Attackers can build a list of valid accounts and use it for phishing, credential stuffing, or targeted brute force attempts. This risk is more significant because the login route has no rate limiting.

#### Existing Security Controls

- Login failure messages are generic and do not disclose whether the email or password was wrong.
- Registration validates email format before database lookup.
- User lookup uses prepared statements.

#### Remaining Risk

The registration flow still exposes account existence. The attack is highly reproducible because only repeated registration requests are needed.

#### Recommended Mitigation

Use neutral registration responses such as `If the account can be created, further instructions will be shown` or redirect to a generic confirmation page. Add rate limiting to registration and monitor repeated duplicate checks.

### 5. Repair Note Manipulation

#### Threat Description

Administrators can add repair notes through a form containing a hidden `device_id`. The page checks that a device exists before rendering the form, but POST processing accepts submitted `device_id` and note content directly. There is no CSRF token, no audit logging, and no repair-note author field.

#### Impact

Repair history may be attached to the wrong device, falsified, or created without clear accountability. This affects integrity and non-repudiation of maintenance records.

#### Existing Security Controls

- The add-note page is protected by admin middleware.
- The target device is checked before the form is displayed.
- Repair-note insertion uses a prepared statement.
- Repair notes are escaped with `htmlspecialchars()` when shown to users.

#### Remaining Risk

Prepared statements protect SQL syntax, but they do not validate business intent. Missing CSRF and audit logging means unauthorized or disputed note creation remains a practical risk.

#### Recommended Mitigation

Validate CSRF tokens, re-check the submitted device ID server-side, store the administrator ID with each repair note, add length limits, and log create/update events for notes.

### 6. Resource Exhaustion Through Unthrottled Forms and Unbounded Text Fields

#### Threat Description

The application accepts repeated submissions to login, registration, add-device, edit-device, and repair-note forms without request throttling. Device problem descriptions and repair notes do not have strong server-side length limits. Listing pages do not implement pagination.

#### Impact

Attackers may generate excessive database records, long text fields, repeated password checks, or heavy listing pages. This may degrade availability for users and administrators.

#### Existing Security Controls

- Add-device requires non-empty fields.
- Add-device limits device name and device model to 100 characters.
- Invalid registration data is rejected through email and phone validation.
- Database operations use prepared statements.

#### Remaining Risk

The system lacks rate limiting, payload-size rules, pagination, and monitoring. As data grows, pages that load all users, all devices, or per-device repair notes may become slower.

#### Recommended Mitigation

Add server-side length limits for all text fields, enforce request-size limits at Apache/PHP, paginate lists, add rate limiting, and monitor excessive submissions.

### 7. Session Hijacking

#### Threat Description

The application relies on PHP session cookies to identify authenticated users and administrators. The source code starts sessions and stores user identity and role in `$_SESSION`, but it does not explicitly configure `Secure`, `HttpOnly`, or `SameSite` cookie attributes.

#### Impact

If an attacker obtains a valid session cookie, they may impersonate the victim. An administrator session would provide access to user listings, all devices, status changes, and repair-note creation.

#### Existing Security Controls

- Login regenerates the session identifier with `session_regenerate_id(true)`.
- Protected pages require `$_SESSION['user_id']`.
- Admin pages require `$_SESSION['role'] === 'admin'`.
- Logout destroys the current session.

#### Remaining Risk

Session cookies are not hardened in code, and there is no inactivity timeout or absolute session expiration. Session hijacking remains a high-impact risk if transport security or browser controls are weak.

#### Recommended Mitigation

Set secure cookie attributes using `session_set_cookie_params()` or PHP configuration. Enforce HTTPS, enable HSTS, implement idle and absolute timeouts, and regenerate session IDs after privilege-sensitive events.

### 8. Stored or Reflected XSS Through Inconsistent Output Encoding

#### Threat Description

Most device, note, and admin listing output uses `htmlspecialchars()`, but the profile page prints `$_SESSION['full_name']` and `$_SESSION['role']` directly. Since the full name originates from registration input and is stored in session after login, inconsistent encoding creates a potential XSS path.

#### Impact

A successful script injection could steal session-related data accessible to JavaScript, alter page content, perform actions as the user, or target administrators if unsafe output is introduced into admin views later.

#### Existing Security Controls

- Many views use `htmlspecialchars()` for database-backed fields.
- The front controller sets basic browser security headers such as `X-Frame-Options` and `X-Content-Type-Options`.
- User input is stored through prepared statements.

#### Remaining Risk

Prepared statements do not prevent XSS. Any unescaped output of user-controlled data remains risky, especially when session cookie `HttpOnly` is not explicitly configured.

#### Recommended Mitigation

Apply `htmlspecialchars()` consistently to all session and database output. Add a Content Security Policy, set `HttpOnly` cookies, and centralize output helper functions to reduce developer mistakes.

### 9. Privilege Escalation Through Compromised or Trusted Session Role

#### Threat Description

Admin authorization depends on the `role` value stored in `$_SESSION`. Under normal conditions this is server-side state and is checked by admin middleware. However, if a session is hijacked or otherwise compromised, the application trusts the stored role without revalidating the account status or role from the database.

#### Impact

A compromised administrator session gives full access to admin dashboard functions, including user listing, all-device listing, status changes, and repair-note creation. A stale session may also remain privileged if roles change in the database after login.

#### Existing Security Controls

- Admin pages include `middleware/admin.php`.
- Admin middleware checks authentication and the `admin` role.
- Login sets role from the database after successful password verification.

#### Remaining Risk

The authorization decision is not revalidated against current database state during requests. There is no privileged-session timeout, step-up authentication, or MFA.

#### Recommended Mitigation

Revalidate role for sensitive admin actions, expire sessions when roles change, add administrator MFA, and implement privileged-session timeout controls.

### 10. Information Disclosure From Hard-Coded Encryption Key and Sensitive Views

#### Threat Description

The encryption key for phone-number protection is hard-coded in `config/encryption.php`. Admin user listings display account metadata such as names, emails, and roles. The database credentials are also stored in source code with a local root user and empty password.

#### Impact

If source code and database contents are exposed together, encrypted phone numbers can be decrypted. Admin account compromise exposes user metadata and repair information.

#### Existing Security Controls

- Phone numbers are encrypted with AES-256-CBC before storage.
- Admin pages require `admin` role authorization.
- Admin user listing output is escaped.
- Database charset is set to `utf8mb4`.

#### Remaining Risk

Hard-coded secrets reduce the value of encryption at rest if the repository is leaked. AES-CBC is used without an authentication tag or HMAC. Sensitive views do not include additional masking or audit controls.

#### Recommended Mitigation

Move secrets to environment variables or a protected secret manager, implement key rotation, use authenticated encryption such as AES-GCM, and minimize or mask sensitive fields in administrative views.

### 11. SQL Injection Attempts Against Database Operations

#### Threat Description

The application accepts many request-controlled values, including email, device IDs, device attributes, status, and repair notes. These inputs would normally be SQL injection targets.

#### Impact

If SQL injection were successful, an attacker could read, modify, or delete users, devices, repair notes, password hashes, or encrypted phone numbers.

#### Existing Security Controls

- User lookup and creation use prepared statements.
- Device create/read/update/delete operations use prepared statements.
- Status updates and repair-note operations use prepared statements.
- The database connection uses `utf8mb4`.
- Direct `query()` calls are used for fixed queries that do not concatenate request parameters.

#### Remaining Risk

The current residual SQL injection risk is medium because prepared statements are widely used. However, future code changes could introduce unsafe concatenation if secure data-access patterns are not enforced.

#### Recommended Mitigation

Maintain a strict prepared-statement-only rule for variable input, add code review checks, populate database constraints, and add security tests for injection payloads on high-risk forms.

### 12. Session Fixation

#### Threat Description

Session fixation occurs when an attacker causes a victim to authenticate with a session ID known to the attacker. This project starts sessions before processing login.

#### Impact

If fixation succeeded, an attacker could reuse the known session ID after the victim authenticates. This would lead to account impersonation.

#### Existing Security Controls

- `AuthController::login()` calls `session_regenerate_id(true)` after successful password verification.
- Logout destroys the session.
- Protected middleware checks for authenticated session state.

#### Remaining Risk

The main fixation risk is reduced by session regeneration. Residual risk remains if cookie attributes are weak, transport security is not enforced, or future authentication changes remove regeneration.

#### Recommended Mitigation

Keep session regeneration in the login flow, regenerate session IDs on privilege changes, harden cookies, enforce HTTPS, and add tests or code review rules to prevent regression.

## Risk Matrix Summary

### Critical Risks

- Missing CSRF protection on state-changing actions — **8.2**.
- Brute force login attempts — **8.0**.

### High Risks

- Unauthorized device modification through hidden `device_id` tampering — **7.4**.
- User enumeration through duplicate registration response — **7.4**.
- Repair note manipulation — **7.2**.
- Resource exhaustion through unthrottled forms and unbounded text fields — **7.2**.
- Session hijacking — **7.0**.
- Stored or reflected XSS through inconsistent output encoding — **7.0**.
- Privilege escalation through compromised or trusted session role — **6.8**.
- Information disclosure from hard-coded encryption key and sensitive views — **6.6**.

### Medium Risks

- SQL injection attempts against database operations — **5.6**.
- Session fixation — **5.0**.

### Low Risks

No low risks were assigned in this assessment. The evaluated issues are directly connected to authentication, authorization, session handling, data integrity, or sensitive data exposure; therefore, they require at least medium-level tracking.

## Security Recommendations

### Immediate Actions

1. **Implement CSRF tokens.** Add token generation, form inclusion, and server-side validation for all state-changing requests.
2. **Convert delete-device to POST.** Do not perform destructive actions through `GET` requests.
3. **Fix device edit authorization.** Re-check ownership on POST and update with both device ID and current user ID.
4. **Add login rate limiting.** Throttle repeated login attempts by IP address and account identifier.
5. **Escape profile output.** Use `htmlspecialchars()` for `$_SESSION['full_name']` and `$_SESSION['role']` in `views/user/profile.php`.
6. **Validate admin status values server-side.** Allow only `Pending`, `In Progress`, and `Completed`.

### Short-Term Improvements

1. **Add audit logging.** Track login attempts, registration events, device creation/edit/deletion, admin status changes, repair-note creation, and logout events.
2. **Implement account lockout or progressive delays.** Slow repeated failed authentication attempts and alert administrators to suspicious patterns.
3. **Harden session cookies.** Configure `Secure`, `HttpOnly`, and `SameSite` cookie attributes and add inactivity timeout.
4. **Move secrets out of source code.** Store database credentials and encryption keys in environment variables or protected configuration.
5. **Add server-side length validation.** Enforce size limits for names, emails, passwords, problem descriptions, repair notes, and edited device fields.
6. **Improve security headers.** Add Content Security Policy and HSTS at the Apache or application level, ensuring headers apply to all routes.

### Long-Term Improvements

1. **Add two-factor authentication.** Prioritize administrator accounts, then optionally support users.
2. **Implement security monitoring.** Centralize logs, alert on brute force attempts, suspicious admin activity, and high-volume form submissions.
3. **Upgrade encryption design.** Use authenticated encryption such as AES-256-GCM and define key rotation procedures.
4. **Create a complete database schema.** Add unique constraints, foreign keys, indexes, default values, and role/status constraints where supported.
5. **Add automated security tests.** Test CSRF validation, authorization boundaries, IDOR prevention, rate limiting, and output encoding.
6. **Introduce pagination and operational limits.** Paginate users, devices, and repair notes to improve availability as data grows.

## Conclusion

The Secure Mobile Maintenance Center Management System demonstrates several security-aware design choices, including password hashing with `password_hash()`, credential verification with `password_verify()`, AES-based phone-number encryption, role-based access control, prepared SQL statements, ownership checks for user device access, session regeneration after login, and consistent output sanitization in many views. These controls reduce important baseline risks such as plaintext password exposure and SQL injection.

However, the DREAD assessment shows that several implementation-specific risks remain significant. The most urgent risks are missing CSRF protection, unthrottled login attempts, incomplete authorization enforcement during device edit submission, and weak session-cookie hardening. Additional concerns include user enumeration, repair-note accountability, hard-coded encryption secrets, inconsistent output encoding, and lack of audit logging.

For university project evaluation, the system can be presented as a functional and security-conscious PHP Native maintenance-management application with clearly documented residual risks. Addressing the immediate and short-term recommendations would substantially improve the project's SSDLC maturity, strengthen the security posture, and provide stronger evidence of secure engineering practice during academic defense and project presentation.
