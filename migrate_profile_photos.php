<?php
require 'config.php';

// Check if columns exist and add them if they don't
$columns_to_add = [
    'profile_photo' => "ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL AFTER bio",
    'profile_background' => "ALTER TABLE users ADD COLUMN profile_background VARCHAR(255) NULL AFTER profile_photo"
];

foreach ($columns_to_add as $column_name => $alter_query) {
    $check_query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $column_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        if ($conn->query($alter_query)) {
            echo "✓ Column '{$column_name}' added successfully to users table\n";
        } else {
            echo "✗ Error adding column '{$column_name}': " . $conn->error . "\n";
        }
    } else {
        echo "✓ Column '{$column_name}' already exists\n";
    }
    $stmt->close();
}

// Create uploads/profiles directory if it doesn't exist
$upload_dir = __DIR__ . '/uploads/profiles/';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "✓ Created uploads/profiles directory\n";
    } else {
        echo "✗ Error creating uploads/profiles directory\n";
    }
} else {
    echo "✓ uploads/profiles directory already exists\n";
}

echo "\n✅ Database migration completed!\n";
?>
