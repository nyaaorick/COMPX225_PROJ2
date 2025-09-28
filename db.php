<?php
/**
 * Database connection file for Kiwi Kloset
 * Contains database configuration and connection function
 */

 //will be js1160 if in university server
// Database configuration - MAMP Settings
$db_host = 'localhost';
$db_name = 'kiwi_kloset';  
$db_user = 'root';         // MAMP default username
$db_pass = 'root';         // MAMP default password

/**
 * Create database connection
 * @return mysqli Database connection object or null on failure
 */
function getDBConnection() {
    global $db_host, $db_name, $db_user, $db_pass;
    
    try {
        // Create connection
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8
        $conn->set_charset("utf8");
        
        return $conn;
        
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

/**
 * Close database connection
 * @param mysqli $conn Database connection to close
 */
function closeDBConnection($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}

/**
 * Execute a prepared statement safely
 * @param mysqli $conn Database connection
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types (e.g., "si" for string, integer)
 * @param array $params Parameters to bind
 * @return mysqli_result|bool Query result or false on failure
 */
function executeQuery($conn, $sql, $types = "", $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Query execution error: " . $e->getMessage());
        if (isset($stmt)) {
            $stmt->close();
        }
        return false;
    }
}
?>
