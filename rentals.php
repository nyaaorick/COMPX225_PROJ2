<?php
/**
 * Kiwi Kloset - Rentals Page
 * Displays all rentals for a specific costume ID
 */

// Include database connection
require_once 'db.php';

// Initialize variables
$costume_id = '';
$costume_info = null;
$rentals = [];
$error_message = '';
$success_message = '';

// Check if costume_id is provided via GET request
if (isset($_GET['costume_id']) && !empty($_GET['costume_id'])) {
    $costume_id = filter_var($_GET['costume_id'], FILTER_VALIDATE_INT);
    
    if ($costume_id === false || $costume_id <= 0) {
        $error_message = 'Invalid costume ID provided. Please enter a valid positive number.';
    } else {
        // Get database connection
        $conn = getDBConnection();
        
        if (!$conn) {
            $error_message = 'Unable to connect to the database. Please check your connection settings.';
        } else {
            // First, check if the costume exists and get its details
            $costume_query = "
                SELECT c.id, c.name, c.size, c.category, c.daily_rate, c.is_available,
                       b.name AS branch_name, b.location AS branch_location
                FROM costumes c
                JOIN branches b ON c.branch_id = b.id
                WHERE c.id = ?
            ";
            
            $costume_result = executeQuery($conn, $costume_query, "i", [$costume_id]);
            
            if ($costume_result !== false && $costume_result->num_rows > 0) {
                $costume_info = $costume_result->fetch_assoc();
                
                // Now get all rentals for this costume
                $rental_query = "
                    SELECT r.id, r.start_datetime, r.end_datetime,
                           c_customer.first_name, c_customer.last_name, c_customer.email, c_customer.phone
                    FROM rentals r
                    JOIN customers c_customer ON r.customer_id = c_customer.id
                    WHERE r.costume_id = ?
                    ORDER BY r.start_datetime DESC
                ";
                
                $rental_result = executeQuery($conn, $rental_query, "i", [$costume_id]);
                
                if ($rental_result !== false) {
                    $rentals = $rental_result->fetch_all(MYSQLI_ASSOC);
                    
                    if (empty($rentals)) {
                        $success_message = 'Costume found, but no rental history exists for this costume yet.';
                    }
                } else {
                    $error_message = 'Failed to retrieve rental information from the database.';
                }
                
            } else {
                $error_message = "No costume found with ID: $costume_id. Please check the costume ID and try again.";
            }
            
            // Close database connection
            closeDBConnection($conn);
        }
    }
} else {
    $error_message = 'No costume ID provided. Please specify a costume ID to view its rental history.';
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

/**
 * Calculate rental duration in hours
 */
function calculateDuration($start, $end) {
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    $duration_hours = round(($end_time - $start_time) / 3600, 1);
    
    if ($duration_hours >= 24) {
        $days = floor($duration_hours / 24);
        $hours = $duration_hours % 24;
        return $days . " day" . ($days > 1 ? "s" : "") . ($hours > 0 ? " " . $hours . "h" : "");
    } else {
        return $duration_hours . " hour" . ($duration_hours != 1 ? "s" : "");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental History - Kiwi Kloset</title>
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
            <h1 class="page-title">Rental History</h1>

            <!-- Back Link -->
            <a href="index.php" class="back-link">← Back to Main Page</a>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
                
                <div class="form-section">
                    <h3>Try Another Costume ID</h3>
                    <form method="GET" action="rentals.php">
                        <div class="form-group">
                            <label for="costume_id">Costume ID:</label>
                            <input type="number" 
                                   id="costume_id" 
                                   name="costume_id" 
                                   placeholder="Enter costume ID"
                                   min="1"
                                   value="<?php echo htmlspecialchars($_GET['costume_id'] ?? ''); ?>"
                                   required>
                        </div>
                        <button type="submit" class="btn">Search Rentals</button>
                    </form>
                </div>

            <?php else: ?>
                
                <?php if ($costume_info): ?>
                    <!-- Costume Information -->
                    <div class="table-section">
                        <h2 class="section-title">Costume Information</h2>
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
                                <tr>
                                    <td><?php echo htmlspecialchars($costume_info['id']); ?></td>
                                    <td><?php echo htmlspecialchars($costume_info['name']); ?></td>
                                    <td><?php echo htmlspecialchars($costume_info['size']); ?></td>
                                    <td><?php echo htmlspecialchars($costume_info['category']); ?></td>
                                    <td>$<?php echo number_format($costume_info['daily_rate'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($costume_info['branch_name']); ?></td>
                                    <td><?php echo htmlspecialchars($costume_info['branch_location']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $costume_info['is_available'] ? '#27ae60' : '#e74c3c'; ?>; font-weight: bold;">
                                            <?php echo $costume_info['is_available'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-info">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Rental History -->
                    <div class="table-section">
                        <h2 class="section-title">
                            Rental History 
                            <?php if (!empty($rentals)): ?>
                                <span style="font-size: 0.8em; color: #6c757d;">(<?php echo count($rentals); ?> rental<?php echo count($rentals) !== 1 ? 's' : ''; ?>)</span>
                            <?php endif; ?>
                        </h2>

                        <?php if (!empty($rentals)): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Rental ID</th>
                                        <th>Customer Name</th>
                                        <th>Customer Email</th>
                                        <th>Customer Phone</th>
                                        <th>Start Date & Time</th>
                                        <th>End Date & Time</th>
                                        <th>Duration</th>
                                        <th>Est. Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rentals as $rental): ?>
                                        <?php
                                            $duration = calculateDuration($rental['start_datetime'], $rental['end_datetime']);
                                            $duration_hours = round((strtotime($rental['end_datetime']) - strtotime($rental['start_datetime'])) / 3600, 1);
                                            $days = ceil($duration_hours / 24);
                                            $estimated_revenue = $days * $costume_info['daily_rate'];
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($rental['id']); ?></td>
                                            <td><?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($rental['email']); ?></td>
                                            <td><?php echo htmlspecialchars($rental['phone']); ?></td>
                                            <td><?php echo formatDateTime($rental['start_datetime']); ?></td>
                                            <td><?php echo formatDateTime($rental['end_datetime']); ?></td>
                                            <td><?php echo $duration; ?></td>
                                            <td>$<?php echo number_format($estimated_revenue, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- Summary Statistics -->
                            <?php
                                $total_rentals = count($rentals);
                                $total_revenue = 0;
                                foreach ($rentals as $rental) {
                                    $duration_hours = round((strtotime($rental['end_datetime']) - strtotime($rental['start_datetime'])) / 3600, 1);
                                    $days = ceil($duration_hours / 24);
                                    $total_revenue += $days * $costume_info['daily_rate'];
                                }
                            ?>
                            <div class="alert alert-info" style="margin-top: 1rem;">
                                <strong>Summary:</strong> 
                                This costume has been rented <?php echo $total_rentals; ?> time<?php echo $total_rentals !== 1 ? 's' : ''; ?> 
                                with an estimated total revenue of $<?php echo number_format($total_revenue, 2); ?>.
                            </div>

                        <?php else: ?>
                            <div class="no-data">
                                <i>📅</i>
                                <p>No rental history found for this costume.</p>
                                <small>This costume has not been rented yet or rental data is not available.</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Search Another Costume -->
                    <div class="form-section">
                        <h3>Search Another Costume</h3>
                        <form method="GET" action="rentals.php">
                            <div class="form-group">
                                <label for="new_costume_id">Different Costume ID:</label>
                                <input type="number" 
                                       id="new_costume_id" 
                                       name="costume_id" 
                                       placeholder="Enter another costume ID"
                                       min="1"
                                       required>
                            </div>
                            <button type="submit" class="btn btn-secondary">Search Rentals</button>
                        </form>
                    </div>

                <?php endif; ?>
            <?php endif; ?>

        </main>
    </div>

    <script>
        // Add some interactive functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight today's date in rental history
            const today = new Date();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const startCell = row.cells[4]; // Start date column
                const endCell = row.cells[5];   // End date column
                
                if (startCell && endCell) {
                    const startDate = new Date(startCell.textContent);
                    const endDate = new Date(endCell.textContent);
                    
                    // Check if rental is currently active (today is between start and end)
                    if (today >= startDate && today <= endDate) {
                        row.style.backgroundColor = '#fff3cd';
                        row.style.borderLeft = '4px solid #ffc107';
                    }
                }
            });
            
            // Form validation
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const costumeIdInput = form.querySelector('input[name="costume_id"]');
                    if (costumeIdInput && (!costumeIdInput.value || costumeIdInput.value <= 0)) {
                        e.preventDefault();
                        alert('Please enter a valid costume ID (positive number).');
                        costumeIdInput.focus();
                    }
                });
            });
        });
    </script>
</body>
</html>
