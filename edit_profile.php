<?php
require 'config.php';
require 'includes/auth.php';

requireLogin();
$userId = getCurrentUserId();
$user = getCurrentUser();

// Handle form submission
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update user profile info
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        // Validation
        if (empty($first_name) || empty($last_name)) {
            $error_msg = 'First name and last name are required';
        } else {
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, country = ?, city = ?, bio = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $first_name, $last_name, $phone, $country, $city, $bio, $userId);
            
            if ($stmt->execute()) {
                $success_msg = 'Profile updated successfully!';
                $user = getCurrentUser(); // Refresh user data
            } else {
                $error_msg = 'Error updating profile: ' . $conn->error;
            }
            $stmt->close();
        }
    }
    
    // Upload profile photo
    if (isset($_POST['action']) && $_POST['action'] === 'upload_profile_photo') {
        if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            $error_msg = 'Please select a profile photo';
        } else {
            $file = $_FILES['profile_photo'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowed)) {
                $error_msg = 'Only image files (JPG, PNG, GIF, WebP) are allowed';
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
                $error_msg = 'File size must be less than 5MB';
            } else {
                $uploadDir = 'uploads/profiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = 'profile_' . $userId . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                    $stmt->bind_param("si", $filePath, $userId);
                    
                    if ($stmt->execute()) {
                        $success_msg = 'Profile photo uploaded successfully!';
                        $user = getCurrentUser();
                    } else {
                        $error_msg = 'Error saving photo to database';
                        unlink($filePath);
                    }
                    $stmt->close();
                } else {
                    $error_msg = 'Error uploading file';
                }
            }
        }
    }
    
    // Upload background photo
    if (isset($_POST['action']) && $_POST['action'] === 'upload_background_photo') {
        if (!isset($_FILES['background_photo']) || $_FILES['background_photo']['error'] !== UPLOAD_ERR_OK) {
            $error_msg = 'Please select a background photo';
        } else {
            $file = $_FILES['background_photo'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowed)) {
                $error_msg = 'Only image files (JPG, PNG, GIF, WebP) are allowed';
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
                $error_msg = 'File size must be less than 5MB';
            } else {
                $uploadDir = 'uploads/profiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = 'background_' . $userId . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $stmt = $conn->prepare("UPDATE users SET profile_background = ? WHERE id = ?");
                    $stmt->bind_param("si", $filePath, $userId);
                    
                    if ($stmt->execute()) {
                        $success_msg = 'Background photo uploaded successfully!';
                        $user = getCurrentUser();
                    } else {
                        $error_msg = 'Error saving background to database';
                        unlink($filePath);
                    }
                    $stmt->close();
                } else {
                    $error_msg = 'Error uploading file';
                }
            }
        }
    }
    
    // Update freelancer skills
    if (isset($_POST['action']) && $_POST['action'] === 'update_skills') {
        $skills = trim($_POST['skills'] ?? '');
        $hourly_rate = floatval($_POST['hourly_rate'] ?? 0);
        $portfolio_url = trim($_POST['portfolio_url'] ?? '');
        
        if (empty($skills)) {
            $error_msg = 'Skills are required';
        } elseif ($hourly_rate < 0) {
            $error_msg = 'Hourly rate cannot be negative';
        } else {
            $check = $conn->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
            $check->bind_param("i", $userId);
            $check->execute();
            $result = $check->get_result();
            
            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE user_profiles SET skills = ?, hourly_rate = ?, portfolio_url = ? WHERE user_id = ?");
                $stmt->bind_param("sdsi", $skills, $hourly_rate, $portfolio_url, $userId);
            } else {
                $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, skills, hourly_rate, portfolio_url) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("idss", $userId, $hourly_rate, $skills, $portfolio_url);
            }
            
            if ($stmt->execute()) {
                $success_msg = 'Skills updated successfully!';
            } else {
                $error_msg = 'Error updating skills: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Get user profile data
$profile = null;
$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/main.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .back-link {
            color: #6366f1;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            position: relative;
            min-height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .background-photo {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 12px;
            background-size: cover;
            background-position: center;
            opacity: 0.7;
            z-index: 0;
        }
        .background-upload {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }
        .background-upload label {
            background: rgba(0,0,0,0.5);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        .background-upload label:hover {
            background: rgba(0,0,0,0.8);
        }
        .background-upload input[type="file"] {
            display: none;
        }
        .profile-content {
            position: relative;
            z-index: 1;
            display: flex;
            gap: 30px;
            align-items: flex-end;
        }
        .profile-photo-section {
            position: relative;
        }
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            background: #f3f4f6;
        }
        .profile-photo-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: white;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .profile-photo-upload label {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6366f1;
            font-size: 20px;
        }
        .profile-photo-upload input[type="file"] {
            display: none;
        }
        .profile-info {
            flex: 1;
        }
        .profile-info h1 {
            font-size: 32px;
            margin: 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        .profile-info p {
            margin: 5px 0;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
        }
        .profile-form {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-section h3 {
            color: #0f172a;
            margin-bottom: 20px;
            font-size: 18px;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0f172a;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .profile-content {
                flex-direction: column;
                align-items: flex-start;
            }
            .profile-info h1 {
                font-size: 24px;
            }
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #10b981;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #7f1d1d;
            border-color: #ef4444;
        }
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #6366f1;
            color: white;
        }
        .btn-primary:hover {
            background-color: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        .btn-secondary {
            background-color: #e5e7eb;
            color: #0f172a;
        }
        .btn-secondary:hover {
            background-color: #d1d5db;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <span class="logo-icon">🚀</span>
                    <span class="logo-text">ZanaHustle</span>
                </a>
            </div>
            <div class="navbar-menu">
                <div class="nav-links">
                    <a href="<?php 
                        $role = isset($_SESSION['current_role']) ? $_SESSION['current_role'] : ($user['primary_role'] ?? 'client');
                        echo ($role === 'freelancer') ? SITE_URL . '/freelancer_dashboard.php' : SITE_URL . '/client_dashboard.php';
                    ?>" class="back-link">← Back to Dashboard</a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-logout">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="profile-container">
        <h1 style="margin-bottom: 30px; color: #0f172a;">My Profile</h1>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success">✓ <?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-error">✗ <?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
        
        <!-- Profile Header with Photos -->
        <div class="profile-header">
            <?php if (!empty($user['profile_background'])): ?>
                <div class="background-photo" style="background-image: url('<?php echo htmlspecialchars($user['profile_background']); ?>');"></div>
            <?php endif; ?>
            
            <div class="background-upload">
                <label for="background_photo" title="Upload background photo">
                    📷 Change Background
                    <form method="POST" enctype="multipart/form-data" style="display: inline;">
                        <input type="hidden" name="action" value="upload_background_photo">
                        <input type="file" id="background_photo" name="background_photo" accept="image/*" onchange="this.form.submit()">
                    </form>
                </label>
            </div>
            
            <div class="profile-content">
                <div class="profile-photo-section">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo" class="profile-photo">
                    <?php else: ?>
                        <div class="profile-photo" style="display: flex; align-items: center; justify-content: center; font-size: 60px;">👤</div>
                    <?php endif; ?>
                    
                    <div class="profile-photo-upload">
                        <label for="profile_photo" title="Upload profile photo">
                            📸
                            <form method="POST" enctype="multipart/form-data" style="display: none;">
                                <input type="hidden" name="action" value="upload_profile_photo">
                                <input type="file" id="profile_photo" name="profile_photo" accept="image/*" onchange="this.form.submit()">
                            </form>
                        </label>
                    </div>
                </div>
                
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                    <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                    <?php if (!empty($user['country'])): ?>
                        <p>📍 <?php echo htmlspecialchars($user['city'] . ', ' . $user['country']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Personal Information -->
        <div class="profile-form">
            <div class="form-section">
                <h3>Personal Information</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">About You</label>
                        <textarea id="bio" name="bio" placeholder="Tell clients/freelancers about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Professional Information (Freelancer Only) -->
        <?php if (isset($_SESSION['current_role']) && $_SESSION['current_role'] === 'freelancer'): ?>
        <div class="profile-form">
            <div class="form-section">
                <h3>Professional Information</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_skills">
                    
                    <div class="form-group">
                        <label for="skills">Skills *</label>
                        <textarea id="skills" name="skills" placeholder="e.g., Web Design, UI/UX, Graphic Design..." required><?php echo htmlspecialchars($profile['skills'] ?? ''); ?></textarea>
                        <small style="color: #666; display: block; margin-top: 5px;">Separate skills with commas</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="hourly_rate">Hourly Rate (<?php echo CURRENCY_SYMBOL; ?>)</label>
                            <input type="number" id="hourly_rate" name="hourly_rate" min="0" step="1000" value="<?php echo htmlspecialchars($profile['hourly_rate'] ?? 0); ?>" placeholder="e.g., 50000">
                        </div>
                        <div class="form-group">
                            <label>Hourly Rate (USD)</label>
                            <input type="text" readonly value="<?php echo number_format(($profile['hourly_rate'] ?? 0) / USD_TO_TZS, 2); ?> USD" style="background-color: #f3f4f6;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="portfolio_url">Portfolio URL</label>
                        <input type="url" id="portfolio_url" name="portfolio_url" value="<?php echo htmlspecialchars($profile['portfolio_url'] ?? ''); ?>" placeholder="https://yourportfolio.com">
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Update Skills</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer" style="margin-top: 60px;">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> ZanaHustle. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>