# 🚀 Deployment Checklist - ZanaHustle Role-Based Services

Use this checklist before deploying to production.

---

## 📋 Pre-Deployment Review

### Code Review
- [ ] All PHP files have proper error handling
- [ ] No hardcoded passwords or credentials
- [ ] All user inputs are validated
- [ ] SQL queries use prepared statements
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities (htmlspecialchars() used)
- [ ] Session management is secure
- [ ] Password hashing is using Bcrypt (cost 12)

### Database
- [ ] Database backup created
- [ ] `database.sql` has been run successfully
- [ ] All 11+ tables created correctly
- [ ] `primary_role` column exists in `users` table
- [ ] `services` table created with correct schema
- [ ] `service_orders` table created with correct schema
- [ ] Indexes created for performance
- [ ] Foreign key constraints in place
- [ ] CHECK constraint on minimum price (>= 10000)

### File Structure
- [ ] All `.php` files present
- [ ] `css/main.css` exists and is complete (1500+ lines)
- [ ] `js/script.js` exists
- [ ] `includes/auth.php`, `includes/header.php`, `includes/footer.php` present
- [ ] `config.php` has correct database credentials
- [ ] No temporary/test files in production folder
- [ ] `.htaccess` configured for URL rewriting (if needed)
- [ ] `uploads/` folder created and writable

### Configuration
- [ ] `config.php` has correct:
  - Database host, username, password
  - SITE_URL matches deployment domain
  - SITE_NAME is "ZanaHustle"
  - MIN_BUDGET = 10000
  - CURRENCY_SYMBOL = "TZS"
  - USD_TO_TZS = 2450
  - SESSION_TIMEOUT = 1800
  - BCRYPT_COST = 12

### Environment
- [ ] PHP version is 7.4 or higher
- [ ] MySQL version is 5.7 or higher
- [ ] MySQLi extension is enabled
- [ ] Session support enabled
- [ ] File uploads enabled
- [ ] Error logging configured
- [ ] HTTPS is enabled (recommended)

---

## 📱 Features Checklist

### Registration & Authentication
- [ ] Register page loads correctly
- [ ] Role selection UI visible (Freelancer/Client radio buttons)
- [ ] Form validation works
- [ ] Users can register successfully
- [ ] Primary role is saved to database
- [ ] Password is hashed securely
- [ ] Login page works
- [ ] Smart redirect based on primary_role works

### Freelancer Features
- [ ] Freelancer Dashboard loads and shows analytics
- [ ] "My Services" page loads
- [ ] Can publish new service
- [ ] Service title field validated
- [ ] Service price validation works (>= 10,000 TZS)
- [ ] Service category dropdown works
- [ ] Delivery time input works
- [ ] Revisions input works
- [ ] Features textarea works
- [ ] Published services appear in list
- [ ] Can edit service (edit button works)
- [ ] Can delete service (delete button with confirmation)
- [ ] Analytics cards show:
  - Published services count
  - Service earnings (TZS + USD)
  - Active orders count
  - Completed orders count
  - Rating/reviews

### Client Features
- [ ] Client Dashboard loads with Browse Services link
- [ ] Browse Services page loads
- [ ] Service cards display correctly
- [ ] Freelancer info shows (name, rating, reviews)
- [ ] Prices display in TZS + USD conversion
- [ ] Search filter works
- [ ] Category filter works
- [ ] Price range filter works
- [ ] Sort options work (newest, price, rating, popular)
- [ ] Service order count increments after order
- [ ] Can place order for a service
- [ ] Delivery date picker works (minimum tomorrow)
- [ ] Order confirmation message displays
- [ ] Service orders saved to database

### Currency & Pricing
- [ ] All prices display in TZS
- [ ] USD conversion shows (e.g., "≈ $6.12 USD")
- [ ] Exchange rate is correct (2450 TZS = 1 USD)
- [ ] Minimum price enforced (10,000 TZS)
- [ ] Price formatting uses thousand separators
- [ ] Decimal places correct (2 for USD, 0 for TZS)

### User Experience
- [ ] Navigation bar responsive
- [ ] Sidebar menu works on all pages
- [ ] Role badge shows correct role
- [ ] Welcome message shows username
- [ ] Logout button works
- [ ] Switch role button works
- [ ] Edit profile accessible
- [ ] All buttons clickable and functional
- [ ] Modals open/close smoothly
- [ ] Forms submit without page reload
- [ ] Success/error messages display
- [ ] Loading states work (if implemented)

### Responsive Design
- [ ] Page works on desktop (1920x1080)
- [ ] Page works on tablet (768x1024)
- [ ] Page works on mobile (375x667)
- [ ] Navigation accessible on all sizes
- [ ] Forms responsive on mobile
- [ ] Service cards resize properly
- [ ] Filters hide/show correctly on mobile
- [ ] Touch targets large enough (44px minimum)
- [ ] No horizontal scroll on mobile

### Performance
- [ ] Page load time < 3 seconds
- [ ] Database queries optimized (indexes used)
- [ ] CSS file is minified or optimized
- [ ] No console errors
- [ ] No memory leaks detected
- [ ] Session cleanup working

---

## 🔒 Security Checklist

- [ ] All forms have CSRF protection (if implemented)
- [ ] All database queries use prepared statements
- [ ] User input is validated server-side
- [ ] Output is escaped with htmlspecialchars()
- [ ] Password hashing uses Bcrypt (cost 12)
- [ ] Session timeout enforced (30 minutes)
- [ ] No sensitive data in URL parameters
- [ ] HTTPS enabled on production
- [ ] SQL error messages don't show on frontend
- [ ] File upload restrictions in place
- [ ] Role-based access control working
- [ ] Primary role check prevents unauthorized access
- [ ] Database connection uses SSL (if available)
- [ ] No debug mode enabled in production

