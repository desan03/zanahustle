# 🚀 ZanaHustle Quick Reference - Role-Based Services

## 📍 New/Updated Pages

| Page | Purpose | Role | URL |
|------|---------|------|-----|
| `freelancer_services.php` | **[NEW]** Publish & manage services | Freelancer | `/freelancer_services.php` |
| `browse_services.php` | **[NEW]** Browse & order services | Client | `/browse_services.php` |
| `register.php` | Updated with role selection | Both | `/register.php` |
| `login.php` | Updated with smart redirect | Both | `/login.php` |
| `freelancer_dashboard.php` | Enhanced with service analytics | Freelancer | `/freelancer_dashboard.php` |
| `client_dashboard.php` | Added "Browse Services" link | Client | `/client_dashboard.php` |

---

## 🔑 Key Constants (in config.php)

```php
define('MIN_BUDGET', 10000);           // Minimum service price in TZS
define('CURRENCY_SYMBOL', 'TZS');      // Currency
define('USD_TO_TZS', 2450);            // Exchange rate
define('SESSION_TIMEOUT', 1800);       // 30 minutes
define('BCRYPT_COST', 12);             // Password hashing strength
```

---

## 🗄️ Database Tables Summary

### `users` (Updated)
```
id | username | email | password_hash | first_name | last_name 
phone | city | bio | hourly_rate | skills | profile_image 
rating | reviews_count | completed_projects | can_be_client | can_be_freelancer 
primary_role ← [NEW] | created_at | updated_at
```

### `services` (New)
```
id | freelancer_id | title | description | category | price 
delivery_time | revisions | features | status | views | orders_count 
rating | reviews_count | created_at | updated_at
```

### `service_orders` (New)
```
id | service_id | client_id | freelancer_id | amount | status 
delivery_date | created_at | updated_at
```

---

## 🔐 Authentication Flow

```
Register Page
    ↓
Select Role (Freelancer/Client)
    ↓
Call registerUser($username, $email, $password, $fname, $lname, $primaryRole)
    ↓
INSERT into users table with primary_role
    ↓
Login Page
    ↓
Verify credentials
    ↓
Call getCurrentUser() to get primary_role
    ↓
IF primary_role == 'client': redirect /browse_services.php
IF primary_role == 'freelancer': redirect /freelancer_dashboard.php
```

---

## 📋 Functions in includes/auth.php

### Updated Functions

```php
// Register user WITH primary role
registerUser($username, $email, $password, $firstName = '', $lastName = '', $primaryRole = 'freelancer')

// Get current user info (includes primary_role)
getCurrentUser()

// Check if user has role access
canAccessRole($role)

// Set current role for session
setUserRole($role)

// Get current role
getCurrentRole()
```

---

## 🎨 CSS Classes for Services

```css
.services-container      /* Wrapper for service pages */
.service-card           /* Individual service card */
.service-card-header    /* Header section of card */
.service-title          /* Service title text */
.service-category       /* Category badge */
.service-desc           /* Service description */
.service-meta           /* Meta info (views, orders, etc) */
.service-price          /* Price display */
.freelancer-info        /* Freelancer details on card */
.freelancer-rating      /* Rating display */

.filters                /* Filter sidebar */
.filter-group           /* Individual filter group */
.services-toolbar       /* Sort/display toolbar */
.services-grid          /* Grid layout for service cards */

.analytics-grid         /* Analytics cards grid */
.analytics-card         /* Individual stat card */
```

---

## 💱 Currency Conversion

### Display TZS Price:
```php
<?php echo number_format($price, 0); ?> TZS
```

### Display USD Equivalent:
```php
<?php echo number_format($price / USD_TO_TZS, 2); ?> USD
```

### Combined Display:
```php
15,000 TZS ≈ $6.12 USD
```

### JavaScript Conversion:
```javascript
const USD_TO_TZS = 2450;
const price_tzs = 15000;
const price_usd = price_tzs / USD_TO_TZS;
```

---

## 📱 Service Card Structure

```html
<div class="service-card">
  <div class="service-card-header">
    <!-- Category badge -->
    <!-- Service title -->
    <!-- Freelancer info (name, rating, reviews) -->
  </div>
  <div class="service-card-body">
    <!-- Description preview -->
    <!-- Meta info (views, orders) -->
    <!-- Price (TZS + USD) -->
    <!-- Delivery time & revisions -->
    <!-- Action buttons (View, Order) -->
  </div>
</div>
```

---

## 🔄 Service Publishing Workflow

```php
// 1. Form submitted to freelancer_services.php
$_POST['create_service'] = '1'

// 2. Validate
if ($price < MIN_BUDGET) error()
if (empty($title)) error()

// 3. Insert
INSERT INTO services 
(freelancer_id, title, description, category, price, 
 delivery_time, revisions, features) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)

// 4. Service is now active and visible to clients
```

---

