# ZanaHustle - API & Function Reference

## Authentication Functions

Located in: `includes/auth.php`

### hashPassword($password)
```php
$hash = hashPassword('mypassword');
```
- Hashes password using bcrypt
- Cost factor: 12 (secure)
- Returns: Hashed string

### verifyPassword($password, $hash)
```php
if (verifyPassword('password', $storedHash)) {
    // Correct password
}
```
- Verifies password against hash
- Returns: boolean

### registerUser($username, $email, $password, $firstName, $lastName)
```php
$result = registerUser('john', 'john@example.com', 'pass123', 'John', 'Doe');
if ($result['success']) {
    $userId = $result['user_id'];
}
```
- Creates new user account
- Returns: `['success' => bool, 'error' => string, 'user_id' => int]`

### loginUser($username, $password)
```php
$result = loginUser('john', 'pass123');
if ($result['success']) {
    // User logged in, session set
    header('Location: dashboard.php');
}
```
- Logs in user and sets session
- Returns: `['success' => bool, 'error' => string, 'user_id' => int]`

### isLoggedIn()
```php
if (isLoggedIn()) {
    echo "User is logged in";
}
```
- Check if user has active session
- Returns: boolean

### getCurrentUserId()
```php
$userId = getCurrentUserId();
```
- Get current logged-in user ID
- Returns: int or null

### getCurrentUser()
```php
$user = getCurrentUser();
echo $user['username'];
echo $user['email'];
echo $user['first_name'];
echo $user['hourly_rate'];
```
- Get full user object
- Returns: array with user data

### logoutUser()
```php
logoutUser();
header('Location: index.php');
```
- Destroys session
- No return value

### checkSessionTimeout()
```php
if (!checkSessionTimeout()) {
    // Session expired
}
```
- Checks if session exceeded 30 min timeout
- Returns: boolean

### requireLogin()
```php
requireLogin();
// Code below only executes if logged in
```
- Redirects to login if not logged in
- No return value (redirects)

### requireGuest()
```php
requireGuest();
// Code below only executes if NOT logged in
```
- Redirects to role_select if already logged in
- No return value (redirects)

### setUserRole($role)
```php
setUserRole('client');
```
- Sets current session role
- Parameters: 'client' or 'freelancer'
- Returns: boolean

### getCurrentRole()
```php
$role = getCurrentRole();
echo $role; // 'client' or 'freelancer'
```
- Get current role from session
- Returns: string or null

### canAccessRole($role)
```php
if (canAccessRole('client')) {
    // User can access client features
}
```
- Check if user can access role
- Returns: boolean

---

## Database Functions

### Example Query Pattern
```php
// Prepare statement
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);

// Bind parameters (i=int, s=string, d=double)
$stmt->bind_param("i", $userId);

// Execute
$stmt->execute();

// Get results
$result = $stmt->get_result();
$user = $result->fetch_assoc(); // Single row
$users = $result->fetch_all(MYSQLI_ASSOC); // Multiple rows
```

### Insert Pattern
```php
$query = "INSERT INTO jobs (client_id, title, description) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $clientId, $title, $description);
$stmt->execute();

$newJobId = $conn->insert_id;
```

### Update Pattern
```php
$query = "UPDATE jobs SET status = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $status, $jobId);
$stmt->execute();

$affectedRows = $stmt->affected_rows;
```

### Delete Pattern
```php
$query = "DELETE FROM proposals WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $proposalId);
$stmt->execute();
```

---

## Form Validation (JavaScript)

Located in: `js/script.js`

### validateAuthForm()
```javascript
// Called automatically on form submit
// Validates: username, email, password, password match
// Shows error if validation fails
```

### validateJobForm()
```javascript
// Validates job posting form
// Checks: title (10+ chars), description (50+ chars), budget
```

### validateProposalForm()
```javascript
// Validates proposal submission
// Checks: bid amount, timeline, cover letter (20+ chars)
```

