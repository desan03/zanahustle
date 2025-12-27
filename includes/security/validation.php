<?php
/**
 * Input Validation Functions
 * Comprehensive validation for all user inputs
 */

/**
 * Validate email address
 */
function validateEmail($email) {
    $email = trim($email);
    if (strlen($email) > 254) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * Minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

/**
 * Validate username
 * 3-50 characters, alphanumeric and underscore only
 */
function validateUsername($username) {
    $username = trim($username);
    if (strlen($username) < 3 || strlen($username) > 50) {
        return false;
    }
    return preg_match('/^[a-zA-Z0-9_]+$/', $username) === 1;
}

/**
 * Validate full name
 * 2-100 characters, letters, spaces, hyphens only
 */
function validateFullName($name) {
    $name = trim($name);
    if (strlen($name) < 2 || strlen($name) > 100) {
        return false;
    }
    return preg_match('/^[a-zA-Z\s\-\']+$/', $name) === 1;
}

/**
 * Validate phone number
 * International format or standard format
 */
function validatePhoneNumber($phone) {
    $phone = preg_replace('/[^\d+\-\s()]/', '', $phone);
    return preg_match('/^[\d+\-\s()]{10,20}$/', $phone) === 1;
}

/**
 * Validate URL
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate currency amount
 */
function validateAmount($amount) {
    if (!is_numeric($amount)) {
        return false;
    }
    $amount = (float)$amount;
    return $amount >= 0 && $amount <= 999999.99;
}

/**
 * Validate service title
 * 5-200 characters
 */
function validateServiceTitle($title) {
    $title = trim($title);
    $length = strlen($title);
    return $length >= 5 && $length <= 200;
}

/**
 * Validate service description
 * 20-5000 characters
 */
function validateServiceDescription($description) {
    $description = trim($description);
    $length = strlen($description);
    return $length >= 20 && $length <= 5000;
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $maxSize = 5242880, $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) {
    if (!isset($file['tmp_name']) || !isset($file['size'])) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    if ($file['size'] === 0) {
        return false;
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    return in_array($mimeType, $allowedMimes);
}

/**
 * Sanitize string input
 */
function sanitizeString($input) {
    $input = trim($input);
    $input = stripslashes($input);
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize email
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitize URL
 */
function sanitizeUrl($url) {
    return filter_var(trim($url), FILTER_SANITIZE_URL);
}

/**
 * Sanitize number
 */
function sanitizeNumber($number) {
    return filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Validate form data - comprehensive validation
 */
function validateFormData($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? '';
        
        if (is_string($fieldRules)) {
            // Simple rule string like "required|email|max:50"
            $ruleArray = explode('|', $fieldRules);
            $fieldErrors = validateFieldRules($field, $value, $ruleArray);
            if (!empty($fieldErrors)) {
                $errors[$field] = $fieldErrors;
            }
        }
    }
    
    return $errors;
}

/**
 * Validate individual field against rules
 */
function validateFieldRules($field, $value, $rules) {
    $errors = [];
    
    foreach ($rules as $rule) {
        $ruleName = $rule;
        $ruleParams = [];
        
        if (strpos($rule, ':') !== false) {
            list($ruleName, $params) = explode(':', $rule, 2);
            $ruleParams = explode(',', $params);
        }
        
        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $errors[] = "Field '{$field}' is required";
                }
                break;
                
            case 'email':
                if (!empty($value) && !validateEmail($value)) {
                    $errors[] = "Field '{$field}' must be a valid email";
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < (int)$ruleParams[0]) {
                    $errors[] = "Field '{$field}' must be at least {$ruleParams[0]} characters";
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > (int)$ruleParams[0]) {
                    $errors[] = "Field '{$field}' must not exceed {$ruleParams[0]} characters";
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $errors[] = "Field '{$field}' must be numeric";
                }
                break;
                
            case 'url':
                if (!empty($value) && !validateUrl($value)) {
                    $errors[] = "Field '{$field}' must be a valid URL";
                }
                break;
        }
    }
    
    return $errors;
}
