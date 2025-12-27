// ZanaHustle - Main JavaScript

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    initializeTabs();
});

/**
 * Initialize event listeners
 */
function initializeEventListeners() {
    // Tab switching
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });

    // Form submission
    const authForm = document.querySelector('.auth-form');
    if (authForm) {
        authForm.addEventListener('submit', validateAuthForm);
    }

    const jobForm = document.querySelector('.job-form');
    if (jobForm) {
        jobForm.addEventListener('submit', validateJobForm);
    }

    const proposalForm = document.querySelector('.proposal-form');
    if (proposalForm) {
        proposalForm.addEventListener('submit', validateProposalForm);
    }

    // Smooth scroll for links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Initialize tabs
 */
function initializeTabs() {
    const firstTab = document.querySelector('.tab-content:first-of-type');
    if (firstTab) {
        firstTab.classList.add('active');
        const firstMenuItem = document.querySelector('.menu-item:first-of-type');
        if (firstMenuItem) {
            firstMenuItem.classList.add('active');
        }
    }
}

/**
 * Switch between tabs
 */
function switchTab(tabName) {
    // Hide all tabs
    const allTabs = document.querySelectorAll('.tab-content');
    allTabs.forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active from all menu items
    const allMenuItems = document.querySelectorAll('.menu-item');
    allMenuItems.forEach(item => {
        item.classList.remove('active');
    });

    // Show selected tab
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
        
        // Add active to corresponding menu item
        const activeMenuItem = document.querySelector(`[data-tab="${tabName}"]`);
        if (activeMenuItem) {
            activeMenuItem.classList.add('active');
        }

        // Scroll to top of content
        selectedTab.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Validate authentication form
 */
function validateAuthForm(e) {
    const username = document.getElementById('username')?.value.trim();
    const password = document.getElementById('password')?.value;
    const email = document.getElementById('email')?.value.trim();
    const confirmPassword = document.getElementById('confirm_password')?.value;

    if (!username) {
        e.preventDefault();
        showError('Username is required');
        return false;
    }

    if (username.length < 3) {
        e.preventDefault();
        showError('Username must be at least 3 characters');
        return false;
    }

    if (!password) {
        e.preventDefault();
        showError('Password is required');
        return false;
    }

    if (password.length < 8) {
        e.preventDefault();
        showError('Password must be at least 8 characters');
        return false;
    }

    if (email && !isValidEmail(email)) {
        e.preventDefault();
        showError('Invalid email format');
        return false;
    }

    if (confirmPassword && password !== confirmPassword) {
        e.preventDefault();
        showError('Passwords do not match');
        return false;
    }

    return true;
}

/**
 * Validate job form
 */
function validateJobForm(e) {
    const title = document.getElementById('job_title')?.value.trim();
    const description = document.getElementById('job_description')?.value.trim();
    const budgetMin = parseFloat(document.getElementById('budget_min')?.value);
    const budgetMax = parseFloat(document.getElementById('budget_max')?.value);

    if (!title) {
        e.preventDefault();
        showError('Job title is required');
        return false;
    }

    if (title.length < 10) {
        e.preventDefault();
        showError('Job title must be at least 10 characters');
        return false;
    }

    if (!description) {
        e.preventDefault();
        showError('Job description is required');
        return false;
    }

    if (description.length < 50) {
        e.preventDefault();
        showError('Job description must be at least 50 characters');
        return false;
    }

    if (budgetMin <= 0) {
        e.preventDefault();
        showError('Minimum budget must be greater than 0');
        return false;
    }

    if (budgetMax <= budgetMin) {
        e.preventDefault();
        showError('Maximum budget must be greater than minimum budget');
        return false;
    }

    return true;
}

/**
 * Validate proposal form
 */
function validateProposalForm(e) {
    const bidAmount = parseFloat(document.getElementById('bid_amount')?.value);
    const coverLetter = document.getElementById('cover_letter')?.value.trim();
    const timeline = document.getElementById('timeline')?.value;

    if (bidAmount <= 0) {
        e.preventDefault();
        showError('Bid amount must be greater than 0');
        return false;
    }

    if (!coverLetter) {
        e.preventDefault();
        showError('Cover letter is required');
        return false;
    }

    if (coverLetter.length < 20) {
        e.preventDefault();
        showError('Cover letter must be at least 20 characters');
        return false;
    }

    if (!timeline) {
        e.preventDefault();
        showError('Timeline is required');
        return false;
    }

    return true;
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Show error message
 */
function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-error';
    alertDiv.textContent = message;
    
    const container = document.querySelector('.auth-form, .job-form, .proposal-form, .dashboard-content');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(date) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy');
    });
}

/**
 * Show notification
 */
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #10b981;
        color: white;
        padding: 15px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1001;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Check if element is in viewport
 */
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

/**
 * Fetch helper with error handling
 */
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            body: options.body ? JSON.stringify(options.body) : null
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        showError('An error occurred. Please try again.');
        throw error;
    }
}

/**
 * Toggle visibility of element
 */
function toggleElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

/**
 * Add animation to elements
 */
function observeElements() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.about-card, .job-card, .testimonial-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.5s ease';
        observer.observe(el);
    });
}

// Start observing elements when page loads
window.addEventListener('load', observeElements);

// Add keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Escape to close modal
    if (e.key === 'Escape') {
        const modal = document.querySelector('.modal[style*="display: block"]');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Ctrl/Cmd + S to auto-save (prevent default)
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        console.log('Auto-save triggered');
    }
});

// Log page analytics (placeholder)
window.addEventListener('load', () => {
    console.log('ZanaHustle page loaded successfully');
    // Add analytics tracking here
});