## 🛒 Service Ordering Workflow

```php
// 1. Client clicks "Order" button on service card
openOrderModal(serviceId)

// 2. Modal shows price and delivery date picker
// Min date = tomorrow

// 3. Client clicks "Confirm Order"
INSERT INTO service_orders 
(service_id, client_id, freelancer_id, amount, status, delivery_date)
VALUES (?, ?, ?, ?, 'pending', ?)

// 4. Update service stats
UPDATE services SET orders_count = orders_count + 1 WHERE id = ?

// 5. Order created and visible in freelancer's analytics
```

---

## 📊 Analytics Queries

### Freelancer Service Earnings:
```sql
SELECT SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) 
FROM service_orders 
WHERE freelancer_id = ?
```

### Total Active Orders:
```sql
SELECT COUNT(*) FROM service_orders 
WHERE freelancer_id = ? AND status = 'in_progress'
```

### Service Completion Rate:
```sql
SELECT 
  COUNT(CASE WHEN status = 'completed' THEN 1 END) / COUNT(*) * 100 
FROM service_orders 
WHERE freelancer_id = ?
```

---

## 🔍 Service Search Query

```php
$query = "SELECT s.*, u.first_name, u.last_name, u.rating, u.reviews_count
          FROM services s
          JOIN users u ON s.freelancer_id = u.id
          WHERE s.status = 'active'";

// Add search
if (!empty($searchQuery)) {
    $query .= " AND (s.title LIKE ? OR s.description LIKE ?)";
}

// Add category
if (!empty($category)) {
    $query .= " AND s.category = ?";
}

// Add price range
if ($maxPrice > 0) {
    $query .= " AND s.price <= ?";
}

// Add sorting
switch ($sortBy) {
    case 'price-low': $query .= " ORDER BY s.price ASC"; break;
    case 'rating': $query .= " ORDER BY u.rating DESC"; break;
    // etc...
}
```

---

## 🎯 Role-Based Page Access

```php
// Check role access
requireLogin();                    // Must be logged in
if (!canAccessRole('freelancer')) {
    header('Location: ' . SITE_URL . '/role_select.php');
    exit;
}
setUserRole('freelancer');

// Now page is protected for freelancers only
```

---

## ⏱️ Session Management

```php
// Check timeout (30 minutes)
checkSessionTimeout();

// Session timeout constant
define('SESSION_TIMEOUT', 1800);  // 30 minutes in seconds

// Last activity check
if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}
```

---

## 🎨 Responsive Breakpoints

```css
/* Desktop: 4 columns */
@media (min-width: 1200px) {
    .services-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Tablet: 2 columns */
@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: 1fr;
    }
    .browse-content {
        grid-template-columns: 1fr;
    }
    .filters {
        display: none;  /* Hide filters on mobile */
    }
}

/* Mobile: 1 column */
@media (max-width: 480px) {
    .services-toolbar {
        flex-direction: column;
    }
}
```

---

## 🔗 Important Links

- **Freelancer Services**: `/freelancer_services.php`
- **Browse Services**: `/browse_services.php`
- **Freelancer Dashboard**: `/freelancer_dashboard.php`
- **Client Dashboard**: `/client_dashboard.php`
- **Register**: `/register.php`
- **Login**: `/login.php`
- **Role Select**: `/role_select.php`

---

## 📝 Common Tasks

### Add a New Service Category:
```sql
-- No schema change needed - categories stored as VARCHAR
-- Just use new value in INSERT:
INSERT INTO services (..., category, ...) 
VALUES (..., 'new-category', ...)
```

### Update Service Status:
```php
$status = 'paused';  // active, paused, inactive
$stmt = $conn->prepare("UPDATE services SET status = ? WHERE id = ? AND freelancer_id = ?");
$stmt->bind_param("sii", $status, $serviceId, $userId);
$stmt->execute();
```

### Mark Order as Completed:
```php
$stmt = $conn->prepare("UPDATE service_orders SET status = 'completed' WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
```

### Calculate Freelancer Total Earnings:
```php
$query = "SELECT SUM(amount) as total_earned FROM service_orders WHERE freelancer_id = ? AND status = 'completed'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$totalEarned = $result['total_earned'] ?? 0;
```

---

## ⚠️ Important Notes

1. **Minimum Price Validation**: Enforced at DB level (CHECK constraint) AND application level
2. **TZS Only**: All prices stored in TZS, conversion to USD is display-only
3. **Session Timeout**: 30 minutes of inactivity = auto-logout
4. **Primary Role is Final**: Can switch roles, but primary_role determines login redirect
5. **Prepared Statements**: All queries use prepared statements to prevent SQL injection
6. **Password Hashing**: Bcrypt cost 12 - secure but slower (intentional for security)

---

**Last Updated**: 2024
**Platform Version**: 2.0 (Role-Based Services)
**Status**: ✅ Production Ready