---

## 🧪 Testing Checklist

### Functional Testing
- [ ] Create freelancer account and verify login redirect
- [ ] Create client account and verify login redirect
- [ ] Publish 2-3 services as freelancer
- [ ] Browse and filter services as client
- [ ] Order multiple services as client
- [ ] Verify order counts increment
- [ ] Check analytics reflect orders
- [ ] Switch roles and verify everything works
- [ ] Test with different browsers (Chrome, Firefox, Safari, Edge)

### Edge Cases
- [ ] Test with minimum valid price (10,000 TZS)
- [ ] Test with very large price (999,999,999 TZS)
- [ ] Test with future delivery date
- [ ] Test with past delivery date (should reject)
- [ ] Test with special characters in title/description
- [ ] Test with very long descriptions
- [ ] Test with empty optional fields
- [ ] Test rapid clicking (double-submit protection)
- [ ] Test session timeout (30+ minutes)
- [ ] Test browser back button after logout

### Browser Compatibility
- [ ] Google Chrome (latest)
- [ ] Mozilla Firefox (latest)
- [ ] Safari (latest)
- [ ] Microsoft Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

---

## 📊 Database Verification

### Table Structure
```sql
-- Verify tables exist
SHOW TABLES;

-- Verify users table has primary_role
DESCRIBE users;

-- Verify services table structure
DESCRIBE services;

-- Verify service_orders table structure
DESCRIBE service_orders;
```

### Sample Data
```sql
-- Check if test data exists or needs seeding
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM services;
SELECT COUNT(*) FROM service_orders;

-- Verify primary_role is set
SELECT id, username, primary_role FROM users LIMIT 10;

-- Verify service pricing constraints
SELECT id, title, price FROM services WHERE price < 10000;
```

### Indexes
```sql
-- Verify indexes exist for performance
SHOW INDEXES FROM services;
SHOW INDEXES FROM service_orders;
SHOW INDEXES FROM users;
```

---

## 🚀 Deployment Steps

### 1. Prepare Environment
```bash
# Create database
mysql -u root -p << EOF
CREATE DATABASE zanahustle;
EOF

# Import schema
mysql -u root -p zanahustle < database.sql

# Create uploads directory
mkdir -p /var/www/zanahustle/uploads
chmod 755 /var/www/zanahustle/uploads
```

### 2. Upload Files
```bash
# Copy all files to web root
scp -r ZanaHustle/* user@server:/var/www/zanahustle/

# Or use FTP/SFTP with your hosting provider
```

### 3. Configure System
```bash
# Update config.php with production settings
# - Database host, username, password
# - SITE_URL with actual domain
# - Error logging configuration

# Set proper file permissions
chmod 755 /var/www/zanahustle
chmod 644 /var/www/zanahustle/*.php
chmod 755 /var/www/zanahustle/uploads
chmod 755 /var/www/zanahustle/includes
```

### 4. Test Deployment
- [ ] Navigate to domain.com and verify landing page loads
- [ ] Test complete user flow (register → login → publish service → order service)
- [ ] Check error logs for any PHP warnings/errors
- [ ] Test on mobile device
- [ ] Test with slow network (3G simulation)

### 5. Enable Monitoring
- [ ] Set up error logging
- [ ] Set up database backup (daily/weekly)
- [ ] Monitor server resources
- [ ] Check uptime monitoring
- [ ] Set up email alerts for errors

### 6. Post-Deployment
- [ ] Remove any test accounts
- [ ] Update documentation with live URLs
- [ ] Announce platform to users
- [ ] Monitor error logs for first week
- [ ] Be ready for support requests

---

## 📧 Deployment Notification

Once deployed, send this to stakeholders:

```
Subject: ZanaHustle Platform Live - Role-Based Services

Dear Team,

ZanaHustle is now live with the following features:

✅ Dual-role system (Freelancer & Client)
✅ Freelancer service publishing and management
✅ Client service browsing and ordering
✅ Advanced filtering and search
✅ Real-time analytics dashboard
✅ TZS pricing with USD conversion
✅ Secure authentication (Bcrypt hashing)
✅ Responsive mobile design

Platform URL: https://zanahustle.com
Documentation: https://zanahustle.com/docs

Happy freelancing!
```

---

## 🔍 Maintenance Checklist

### Weekly
- [ ] Check error logs for patterns
- [ ] Review user feedback/support tickets
- [ ] Monitor database size growth
- [ ] Check disk space availability

### Monthly
- [ ] Database backup verification
- [ ] Performance analysis
- [ ] Security updates for PHP/MySQL
- [ ] Review analytics trends

### Quarterly
- [ ] Full security audit
- [ ] Load testing
- [ ] Dependency updates
- [ ] Disaster recovery drill

---

## ❌ Known Issues to Monitor

- None documented yet - platform in v2.0

---

## 📞 Support Escalation

If issues arise:

1. **Database Connection Error**: Check config.php credentials and MySQL status
2. **Service Not Publishing**: Check MIN_BUDGET validation and price input
3. **Login Redirect Fails**: Check primary_role column exists in database
4. **Services Not Showing**: Check service status = 'active' in database
5. **Analytics Wrong**: Check SQL queries and aggregation logic

---

## ✅ Final Sign-Off

```
Deployed By: _________________
Date: _________________
Tested By: _________________
Approved By: _________________

Notes: _________________________________________________________________
______________________________________________________________________
```

---

**Version**: 2.0 (Role-Based Services)
**Last Updated**: 2024
**Status**: Ready for Production ✅
