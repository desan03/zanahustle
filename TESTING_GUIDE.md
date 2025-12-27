# 🧪 Testing Guide - Role-Based Services Implementation

This guide walks you through testing all the new role-based system and services features in ZanaHustle.

---

## ⚙️ Pre-Testing Setup

### 1. Ensure Database is Updated
```bash
# In your MySQL client or phpMyAdmin, verify these columns/tables exist:
# Users table should have 'primary_role' column
# Should have 'services' table
# Should have 'service_orders' table

# Quick SQL check:
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME='users' AND COLUMN_NAME='primary_role';

SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_NAME IN ('services', 'service_orders');
```

### 2. Clear Any Existing Sessions
- Close all browser tabs
- Clear cookies for localhost

### 3. Ensure XAMPP is Running
- Apache (port 80)
- MySQL (port 3306)

---

## 🧑‍💼 Test 1: Freelancer Registration & Login

### Step 1: Register as Freelancer
1. Navigate to `http://localhost/zanahustle/register.php`
2. Fill in registration form:
   - Username: `freelancer1`
   - Email: `freelancer1@test.com`
   - Password: `Test@123456`
   - First Name: `John`
   - Last Name: `Developer`
3. **Important**: Scroll down and select **"💼 Freelancer"** radio button
4. Click "Register"
5. Should redirect to login page

### Step 2: Login as Freelancer
1. Go to `http://localhost/zanahustle/login.php`
2. Enter credentials:
   - Username: `freelancer1`
   - Password: `Test@123456`
3. Click "Login"
4. **Expected Result**: Redirected to **Freelancer Dashboard** (`/freelancer_dashboard.php`)
5. Verify:
   - ✅ Welcome message shows "Welcome, freelancer1"
   - ✅ Role badge shows "Freelancer"
   - ✅ Sidebar has "My Services" link
   - ✅ Overview stats show service-related metrics

---

## 👔 Test 2: Client Registration & Login

### Step 1: Register as Client
1. Navigate to `http://localhost/zanahustle/register.php`
2. Fill in registration form:
   - Username: `client1`
   - Email: `client1@test.com`
   - Password: `Test@123456`
   - First Name: `Alice`
   - Last Name: `Buyer`
3. **Important**: Scroll down and select **"👔 Client"** radio button
4. Click "Register"
5. Should redirect to login page

### Step 2: Login as Client
1. Go to `http://localhost/zanahustle/login.php`
2. Enter credentials:
   - Username: `client1`
   - Password: `Test@123456`
3. Click "Login"
4. **Expected Result**: Redirected to **Browse Services** page (`/browse_services.php`)
5. Verify:
   - ✅ Page shows "Browse Freelancer Services" heading
   - ✅ Filter sidebar on left
   - ✅ Empty services message (since no services published yet)

---

## 📝 Test 3: Freelancer Publishing Services

### Step 1: Navigate to Services Page
1. Login as freelancer1 (use Test 1)
2. In sidebar, click **"💼 My Services"** link
3. Should see `freelancer_services.php` page

### Step 2: Publish First Service
1. Fill in "Create New Service" form:
   - **Service Title**: "Professional Web Development"
   - **Service Description**: "I will create a responsive website for your business using HTML5, CSS3, and JavaScript"
   - **Category**: "Web Development"
   - **Price**: "25000" (TZS)
   - **Delivery Time**: "7" (days)
   - **Number of Revisions**: "3"
   - **Features**: "Mobile responsive, Fast loading, SEO optimized, Contact form, Unlimited pages"

2. Click **"Publish Service"** button
3. **Expected Results**:
   - ✅ Success message appears
   - ✅ Service appears in "My Services" list below
   - ✅ Shows: Title, Category, Description (80 chars), Views (0), Orders (0), Delivery (7 days)
   - ✅ Price shows: "25,000 TZS ≈ $10.20 USD"
   - ✅ Edit and Delete buttons available

### Step 3: Publish Second Service (Different Category)
1. Fill in another service:
   - **Service Title**: "Logo Design"
   - **Service Description**: "I will design a unique, professional logo for your brand"
   - **Category**: "Graphic Design"
   - **Price**: "15000" (TZS)
   - **Delivery Time**: "3" (days)
   - **Number of Revisions**: "2"
   - **Features**: "Original design, High resolution, All formats provided"

