<?php
/**
 * Kiwi Kloset - Main Index Page
 * Displays company information, all costumes, and forms for staff operations
 */

// Include database connection
require_once 'db.php';

// Get database connection
$conn = getDBConnection();
$error_message = '';
$costumes = [];
$branches = [];

// Check if database connection is successful
if (!$conn) {
    $error_message = 'Unable to connect to the database. Please check your connection settings.';
} else {
    // Fetch all costumes with branch information
    $costume_query = "
        SELECT c.id, c.name, c.size, c.daily_rate, c.category, c.is_available, 
               b.name AS branch_name, b.location AS branch_location
        FROM costumes c
        JOIN branches b ON c.branch_id = b.id
        ORDER BY c.name, c.size
    ";
    
    $costume_result = executeQuery($conn, $costume_query);
    
    if ($costume_result !== false) {
        $costumes = $costume_result->fetch_all(MYSQLI_ASSOC);
    } else {
        $error_message = 'Failed to retrieve costume information from the database.';
    }
    
    // Fetch all branches for the add costume form
    $branch_query = "SELECT id, name, location FROM branches ORDER BY name";
    $branch_result = executeQuery($conn, $branch_query);
    
    if ($branch_result !== false) {
        $branches = $branch_result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Close database connection
    closeDBConnection($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiwi Kloset - Staff Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header and Navigation -->
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <h1>Kiwi Kloset</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="#costumes">All Costumes</a></li>
                <li><a href="#rentals-form">Find Rentals</a></li>
                <li><a href="#add-costume-form">Add Costume</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <main class="main-content">
            <!-- Page Title and Instructions -->
            <h1 class="page-title">Staff Management System</h1>
            
            <div class="instructions">
                <h3>Welcome to the Kiwi Kloset Staff Management System</h3>
                <p>
                    This system allows you to manage costume inventory and rentals. You can:
                </p>
                <ul style="margin-left: 2rem; margin-top: 1rem;">
                    <li><strong>View All Costumes:</strong> See the complete inventory below with availability status</li>
                    <li><strong>Find Rentals:</strong> Enter a costume ID to see all rental history for that costume</li>
                    <li><strong>Add New Costume:</strong> Register a new costume in the inventory system</li>
                </ul>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Costumes Table Section -->
            <section id="costumes" class="table-section">
                <h2 class="section-title">All Costumes in Inventory</h2>
                
                <?php if (!empty($costumes)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Costume Name</th>
                                <th>Size</th>
                                <th>Category</th>
                                <th>Daily Rate</th>
                                <th>Branch</th>
                                <th>Location</th>
                                <th>Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($costumes as $costume): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($costume['id']); ?></td>
                                    <td><?php echo htmlspecialchars($costume['name']); ?></td>
                                    <td><?php echo htmlspecialchars($costume['size']); ?></td>
                                    <td><?php echo htmlspecialchars($costume['category']); ?></td>
                                    <td>$<?php echo number_format($costume['daily_rate'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($costume['branch_name']); ?></td>
                                    <td><?php echo htmlspecialchars($costume['branch_location']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $costume['is_available'] ? '#27ae60' : '#e74c3c'; ?>; font-weight: bold;">
                                            <?php echo $costume['is_available'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p style="color: #6c757d; font-style: italic;">
                        Total costumes in inventory: <?php echo count($costumes); ?>
                    </p>
                <?php else: ?>
                    <div class="no-data">
                        <i>📦</i>
                        <p>No costumes found in the database.</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Forms Section -->
            <div class="form-grid">
                <!-- Find Rentals Form -->
                <section id="rentals-form" class="form-section">
                    <h2 class="section-title">Find Rentals by Costume</h2>
                    <form method="GET" action="rentals.php">
                        <div class="form-group">
                            <label for="costume_id">Costume ID:</label>
                            <input type="number" 
                                   id="costume_id" 
                                   name="costume_id" 
                                   placeholder="Enter costume ID to see rental history"
                                   min="1"
                                   required>
                        </div>
                        <button type="submit" class="btn">View Rentals</button>
                    </form>
                    <p style="margin-top: 1rem; color: #6c757d; font-size: 0.9rem;">
                        Enter a costume ID from the table above to see all rental history for that costume.
                    </p>
                </section>

                <!-- Add New Costume Form -->
                <section id="add-costume-form" class="form-section">
                    <h2 class="section-title">Add New Costume</h2>
                    <form method="POST" action="add.php">
                        <div class="form-group">
                            <label for="name">Costume Name:</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   placeholder="e.g., Pirate Captain"
                                   maxlength="255"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="size">Size:</label>
                            <input type="text" 
                                   id="size" 
                                   name="size" 
                                   placeholder="e.g., Mens Medium"
                                   maxlength="255"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category:</label>
                            <input type="text" 
                                   id="category" 
                                   name="category" 
                                   placeholder="e.g., Halloween, Movies, Gaming"
                                   maxlength="255"
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
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="branch_id">Branch:</label>
                            <select id="branch_id" name="branch_id" required>
                                <option value="">Select a branch...</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo htmlspecialchars($branch['id']); ?>">
                                        <?php echo htmlspecialchars($branch['name'] . ' - ' . $branch['location']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="is_available">Available for Rent:</label>
                            <select id="is_available" name="is_available" required>
                                <option value="1">Yes - Available</option>
                                <option value="0">No - Not Available</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Add Costume</button>
                    </form>
                </section>
            </div>
        </main>
    </div>

    <script>
        // Simple form validation and UX enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for navigation links
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
            
            // Form validation feedback
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
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
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            });
        });
    </script>
</body>
</html>
