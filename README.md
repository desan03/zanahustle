# ZanaHustle - Freelancing Platform for East Africa

A modern, secure, mobile-first freelancing marketplace connecting East African talent with real opportunities.

## Features

### Core Platform Concept
- **Single Account, Dual Role**: One account can function as both Client and Freelancer
- **Role-Based Access**: Users can switch between roles at any time
- **Secure Authentication**: Password hashing with bcrypt
- **Session Management**: Secure session handling with timeout protection

### User Features

#### For Clients
- ✅ Post detailed job descriptions with attachments
- ✅ Manage job listings (open, in-progress, completed)
- ✅ Review and compare freelancer proposals
- ✅ Accept/reject proposals
- ✅ Message freelancers directly
- ✅ Rate and review completed work
- ✅ Track project progress

#### For Freelancers
- ✅ Browse available jobs with filters
- ✅ Submit competitive proposals with cover letters
- ✅ Manage profile and showcase skills
- ✅ Track earnings and completed projects
- ✅ Build reputation through ratings and reviews
- ✅ Get top-rated badges
- ✅ Portfolio management

### Platform Pages

1. **Landing Page** - Hero section, about, how it works, testimonials, partners, footer
2. **Registration** - Create new account
3. **Login** - Secure login
4. **Role Selection** - Choose role after login
5. **Client Dashboard** - Post jobs, manage proposals
6. **Freelancer Dashboard** - Browse jobs, submit proposals, manage profile
7. **Public Navigation** - Sticky navbar with role indicators

## Tech Stack

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL/MariaDB** - Database
- **bcrypt** - Password hashing
- **MySQLi** - Database connection (prepared statements for security)

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