2. Click "Publish Service"
3. Should now see **2 services** in "My Services" list

### Step 4: Check Analytics
- Go back to Freelancer Dashboard (click logo or Dashboard link)
- In Overview tab, verify analytics cards show:
  - **Published Services**: 2
  - Other stats (earnings, orders, etc. will be 0 for new services)

---

## 🔍 Test 4: Client Browsing & Filtering Services

### Step 1: Basic Service Discovery
1. Login as client1
2. Should see Browse Services page with both services published
3. Verify service cards show:
   - ✅ Service title
   - ✅ Freelancer name (John Developer)
   - ✅ Freelancer rating (not yet rated)
   - ✅ Category badge
   - ✅ Price in TZS + USD conversion
   - ✅ Delivery time and revisions count
   - ✅ "View" and "Order" buttons

### Step 2: Search Functionality
1. In filter sidebar, type in "Logo Design" search box
2. Click "Apply Filters"
3. **Expected**: Only Logo Design service shows
4. Clear search, search for "web"
5. **Expected**: Only Web Development service shows

### Step 3: Filter by Category
1. In Category filter, select "Web Development"
2. Click "Apply Filters"
3. **Expected**: Only Web Development service shows

### Step 4: Filter by Price Range
1. Clear category filter
2. Enter Max Price: "20000"
3. Click "Apply Filters"
4. **Expected**: Only Logo Design service shows (15,000 < 20,000)
5. Change to "30000"
6. **Expected**: Both services show

### Step 5: Sorting Options
1. Use toolbar dropdown to test each sort:
   - **Newest**: Web Dev first, then Logo Design (published order)
   - **Price Low to High**: Logo Design (15k) then Web Dev (25k)
   - **Price High to Low**: Web Dev (25k) then Logo Design (15k)
   - **Best Rated**: Both show (neither has ratings yet)
   - **Most Popular**: Both show (both have 0 orders)

---

## 🛒 Test 5: Client Ordering a Service

### Step 1: Place Order
1. Login as client1
2. On Browse Services, click **"Order"** button on Web Development service
3. Modal appears with title "Order: Professional Web Development"
4. In modal:
   - Price shows: "25,000 TZS ≈ $10.20 USD"
   - Delivery date field shows (minimum is tomorrow)

### Step 2: Select Delivery Date
1. Click delivery date field
2. Calendar picker appears
3. Select a date **at least 2-3 days from today**
4. Click "Confirm Order"
5. **Expected Results**:
   - ✅ Modal closes
   - ✅ Success message appears: "Order placed successfully!"
   - ✅ Service card's "Orders" count increments (0 → 1)

### Step 3: Place Another Order
1. Click **"Order"** on Logo Design service
2. Select delivery date
3. Click "Confirm Order"
4. Should now have ordered both services
5. Each service's order count should be 1

---

## 📊 Test 6: Service Analytics Update

### Step 1: Check Freelancer Dashboard Updates
1. Logout from client
2. Login as freelancer1
3. Go to Freelancer Dashboard (Overview tab)
4. **Expected Analytics**:
   - **Total Orders**: 2
   - **Completed Orders**: 0
   - **Active Orders**: 2
   - **Service Earnings**: "50,000 TZS ≈ $20.41 USD" (25k + 15k)

### Step 2: Check My Services Stats
1. Click "My Services" link
2. In service cards, verify:
   - Web Development: orders_count = 1
   - Logo Design: orders_count = 1
   - Price and delivery info all correct

---

## 🔄 Test 7: Role Switching

### Step 1: Switch Roles from Freelancer
1. Login as freelancer1
2. In navbar, click **"Switch Role"** button
3. Should redirect to `role_select.php`
4. Click **"👔 Client"** option
5. **Expected**: Redirects to Browse Services
6. Verify you can now see services you published

### Step 2: Switch Back
1. Click "Switch Role" again
2. Select **"💼 Freelancer"**
3. Should redirect to Freelancer Dashboard
4. Verify your services are still there with all stats

