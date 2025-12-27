# 🔐 ZanaHustle Security Documentation

## Overview

ZanaHustle implements enterprise-grade security measures to protect user data and prevent common web vulnerabilities.

---

## Security Features Implemented

### 1. Authentication & Authorization

#### Password Security
- **Bcrypt Hashing**: Passwords are hashed using bcrypt with cost parameter 12
- **Password Validation**: Minimum 8 characters, requires uppercase, lowercase, and numbers
- **No Plain Text Storage**: Passwords are never stored in plain text

#### Session Management
- **Session Regeneration**: Session ID regenerated after login to prevent session fixation
- **Session Timeout**: Automatic logout after 30 minutes of inactivity
- **HTTPOnly Cookies**: JavaScript cannot access session cookies
- **Secure Cookies**: HTTPS-only cookies in production (configurable via SESSION_SECURE)
- **User Agent Validation**: Sessions validated against User-Agent to detect hijacking

#### Rate Limiting
- **Login Protection**: Maximum 5 login attempts per 10 minutes
- **Registration Protection**: Maximum 3 registrations per hour per IP address
- **Per-IP Tracking**: Prevents brute force attacks from multiple attempts

#### CSRF Protection
- **Token Generation**: Unique CSRF tokens generated per session
- **Form Protection**: All state-changing requests require valid CSRF tokens
- **Token Validation**: Server-side verification of tokens before processing requests
- **Automatic Regeneration**: Tokens available in session immediately after generation

### 2. Data Protection

#### SQL Injection Prevention
- **Prepared Statements**: All database queries use parameterized prepared statements
- **Parameter Binding**: User input bound to query placeholders, never concatenated
- **Query Type Validation**: Proper handling of different SQL data types
- **Strict SQL Mode**: Database enforces `STRICT_TRANS_TABLES` mode

#### XSS Prevention
- **Output Escaping**: All user input displayed using `htmlspecialchars()`
- **Context-Aware Encoding**: Different encoding for HTML, JavaScript, URLs
- **Content-Security-Policy**: CSP header restricts script execution
- **HTTPOnly Cookies**: Session data not accessible to JavaScript

#### Input Validation
- **Email Validation**: RFC-compliant email validation using filter_var()
- **Username Validation**: Alphanumeric and underscore only, 3-50 characters
- **Full Name Validation**: Letters, spaces, hyphens, and apostrophes only
- **Phone Validation**: International and standard format support
- **Amount Validation**: Numeric validation with min/max bounds
- **Service Title/Description**: Length validation (5-200 and 20-5000 characters)

#### Input Sanitization
- **String Sanitization**: Removes slashes, trims whitespace, encodes special characters
- **Email Sanitization**: Removes invalid email characters
- **URL Sanitization**: Validates and sanitizes URLs
- **Number Sanitization**: Extracts numeric values safely

### 3. HTTP Security Headers

#### Clickjacking Protection
- **X-Frame-Options: SAMEORIGIN**: Prevents embedding in frames from other origins

#### MIME Type Sniffing Prevention
- **X-Content-Type-Options: nosniff**: Enforces declared MIME types

#### HTTPS Enforcement
- **Strict-Transport-Security**: Forces HTTPS with 1-year max-age

#### Browser XSS Filter
- **X-XSS-Protection**: Enables browser XSS protection

#### Privacy Protection
- **Referrer-Policy**: Limits referrer information sent to external sites
- **Permissions-Policy**: Disables geolocation, microphone, camera access
- **Cross-Origin-Embedder-Policy**: Prevents cross-origin resource embedding

#### Content Security Policy
```
default-src 'self'
script-src 'self' 'unsafe-inline'
style-src 'self' 'unsafe-inline'
img-src 'self' data: https:
font-src 'self'
connect-src 'self'
```

### 4. File Upload Security

#### MIME Type Validation
- **Content Verification**: Uses `finfo_file()` to verify actual MIME type
- **Whitelist-Based**: Only approved MIME types accepted (JPEG, PNG, GIF, WebP)

#### File Size Limits
- **Maximum Size**: 5 MB per file
- **Minimum Size**: Must be greater than 0 bytes
- **Validation**: Checked before file processing

#### Secure File Naming
- **Hash-Based Naming**: Files renamed to prevent directory traversal
- **Timestamp Addition**: Unique filenames using hash + timestamp
- **Extension Validation**: Whitelist of allowed file extensions

#### Directory Separation
- **Upload Directory**: Separate from web root (outside public access when possible)
- **Proper Permissions**: 755 for directories, 644 for files
- **Web Access Prevention**: `.htaccess` prevents direct execution of uploaded files

### 5. API Security

#### Authentication
- **User Verification**: All endpoints check for authenticated user
- **Session Validation**: Valid session required for all API calls

#### Authorization
- **Ownership Verification**: Users can only access their own data
- **Role-Based Access**: Clients cannot access freelancer-only functions
- **Resource Owner Check**: API endpoints verify user owns the resource being modified

#### Request Validation
- **CSRF Token Check**: State-changing requests require valid CSRF tokens
- **Content-Type Validation**: Proper content type checking for JSON/form data
- **Input Sanitization**: All inputs sanitized before database operations

#### Response Security
- **Error Handling**: Generic error messages to users, detailed logging internally
- **No Information Disclosure**: Sensitive information never in error messages
- **Type Safety**: JSON responses with proper content-type headers

