<?php
// setup_db.php
// Force database creation using PHP connection and import database.sql
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP
$port = 3307; // Verified port

echo "<h2>Database Setup Utility</h2>";

// 1. Connect without DB selected
$conn = new mysqli($host, $user, $pass, '', $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<p>✅ Connected to MySQL server.</p>";

// 2. Create Database
$sql = "CREATE DATABASE IF NOT EXISTS civicconnect";
if ($conn->query($sql) === TRUE) {
    echo "<p>✅ Database 'civicconnect' created or exists.</p>";
} else {
    die("❌ Error creating database: " . $conn->error);
}

// 3. Select Database
$conn->select_db('civicconnect');

// 4. Import database.sql
$sqlFile = __DIR__ . '/database.sql';
if (!file_exists($sqlFile)) {
    die("❌ Error: database.sql not found at $sqlFile");
}

$sqlContent = file_get_contents($sqlFile);

// Remove comments to avoid issues with multi_query sometimes
$sqlContent = preg_replace('/--.*$/m', '', $sqlContent);

if ($conn->multi_query($sqlContent)) {
    echo "<p>✅ Database schema import started...</p>";
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // Check for more results
    } while ($conn->more_results() && $conn->next_result());
    
    if ($conn->error) {
        echo "<p>❌ Error importing schema: " . $conn->error . "</p>";
    } else {
        echo "<p>✅ Database schema imported successfully from database.sql.</p>";
    }
} else {
    echo "<p>❌ Error preparing import: " . $conn->error . "</p>";
}

echo "<h3>Setup Complete. You can now use the app.</h3>";
$conn->close();
?>