### isValidEmail(email)
```javascript
if (isValidEmail('test@example.com')) {
    // Valid email format
}
```

---

## Utility Functions (JavaScript)

### formatCurrency(amount)
```javascript
formatCurrency(1000); // Returns "$1,000.00"
```

### formatDate(date)
```javascript
formatDate('2025-12-27'); // Returns "Dec 27, 2025"
```

### switchTab(tabName)
```javascript
switchTab('my-jobs');
// Switches to tab with id="my-jobs"
```

### copyToClipboard(text)
```javascript
copyToClipboard('https://example.com');
// Copies to clipboard and shows notification
```

### showError(message)
```javascript
showError('Please fill in all fields');
// Shows error alert for 5 seconds
```

### showNotification(message)
```javascript
showNotification('Proposal submitted!');
// Shows success notification for 3 seconds
```

### fetchData(url, options)
```javascript
const data = await fetchData('/api/jobs', {
    method: 'POST',
    body: { title: 'New Job' }
});
```

### debounce(func, wait)
```javascript
const debouncedSearch = debounce(function() {
    // Search logic
}, 500);
```

### isInViewport(element)
```javascript
if (isInViewport(element)) {
    // Element is visible in viewport
}
```

---

## Database Tables

### users
```
id (INT) - Primary key
username (VARCHAR 50) - Unique
email (VARCHAR 100) - Unique
password_hash (VARCHAR 255)
first_name (VARCHAR 100)
last_name (VARCHAR 100)
profile_photo (VARCHAR 255)
bio (TEXT)
phone (VARCHAR 20)
country (VARCHAR 50)
city (VARCHAR 50)
can_be_client (BOOLEAN) - Default TRUE
can_be_freelancer (BOOLEAN) - Default TRUE
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
is_active (BOOLEAN)
```

### user_profiles
```
id (INT) - Primary key
user_id (INT) - Foreign key to users
skills (TEXT)
portfolio_url (VARCHAR 255)
hourly_rate (DECIMAL 10,2)
total_earnings (DECIMAL 15,2)
completed_projects (INT)
rating (DECIMAL 3,2)
reviews_count (INT)
verification_status (VARCHAR 20)
is_top_rated (BOOLEAN)
```

### jobs
```
id (INT) - Primary key
client_id (INT) - Foreign key to users
title (VARCHAR 255)
description (TEXT)
category (VARCHAR 100)
budget_type (VARCHAR 20) - 'fixed' or 'hourly'
budget_min (DECIMAL 10,2)
budget_max (DECIMAL 10,2)
duration (VARCHAR 50)
experience_level (VARCHAR 20)
status (VARCHAR 20) - 'open', 'in_progress', 'completed'
views (INT)
proposals_count (INT)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deadline (DATE)
```

### proposals
```
id (INT) - Primary key
job_id (INT) - Foreign key to jobs
freelancer_id (INT) - Foreign key to users
bid_amount (DECIMAL 10,2)
cover_letter (TEXT)
timeline (VARCHAR 50)
status (VARCHAR 20) - 'pending', 'accepted', 'rejected'
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
UNIQUE(job_id, freelancer_id)
```

### contracts
```
id (INT) - Primary key
job_id (INT) - Foreign key to jobs
client_id (INT) - Foreign key to users
freelancer_id (INT) - Foreign key to users
proposal_id (INT) - Foreign key to proposals
amount (DECIMAL 10,2)
status (VARCHAR 20)
start_date (TIMESTAMP)
end_date (DATE)
created_at (TIMESTAMP)
```

### reviews
```
id (INT) - Primary key
contract_id (INT) - Foreign key to contracts
reviewer_id (INT) - Foreign key to users
reviewee_id (INT) - Foreign key to users
rating (INT) - 1-5 stars
comment (TEXT)
created_at (TIMESTAMP)
```

### messages
```
id (INT) - Primary key
sender_id (INT) - Foreign key to users
receiver_id (INT) - Foreign key to users
subject (VARCHAR 255)
body (TEXT)
is_read (BOOLEAN)
created_at (TIMESTAMP)
```

