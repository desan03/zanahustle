# ZanaHustle - Freelance Service Marketplace

A modern, secure freelancing platform built with PHP and MySQL, enabling freelancers to sell services and clients to hire talent.

![Status](https://img.shields.io/badge/status-production%20ready-brightgreen)
![PHP](https://img.shields.io/badge/php-7.4%2B-blue)
![MySQL](https://img.shields.io/badge/mysql-5.7%2B-orange)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 🚀 Features

### Core Functionality
- **Dual-Role System**: Users can be both clients and freelancers
- **Service Management**: Freelancers publish services with pricing
- **Service Ordering**: Clients browse and order services with instant payment integration
- **Messaging System**: Secure messaging between clients and freelancers
- **Order Management**: Track orders with status updates (pending, in progress, completed)
- **User Profiles**: Detailed freelancer profiles with ratings and reviews
- **Service Browsing**: Search and filter services by category, price, and ratings

### Security Features
- **Bcrypt Password Hashing**: Cost parameter 12 for maximum security
- **CSRF Protection**: Token-based protection on all state-changing requests
- **SQL Injection Prevention**: Prepared statements on all database queries
- **XSS Protection**: Output escaping and Content Security Policy headers
- **Rate Limiting**: Brute force protection on login and registration
- **Session Management**: Regeneration after login, 30-minute timeout
- **Input Validation**: Comprehensive validation on all user inputs
- **File Upload Security**: MIME type validation and secure file naming

### Performance & Scalability
- **Prepared Statements**: Optimized database queries
- **HTTP Caching**: Browser and server caching headers
- **Responsive Design**: Mobile-first approach for all devices
- **Database Indexes**: Optimized queries for fast lookups

---

## 📋 Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache 2.4+ with mod_rewrite enabled (or Nginx)
- **SSL Certificate**: HTTPS required for production

## 🔧 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/zanahustle.git
cd zanahustle
```

### 2. Configure Environment

```bash
# Copy the example environment file
cp .env.example .env

# Edit .env with your database credentials
nano .env
```

### 3. Create Database

```bash
# Using MySQL client
mysql -u root -p < database.sql

# Or using phpMyAdmin
# 1. Create new database: abc
# 2. Import database.sql
```

### 4. Set File Permissions

```bash
# Set directory permissions
chmod 755 .
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Create and secure upload directory
mkdir -p uploads/images uploads/documents
chmod 755 uploads
chmod 755 uploads/images
chmod 755 uploads/documents

# Create logs directory
mkdir -p logs
chmod 755 logs
```

### 5. Configure Web Server

#### Apache (.htaccess)

The project includes `.htaccess` for URL rewriting. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx

Add to your Nginx configuration:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 6. Access the Application

```
http://localhost/ZanaHustle
```

---

## 📚 Documentation

### User Guides
- [Getting Started](docs/GETTING_STARTED.md) - First-time user guide
- [Freelancer Guide](docs/FREELANCER_GUIDE.md) - How to publish and manage services
- [Client Guide](docs/CLIENT_GUIDE.md) - How to find and hire freelancers

### Technical Documentation
- **[Security](SECURITY.md)** - Complete security implementation details
- **[API Reference](API_REFERENCE.md)** - API endpoints documentation
- [Database Schema](docs/DATABASE.md) - Database structure and relationships
- **[Deployment](DEPLOYMENT_CHECKLIST.md)** - Production deployment guide

### Development
- [Contributing](CONTRIBUTING.md) - How to contribute to the project
- [Architecture](docs/ARCHITECTURE.md) - System architecture overview
- [Troubleshooting](docs/TROUBLESHOOTING.md) - Common issues and solutions

---

## 🔐 Security Best Practices

### For Administrators
1. Change database password in `.env`
2. Set `DEBUG_MODE=false` in production
3. Set `SESSION_SECURE=true` when using HTTPS
4. Install valid SSL certificate
5. Configure regular backups
6. Monitor error logs in `/logs/`
7. Keep PHP and MySQL updated

### For Users
1. Use strong passwords (minimum 8 characters, uppercase, lowercase, numbers)
2. Never share your session link or cookies
3. Change password every 90 days
4. Report security issues to security@zanahustle.local

---

## 📊 Project Structure

```
zanahustle/
├── api/                    # API endpoints
│   ├── get_order_messages.php
│   ├── send_order_message.php
│   └── update_order_status.php
├── assets/                 # Static files
├── css/                    # Stylesheets
│   └── main.css
├── includes/               # PHP includes
│   ├── auth.php           # Authentication functions
│   └── security/          # Security utilities
│       ├── csrf.php
│       ├── error_handler.php
│       ├── headers.php
│       ├── rate_limit.php
│       └── validation.php
├── js/                    # JavaScript
│   └── script.js
├── logs/                  # Application logs (created at runtime)
├── uploads/               # User uploads (images, documents)
├── .env.example          # Environment configuration template
├── config.php            # Main configuration file
├── database.sql          # Database schema
├── register.php          # Registration page
├── login.php             # Login page
├── client_dashboard.php  # Client dashboard
└── freelancer_dashboard.php # Freelancer dashboard
```

---

## 🚀 Deployment

### Production Checklist

- [ ] Database backup configured
- [ ] SSL certificate installed
- [ ] `.env` file configured with production credentials
- [ ] `DEBUG_MODE` set to `false`
- [ ] `SESSION_SECURE` set to `true`
- [ ] File permissions set correctly
- [ ] Error logging configured
- [ ] Backups automated (daily minimum)
- [ ] Monitoring and alerts setup
- [ ] Firewall configured

See **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** for detailed instructions.

---

## 🐛 Troubleshooting

### Common Issues

**Q: "Database connection failed"**
- Check `.env` credentials
- Ensure MySQL server is running
- Verify database exists

**Q: "Permission denied" errors**
- Run `chmod 755` on directories
- Run `chmod 644` on files
- Ensure web server user has access

**Q: "CSRF token invalid"**
- Clear browser cookies
- Refresh the page
- Check session timeout

---

## 📞 Support

### Getting Help

1. **Documentation**: Check **[SECURITY.md](SECURITY.md)** and **[API_REFERENCE.md](API_REFERENCE.md)**
2. **Issues**: Check existing GitHub Issues
3. **Email**: support@zanahustle.local
4. **Security Issues**: security@zanahustle.local (do not use public issues)

---

**Made with ❤️ for the East African freelancing community**

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern, mobile-first styling
- **Vanilla JavaScript** - No dependencies, lightweight
- **Responsive Design** - Works on all devices

### Architecture
- **Object-oriented PHP** - Future-ready
- **MVC-ready structure** - Prepared for scaling
- **Prepared Statements** - SQL injection prevention
- **Session Management** - Security best practices

## Installation

### Prerequisites
- XAMPP or similar (Apache, PHP 7.4+, MySQL)
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Steps

1. **Extract files** to `c:\xampp\htdocs\ZanaHustle\`

2. **Create database**:
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Run the SQL from `database.sql` to create:
     - `zanahustle` database
     - All required tables with relationships
     - Indexes for performance

3. **Configure database** (if needed):
   - Edit `config.php` if your credentials differ:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'zanahustle');
   ```

4. **Start XAMPP**:
   - Start Apache and MySQL

5. **Access platform**:
   - Open: `http://localhost/ZanaHustle/`
   - Register a new account
   - Login and choose your role

## Project Structure

```
ZanaHustle/
├── config.php              # Database configuration & constants
├── database.sql            # Database schema
├── index.php              # Landing page
├── register.php           # Registration page
├── login.php              # Login page
├── logout.php             # Logout handler
├── role_select.php        # Role selection after login
├── client_dashboard.php   # Client dashboard
├── freelancer_dashboard.php # Freelancer dashboard
├── css/
│   └── main.css           # All styling (mobile-first, responsive)
├── js/
│   └── script.js          # Utility functions, validations, interactions
├── includes/
│   ├── auth.php           # Authentication functions
│   └── header.php         # Header/footer templates
├── uploads/               # File uploads directory
└── assets/                # Images and static files
```

## Database Schema

### Tables
- **users** - User accounts with dual-role support
- **user_profiles** - Extended user information
- **jobs** - Job postings by clients
- **job_attachments** - Files attached to jobs
- **proposals** - Freelancer proposals for jobs
- **contracts** - Active contracts/agreements
- **reviews** - Ratings and reviews
- **messages** - Direct messaging system

## Usage Guide

### For New Users

1. **Register**
   - Go to `/register.php`
   - Enter username, email, password
   - Account is automatically enabled for both roles

2. **Login**
   - Go to `/login.php`
   - Enter credentials
   - Redirected to role selection

3. **Choose Role**
   - Select "Hire Talent" (Client) or "Find Work" (Freelancer)
   - Can switch roles anytime via "Switch Role" button

### As a Client

1. **Post a Job**
   - Go to Dashboard → Post Job
   - Fill in: Title, Description, Budget, Duration, Experience Level
   - Click "Post Job"
   - View proposals on "Proposals" tab

2. **Manage Jobs**
   - View all posted jobs
   - See proposal count and views
   - Edit or close jobs

3. **Review Proposals**
   - See freelancer details and bids
   - Accept or reject proposals
   - Message freelancers

### As a Freelancer

1. **Browse Jobs**
   - Go to Dashboard → Browse Jobs
   - See all open jobs with budget and details
   - Click "Submit Proposal"

2. **Submit Proposal**
   - Enter bid amount
   - Write compelling cover letter
   - Select timeline
   - Submit

3. **Manage Profile**
   - Edit skills and experience
   - Set hourly rate
   - Build portfolio
   - View reputation and ratings

## Security Features

✅ **Password Security**
- Bcrypt hashing (cost factor 12)
- Minimum 8 characters required
- Password confirmation on registration

✅ **Database Security**
- Prepared statements (MySQLi)
- SQL injection prevention
- Input validation and sanitization

✅ **Session Security**
- Session timeout (30 minutes)
- Session hijacking prevention
- HTTPS ready

✅ **Access Control**
- Role-based access (client/freelancer)
- Server-side enforcement
- Protected pages require login

✅ **Data Protection**
- XSS prevention with htmlspecialchars()
- CSRF ready (implement tokens for forms)

## Future Enhancements

### Phase 2
- [ ] Real payment gateway integration (Stripe, M-Pesa)
- [ ] Email notifications
- [ ] Advanced search and filters
- [ ] Messaging system with real-time updates
- [ ] Contract management
- [ ] Escrow system

### Phase 3
- [ ] Admin dashboard
- [ ] Analytics and reporting
- [ ] API for mobile app
- [ ] Advanced reviews/ratings algorithm
- [ ] Dispute resolution system
- [ ] Milestone-based payments

### Phase 4
- [ ] Video interviews
- [ ] Portfolio showcase
- [ ] Skills verification
- [ ] Job recommendations (AI)
- [ ] Mobile app (React Native/Flutter)
- [ ] Multi-language support

## Best Practices for Development

### Code Style
- Use prepared statements for all DB queries
- Always validate input server-side
- Sanitize output with htmlspecialchars()
- Use meaningful variable names
- Add comments for complex logic

### Database
- Use transactions for critical operations
- Add proper indexes for performance
- Regular backups
- Monitor slow queries

### Frontend
- Mobile-first approach
- Semantic HTML
- Accessibility standards (WCAG)
- Progressive enhancement
- Lazy loading for images

## Troubleshooting

### Common Issues

**Database Connection Error**
- Check MySQL is running
- Verify DB credentials in `config.php`
- Ensure database and tables exist

**Login Issues**
- Clear browser cookies
- Check username/password
- Verify user exists in database
- Check user is_active status

**CSS Not Loading**
- Clear browser cache (Ctrl+Shift+Del)
- Check file path in HTML
- Verify CSS syntax

**Forms Not Submitting**
- Check JavaScript errors (F12 → Console)
- Verify form method is POST
- Check input field names match PHP variables

## Support & Contact

- **Email**: hello@zanahustle.com
- **Phone**: +255 123 456 789
- **Website**: www.zanahustle.com
- **Community**: [Slack/Discord link]

## License

ZanaHustle © 2025. All rights reserved.

Built with ❤️ for East Africa
