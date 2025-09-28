<?php
/**
 * Kiwi Kloset - Add Costume Page
 * Handles adding new costumes to the database via POST request
 */

// Include database connection
require_once 'db.php';

// Initialize variables
$new_costume = null;
$error_message = '';
$success_message = '';
$validation_errors = [];

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Sanitize and validate input data
    $name = trim($_POST['name'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $daily_rate = trim($_POST['daily_rate'] ?? '');
    $branch_id = trim($_POST['branch_id'] ?? '');
    $is_available = trim($_POST['is_available'] ?? '');
    
    // Validation
    if (empty($name)) {
        $validation_errors[] = 'Costume name is required.';
    } elseif (strlen($name) > 255) {
        $validation_errors[] = 'Costume name must be 255 characters or less.';
    }
    
    if (empty($size)) {
        $validation_errors[] = 'Size is required.';
    } elseif (strlen($size) > 255) {
        $validation_errors[] = 'Size must be 255 characters or less.';
    }
    
    if (empty($category)) {
        $validation_errors[] = 'Category is required.';
    } elseif (strlen($category) > 255) {
        $validation_errors[] = 'Category must be 255 characters or less.';
    }
    
    if (empty($daily_rate)) {
        $validation_errors[] = 'Daily rate is required.';
    } elseif (!is_numeric($daily_rate) || $daily_rate < 0 || $daily_rate > 999.99) {
        $validation_errors[] = 'Daily rate must be a valid number between 0.00 and 999.99.';
    }
    
    if (empty($branch_id)) {
        $validation_errors[] = 'Branch selection is required.';
    } elseif (!filter_var($branch_id, FILTER_VALIDATE_INT) || $branch_id <= 0) {
        $validation_errors[] = 'Please select a valid branch.';
    }
    
    if ($is_available === '') {
        $validation_errors[] = 'Availability status is required.';
    } elseif (!in_array($is_available, ['0', '1'])) {
        $validation_errors[] = 'Please select a valid availability status.';
    }
    
    // If validation passes, proceed with database insertion
    if (empty($validation_errors)) {
        // Get database connection
        $conn = getDBConnection();
        
        if (!$conn) {
            $error_message = 'Unable to connect to the database. Please check your connection settings.';
        } else {
            // First, verify that the branch exists
            $branch_check_query = "SELECT id, name, location FROM branches WHERE id = ?";
            $branch_result = executeQuery($conn, $branch_check_query, "i", [$branch_id]);
            
            if ($branch_result === false || $branch_result->num_rows === 0) {
                $error_message = 'Selected branch does not exist. Please choose a valid branch.';
            } else {
                $branch_info = $branch_result->fetch_assoc();
                
                // Generate new costume ID (get the next available ID)
                $id_query = "SELECT MAX(id) as max_id FROM costumes";
                $id_result = executeQuery($conn, $id_query);
                $next_id = 1; // Default to 1 if no costumes exist
                
                if ($id_result !== false) {
                    $id_row = $id_result->fetch_assoc();
                    if ($id_row['max_id'] !== null) {
                        $next_id = $id_row['max_id'] + 1;
                    }
                }
                
                // Insert new costume
                $insert_query = "
                    INSERT INTO costumes (id, name, size, category, daily_rate, branch_id, is_available) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ";
                
                $insert_result = executeQuery($conn, $insert_query, "isssdi", [
                    $next_id,
                    $name,
                    $size,
                    $category,
                    $daily_rate,
                    $branch_id,
                    $is_available
                ]);
                
                if ($insert_result !== false) {
                    // Success! Get the inserted costume details
                    $costume_query = "
                        SELECT c.id, c.name, c.size, c.category, c.daily_rate, c.is_available,
                               b.name AS branch_name, b.location AS branch_location
                        FROM costumes c
                        JOIN branches b ON c.branch_id = b.id
                        WHERE c.id = ?
                    ";
                    
                    $costume_result = executeQuery($conn, $costume_query, "i", [$next_id]);
                    
                    if ($costume_result !== false && $costume_result->num_rows > 0) {
                        $new_costume = $costume_result->fetch_assoc();
                        $success_message = "Costume successfully added to the inventory with ID #{$next_id}!";
                    } else {
                        $error_message = 'Costume was added but could not retrieve details.';
                    }
                } else {
                    $error_message = 'Failed to add costume to the database. Please try again.';
                }
            }
            
            // Close database connection
            closeDBConnection($conn);
        }
    } else {
        // Validation failed
        $error_message = 'Please correct the following errors:';
    }
} else {
    // Not a POST request
    $error_message = 'This page should be accessed by submitting the add costume form.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Costume Result - Kiwi Kloset</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header and Navigation -->
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <h1>🥝 Kiwi Kloset</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#costumes">All Costumes</a></li>
                <li><a href="index.php#rentals-form">Find Rentals</a></li>
                <li><a href="index.php#add-costume-form">Add Costume</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <main class="main-content">
            <h1 class="page-title">Add Costume Result</h1>

            <!-- Back Link -->
            <a href="index.php" class="back-link">← Back to Main Page</a>

            <?php if (!empty($success_message) && $new_costume): ?>
                <!-- Success Message -->
                <div class="alert alert-success">
                    <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
                </div>

                <!-- Display New Costume Information -->
                <div class="table-section">
                    <h2 class="section-title">New Costume Details</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Size</th>
                                <th>Category</th>
                                <th>Daily Rate</th>
                                <th>Branch</th>
                                <th>Location</th>
                                <th>Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background-color: #d4edda; border-left: 4px solid #27ae60;">
                                <td><strong><?php echo htmlspecialchars($new_costume['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($new_costume['name']); ?></td>
                                <td><?php echo htmlspecialchars($new_costume['size']); ?></td>
                                <td><?php echo htmlspecialchars($new_costume['category']); ?></td>
                                <td>$<?php echo number_format($new_costume['daily_rate'], 2); ?></td>
                                <td><?php echo htmlspecialchars($new_costume['branch_name']); ?></td>
                                <td><?php echo htmlspecialchars($new_costume['branch_location']); ?></td>
                                <td>
                                    <span style="color: <?php echo $new_costume['is_available'] ? '#27ae60' : '#e74c3c'; ?>; font-weight: bold;">
                                        <?php echo $new_costume['is_available'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Action Buttons -->
                <div style="text-align: center; margin: 2rem 0;">
                    <a href="index.php#add-costume-form" class="btn btn-success">Add Another Costume</a>
                    <a href="index.php#costumes" class="btn btn-secondary" style="margin-left: 1rem;">View All Costumes</a>
                    <a href="rentals.php?costume_id=<?php echo $new_costume['id']; ?>" class="btn" style="margin-left: 1rem;">View Rental History</a>
                </div>

            <?php else: ?>
                <!-- Error Message -->
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>

                <?php if (!empty($validation_errors)): ?>
                    <div class="alert alert-error">
                        <strong>Validation Errors:</strong>
                        <ul style="margin: 0.5rem 0 0 1.5rem;">
                            <?php foreach ($validation_errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Show submitted data if available (for debugging/reference) -->
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <div class="form-section">
                        <h3>Submitted Information</h3>
                        <table style="margin-bottom: 1rem;">
                            <tbody>
                                <tr><td><strong>Name:</strong></td><td><?php echo htmlspecialchars($_POST['name'] ?? 'Not provided'); ?></td></tr>
                                <tr><td><strong>Size:</strong></td><td><?php echo htmlspecialchars($_POST['size'] ?? 'Not provided'); ?></td></tr>
                                <tr><td><strong>Category:</strong></td><td><?php echo htmlspecialchars($_POST['category'] ?? 'Not provided'); ?></td></tr>
                                <tr><td><strong>Daily Rate:</strong></td><td><?php echo htmlspecialchars($_POST['daily_rate'] ?? 'Not provided'); ?></td></tr>
                                <tr><td><strong>Branch ID:</strong></td><td><?php echo htmlspecialchars($_POST['branch_id'] ?? 'Not provided'); ?></td></tr>
                                <tr><td><strong>Available:</strong></td><td><?php echo ($_POST['is_available'] ?? '') === '1' ? 'Yes' : (($_POST['is_available'] ?? '') === '0' ? 'No' : 'Not provided'); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Try Again Form -->
                <div class="form-section">
                    <h3>Try Adding the Costume Again</h3>
                    <form method="POST" action="add.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Costume Name:</label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       placeholder="e.g., Pirate Captain"
                                       maxlength="255"
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="size">Size:</label>
                                <input type="text" 
                                       id="size" 
                                       name="size" 
                                       placeholder="e.g., Mens Medium"
                                       maxlength="255"
                                       value="<?php echo htmlspecialchars($_POST['size'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="category">Category:</label>
                                <input type="text" 
                                       id="category" 
                                       name="category" 
                                       placeholder="e.g., Halloween, Movies, Gaming"
                                       maxlength="255"
                                       value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="daily_rate">Daily Rate ($):</label>
                                <input type="number" 
                                       id="daily_rate" 
                                       name="daily_rate" 
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0"
                                       max="999.99"
                                       value="<?php echo htmlspecialchars($_POST['daily_rate'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="branch_id">Branch ID:</label>
                                <input type="number" 
                                       id="branch_id" 
                                       name="branch_id" 
                                       placeholder="Enter branch ID (1-5)"
                                       min="1"
                                       value="<?php echo htmlspecialchars($_POST['branch_id'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="is_available">Available for Rent:</label>
                                <select id="is_available" name="is_available" required>
                                    <option value="">Select availability...</option>
                                    <option value="1" <?php echo ($_POST['is_available'] ?? '') === '1' ? 'selected' : ''; ?>>Yes - Available</option>
                                    <option value="0" <?php echo ($_POST['is_available'] ?? '') === '0' ? 'selected' : ''; ?>>No - Not Available</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Add Costume</button>
                        <a href="index.php" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</a>
                    </form>
                </div>

            <?php endif; ?>

            <!-- Help Section -->
            <div class="form-section" style="background-color: #e8f4fd;">
                <h3>Need Help?</h3>
                <p>If you're having trouble adding a costume, please check:</p>
                <ul style="margin-left: 2rem; margin-top: 1rem;">
                    <li>All required fields are filled out correctly</li>
                    <li>Daily rate is a valid number (e.g., 45.50)</li>
                    <li>Branch ID corresponds to an existing branch (1-5)</li>
                    <li>Database connection is working properly</li>
                </ul>
                <p style="margin-top: 1rem;">
                    <a href="index.php">Return to the main page</a> to see available branches or try adding the costume again.
                </p>
            </div>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.style.borderColor = '#e74c3c';
                            isValid = false;
                        } else {
                            field.style.borderColor = '#ddd';
                        }
                    });
                    
                    // Validate daily rate
                    const dailyRate = form.querySelector('[name="daily_rate"]');
                    if (dailyRate && dailyRate.value) {
                        const rate = parseFloat(dailyRate.value);
                        if (isNaN(rate) || rate < 0 || rate > 999.99) {
                            dailyRate.style.borderColor = '#e74c3c';
                            isValid = false;
                        }
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields correctly.');
                    }
                });
            }
            
            // Auto-focus on first input if there are errors
            if (document.querySelector('.alert-error')) {
                const firstInput = document.querySelector('input[type="text"]');
                if (firstInput) {
                    firstInput.focus();
                }
            }
        });
    </script>
</body>
</html>