### 6. Error Handling & Logging

#### Error Handling
- **Production Mode**: Users see generic error messages, no technical details
- **Debug Mode**: Detailed error messages for development (set via DEBUG_MODE)
- **Exception Catching**: All exceptions caught and logged

#### Error Logging
- **File-Based Logging**: Errors logged to `/logs/error.log`
- **Log Format**: Timestamp, severity, user ID, IP address, error message
- **Log Rotation**: Logs should be rotated monthly in production

#### Error Types Logged
- PHP Errors and Warnings
- Exceptions and Fatal Errors
- Authentication Failures
- Database Errors
- File Upload Failures

### 7. Environment Configuration

#### Environment Variables
- **Database Credentials**: In .env file, not in source code
- **Site URL**: Configurable per environment
- **Debug Mode**: Disabled in production
- **Session Security**: HTTPS-only flag for production

#### Sensitive Data Protection
- **.env File**: Never committed to version control (in .gitignore)
- **.env.example**: Template provided for configuration
- **Credentials Rotation**: Update database credentials regularly

---

## Security Checklist for Deployment

### Pre-Deployment
- [ ] Database credentials moved to .env file
- [ ] DEBUG_MODE set to false in production
- [ ] SESSION_SECURE set to true (requires HTTPS)
- [ ] Database backed up
- [ ] HTTPS certificate installed and valid
- [ ] Error logs directory created with 755 permissions
- [ ] Upload directory outside web root if possible

### Ongoing
- [ ] Regular security updates (PHP, MySQL, OS)
- [ ] Log rotation configured (monthly minimum)
- [ ] Database user has minimal required privileges
- [ ] File permissions verified (644 files, 755 directories)
- [ ] Regular security audits performed
- [ ] Backup strategy implemented

### Additional Recommendations

#### Short Term
1. **Two-Factor Authentication**: Add TOTP/SMS 2FA for user accounts
2. **Account Lockout**: Lock accounts after multiple failed login attempts
3. **Email Verification**: Require email verification before account activation
4. **Password Reset**: Secure password reset with token expiration

#### Medium Term
1. **Audit Logging**: Log all sensitive user actions
2. **API Rate Limiting**: Add rate limiting on a per-endpoint basis
3. **IP Whitelisting**: Allow/deny access based on IP ranges (optional)
4. **Database Encryption**: Encrypt sensitive fields in database

#### Long Term
1. **Penetration Testing**: Annual third-party security audit
2. **Bug Bounty Program**: Consider bug bounty for security researchers
3. **Security Monitoring**: Implement SIEM for threat detection
4. **Incident Response**: Develop incident response plan

---

## Vulnerability Testing

### SQL Injection Testing
```bash
# Try entering SQL in login form
' OR '1'='1
' UNION SELECT * FROM users --
```
**Result**: Should be prevented by prepared statements

### XSS Testing
```bash
# Try entering JavaScript in profile fields
<script>alert('XSS')</script>
<img src=x onerror="alert('XSS')">
```
**Result**: Should be escaped and rendered as text

### CSRF Testing
- Submit forms without CSRF token
- Modify CSRF token value
**Result**: Should return 403 Forbidden error

### Session Fixation Testing
- Capture session ID before login
- Attempt to use after login
**Result**: Session ID should change after successful login

---

## Security Testing Tools

### Recommended Tools
- **OWASP ZAP**: Web application security scanner
- **Burp Suite Community**: Web proxy for manual testing
- **SQLMap**: SQL injection detection and exploitation
- **WPScan**: WordPress vulnerability scanner (if used)
- **SSL Labs**: HTTPS/SSL certificate testing

### Command Line Testing
```bash
# Test HTTP security headers
curl -I https://yourdomain.com

# Check SSL certificate
openssl s_client -connect yourdomain.com:443

# Test for common vulnerabilities
nikto -h https://yourdomain.com
```

---

## Security Policies

### Password Policy
- Minimum 8 characters
- Must contain uppercase letter
- Must contain lowercase letter
- Must contain number
- No dictionary words
- Change password every 90 days (recommended)

### Session Policy
- Timeout: 30 minutes of inactivity
- Regenerate on login
- Verify User-Agent consistency
- HTTPOnly and Secure flags set

### Data Retention
- User activity logs: 90 days
- Error logs: 30 days
- Backup retention: 6 months minimum
- Account deletion: Data deleted within 30 days

---

## Reporting Security Issues

If you discover a security vulnerability, please do NOT:
- Post it on public issue trackers
- Share it on social media
- Publicly disclose it

Instead:
1. Email security team with details
2. Include steps to reproduce
3. Allow 30 days for fix and disclosure
4. Follow responsible disclosure practices

---

## Resources

### OWASP
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [OWASP Session Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)

### PHP Security
- [PHP: Password Hashing](https://www.php.net/manual/en/faq.passwords.php)
- [PHP: Prepared Statements](https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php)
- [PHP: Security](https://www.php.net/manual/en/security.php)

### Cryptography
- [NIST Guidelines](https://pages.nist.gov/800-63-3/)
- [Have I Been Pwned](https://haveibeenpwned.com/) - Check compromised passwords

---

**Last Updated**: December 27, 2025  
**Status**: ✅ All security measures implemented and verified