---

## ❌ Test 8: Error Handling

### Test Invalid Service Price
1. Login as freelancer1
2. Go to "My Services"
3. Try to publish service with price "5000" (less than minimum 10,000)
4. **Expected**: Error message appears, service not created

### Test Invalid Delivery Date (Client)
1. Login as client1
2. Try to order a service
3. Select today's date
4. **Expected**: Date picker prevents selection (minimum is tomorrow)

### Test Duplicate Order
1. Login as client1
2. Order same service twice
3. **Expected**: Both orders created (system allows it)

---

## 🔐 Test 9: Access Control

### Test Freelancer Access
1. Login as freelancer1
2. Try to directly access `/browse_services.php`
3. **Expected**: May show message or redirect to freelancer dashboard

### Test Client Access
1. Login as client1
2. Try to directly access `/freelancer_services.php`
3. **Expected**: May show message or redirect appropriately

---

## 📱 Test 10: Responsive Design

### On Desktop (1920px)
1. Browse Services shows 4 columns of service cards
2. Filters sidebar visible on left
3. All UI elements properly spaced

### On Tablet (768px)
1. Resize browser to 768px
2. Service grid shows 2 columns
3. Filters sidebar becomes smaller or collapses
4. Navigation remains accessible

### On Mobile (375px)
1. Resize browser to 375px
2. Service grid shows 1 column
3. Filters hide/collapse
4. Mobile menu working
5. All buttons and forms accessible

---

## 📝 Test Checklist

Copy and paste to track completion:

```
REGISTRATION & LOGIN:
☐ Freelancer registration with role selection
☐ Client registration with role selection
☐ Freelancer login redirects to dashboard
☐ Client login redirects to browse services
☐ Role persists on page refresh

FREELANCER SERVICES:
☐ Can publish service with all fields
☐ Service appears in "My Services" list
☐ Can publish multiple services
☐ Service prices display with TZS + USD
☐ Analytics show correct counts

CLIENT BROWSING:
☐ Can search services by keyword
☐ Can filter by category
☐ Can filter by price range
☐ Can sort by newest/price/rating/popular
☐ Service cards show all information
☐ Service card prices match freelancer's pricing

SERVICE ORDERING:
☐ Can order a service
☐ Delivery date picker works
☐ Order confirmation message appears
☐ Service order_count increments
☐ Can order multiple services from same/different freelancers

ANALYTICS:
☐ Freelancer dashboard shows service earnings
☐ Freelancer dashboard shows active orders
☐ Analytics cards show USD conversion
☐ Service stats update after orders placed

ROLE SWITCHING:
☐ Can switch from freelancer to client role
☐ Can switch from client to freelancer role
☐ Dashboard/content changes based on role
☐ Previous data persists after role switch

ERROR HANDLING:
☐ Minimum price validation works
☐ Required field validation works
☐ Delivery date validation works
☐ Proper error messages display

RESPONSIVE DESIGN:
☐ Works on desktop (1920px)
☐ Works on tablet (768px)
☐ Works on mobile (375px)
☐ Navigation accessible on all sizes
```

---

## 🐛 If You Find Issues

### Service Not Publishing
- Check browser console for JS errors (F12 → Console)
- Check MySQL for errors (error logs)
- Verify price is >= 10,000 TZS
- Verify all required fields filled

### Order Not Placing
- Check delivery date is valid
- Verify service still exists in database
- Check MySQL error logs
- Ensure session is still active

### Redirect Not Working
- Check primary_role value in users table
- Verify header() is called before HTML output
- Check for PHP errors in error_log
- Verify login session created properly

### Styling Issues
- Clear browser cache (Ctrl+Shift+Delete)
- Check CSS file is being loaded (F12 → Network)
- Verify no console errors blocking scripts
- Try different browser

---

## 📞 Support

If tests fail:
1. Check PHP error logs in `/xampp/apache/logs/`
2. Check MySQL error logs
3. Verify database schema matches `database.sql`
4. Ensure all files are uploaded correctly
5. Check file permissions on upload folders

---

**Happy Testing! 🎉**