---

## Session Variables

```php
$_SESSION['user_id']           // Current user ID (int)
$_SESSION['username']          // Username (string)
$_SESSION['first_name']        // User's first name (string)
$_SESSION['can_be_client']     // Can use client role (boolean)
$_SESSION['can_be_freelancer'] // Can use freelancer role (boolean)
$_SESSION['current_role']      // Active role (string)
$_SESSION['last_activity']     // Last action timestamp (int)
```

---

## Configuration Variables

Located in: `config.php`

```php
DB_HOST         // Database host (localhost)
DB_USER         // Database user (root)
DB_PASS         // Database password ('')
DB_NAME         // Database name (zanahustle)
SITE_NAME       // Platform name (ZanaHustle)
SITE_URL        // Base URL (http://localhost/ZanaHustle)
UPLOAD_DIR      // File upload directory
SESSION_TIMEOUT // Session timeout in seconds (1800)
```

---

## Error Codes

### HTTP Status Codes
- 200 - Success
- 301 - Redirect
- 400 - Bad request
- 401 - Unauthorized (not logged in)
- 403 - Forbidden (no permission)
- 404 - Not found
- 500 - Server error

### Custom Messages
- "Username already exists"
- "Invalid username or password"
- "Password must be at least 8 characters"
- "You have already submitted a proposal for this job"
- "Passwords do not match"
- "Job title and description are required"

---

## Security Best Practices Used

✅ Password hashing with bcrypt (cost 12)
✅ Prepared statements for all queries
✅ Input validation (server-side)
✅ Output escaping with htmlspecialchars()
✅ Session timeout enforcement
✅ SQL injection prevention
✅ XSS protection
✅ CSRF ready (tokens can be added)
✅ Role-based access control
✅ Secure password requirements

---

## Code Examples

### Register a User
```php
$result = registerUser('newuser', 'user@example.com', 'SecurePass123', 'John', 'Doe');
if ($result['success']) {
    echo "User registered: " . $result['user_id'];
}
```

### Post a Job
```php
$title = "Website Design";
$description = "Design a modern website for my business...";
$query = "INSERT INTO jobs (client_id, title, description, budget_min, budget_max) 
          VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("issdd", $clientId, $title, $description, 100, 500);
$stmt->execute();
$jobId = $conn->insert_id;
```

### Submit a Proposal
```php
$query = "INSERT INTO proposals (job_id, freelancer_id, bid_amount, cover_letter)
          VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iids", $jobId, $freelancerId, $bidAmount, $coverLetter);
$stmt->execute();
```

### Accept a Proposal
```php
// Update proposal status
$query = "UPDATE proposals SET status = 'accepted' WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $proposalId);
$stmt->execute();

// Create contract
$query2 = "INSERT INTO contracts (job_id, client_id, freelancer_id, proposal_id, amount)
           SELECT job_id, client_id, freelancer_id, id, bid_amount FROM proposals WHERE id = ?";
$stmt2 = $conn->prepare($query2);
$stmt2->bind_param("i", $proposalId);
$stmt2->execute();
```

---

## Next Steps

For Phase 2 development:

1. **Add Payment Processing**
   - Integrate Stripe/PayPal
   - Implement escrow system
   - Handle transactions

2. **Add Email Notifications**
   - Welcome emails
   - Job postings
   - Proposal alerts
   - Message notifications

3. **Implement Messaging System**
   - Real-time chat
   - Conversation history
   - Notifications

4. **Add File Management**
   - Portfolio uploads
   - Job attachments
   - Document storage

5. **Create Admin Panel**
   - User management
   - Job moderation
   - Analytics dashboard
   - Support tickets

---

## Support

For questions or issues:
- Check README.md for full documentation
- Review code comments in PHP files
- Check JavaScript console for errors (F12)
- Verify database structure in database.sql
