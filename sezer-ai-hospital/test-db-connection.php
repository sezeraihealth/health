<?php
/**
 * Comprehensive Database Connection Test
 * Tests all aspects of the database connection and configuration
 */

echo "=== SEZER AI Hospital Database Connection Test ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Check if PostgreSQL extension is available
echo "1. Checking PostgreSQL Extension Availability:\n";
if (extension_loaded('pdo_pgsql')) {
    echo "   ✅ PDO PostgreSQL extension is loaded\n";
} else {
    echo "   ❌ PDO PostgreSQL extension is NOT loaded\n";
    echo "   Error: Please install php-pgsql extension\n";
    exit(1);
}

if (extension_loaded('pgsql')) {
    echo "   ✅ PostgreSQL extension is loaded\n";
} else {
    echo "   ⚠️  PostgreSQL extension is not loaded (PDO is sufficient)\n";
}

// Test 2: Load environment variables
echo "\n2. Loading Environment Variables:\n";

// Include the env loader
require_once 'includes/class-env-loader.php';
SezerAIHospital_EnvLoader::init();

$env_vars = SezerAIHospital_EnvLoader::all();
if (empty($env_vars)) {
    echo "   ❌ Failed to load .env file\n";
    exit(1);
}

echo "   ✅ Environment file loaded successfully\n";
echo "   Variables found: " . count($env_vars) . "\n";

// Test 3: Validate environment variables
echo "\n3. Validating Environment Variables:\n";

$required_vars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
$missing_vars = [];

foreach ($required_vars as $var) {
    $value = SezerAIHospital_EnvLoader::get($var);
    if (empty($value)) {
        $missing_vars[] = $var;
        echo "   ❌ Missing: $var\n";
    } else {
        // Mask password for display
        $display_value = ($var === 'DB_PASSWORD') ? str_repeat('*', strlen($value)) : $value;
        echo "   ✅ $var = $display_value\n";
    }
}

if (!empty($missing_vars)) {
    echo "   ❌ Missing required environment variables: " . implode(', ', $missing_vars) . "\n";
    exit(1);
}

// Test 4: Network connectivity
echo "\n4. Testing Network Connectivity:\n";

$host = SezerAIHospital_EnvLoader::get('DB_HOST');
$port = SezerAIHospital_EnvLoader::get('DB_PORT');

echo "   Testing ping to $host...\n";
$ping_result = exec("ping -c 3 -W 5 $host 2>&1", $ping_output, $ping_exit_code);
if ($ping_exit_code === 0) {
    echo "   ✅ Host $host is reachable\n";
} else {
    echo "   ❌ Host $host is not reachable\n";
    echo "   Ping output: " . implode("\n   ", $ping_output) . "\n";
}

echo "   Testing port connectivity to $host:$port...\n";
$nc_result = exec("timeout 10 nc -zv $host $port 2>&1", $nc_output, $nc_exit_code);
if ($nc_exit_code === 0) {
    echo "   ✅ Port $port is open on $host\n";
} else {
    echo "   ❌ Port $port is not accessible on $host\n";
    echo "   Netcat output: " . implode("\n   ", $nc_output) . "\n";
}

// Test 5: Database connection attempt
echo "\n5. Testing Database Connection:\n";

$host = SezerAIHospital_EnvLoader::get('DB_HOST');
$port = SezerAIHospital_EnvLoader::get('DB_PORT');
$dbname = SezerAIHospital_EnvLoader::get('DB_NAME');
$user = SezerAIHospital_EnvLoader::get('DB_USER');
$password = SezerAIHospital_EnvLoader::get('DB_PASSWORD');

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname};connect_timeout=15";
echo "   DSN: $dsn\n";
echo "   User: $user\n";
echo "   Attempting connection...\n";

try {
    $start_time = microtime(true);
    
    $pdo = new PDO($dsn, $user, $password, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 15,
    ));
    
    $connection_time = round((microtime(true) - $start_time) * 1000, 2);
    echo "   ✅ CONNECTION SUCCESSFUL! (took {$connection_time}ms)\n";
    
    // Test 6: Database information
    echo "\n6. Database Information:\n";
    
    $version_stmt = $pdo->query('SELECT version()');
    $version = $version_stmt->fetchColumn();
    echo "   PostgreSQL Version: $version\n";
    
    $current_db_stmt = $pdo->query('SELECT current_database()');
    $current_db = $current_db_stmt->fetchColumn();
    echo "   Current Database: $current_db\n";
    
    $current_user_stmt = $pdo->query('SELECT current_user');
    $current_user = $current_user_stmt->fetchColumn();
    echo "   Current User: $current_user\n";
    
    // Test 7: Database permissions
    echo "\n7. Testing Database Permissions:\n";
    
    $permissions_tests = [
        'CREATE TABLE' => "CREATE TABLE IF NOT EXISTS test_permissions_table (id SERIAL PRIMARY KEY, test_column VARCHAR(50))",
        'INSERT' => "INSERT INTO test_permissions_table (test_column) VALUES ('test_value')",
        'SELECT' => "SELECT * FROM test_permissions_table LIMIT 1",
        'UPDATE' => "UPDATE test_permissions_table SET test_column = 'updated_value' WHERE id = 1",
        'DELETE' => "DELETE FROM test_permissions_table WHERE id = 1",
        'DROP TABLE' => "DROP TABLE IF EXISTS test_permissions_table"
    ];
    
    foreach ($permissions_tests as $permission => $sql) {
        try {
            $pdo->exec($sql);
            echo "   ✅ $permission: OK\n";
        } catch (PDOException $e) {
            echo "   ❌ $permission: FAILED - " . $e->getMessage() . "\n";
        }
    }
    
    // Test 8: Test plugin database class
    echo "\n8. Testing Plugin Database Class:\n";
    
    require_once 'includes/class-database.php';
    $db_instance = SezerAIHospital_Database::get_instance();
    
    if ($db_instance->is_connected()) {
        echo "   ✅ Plugin database class connected successfully\n";
        
        // Test table creation
        echo "   Testing table creation...\n";
        $table_result = $db_instance->create_tables();
        if ($table_result) {
            echo "   ✅ Database tables created successfully\n";
            
            // Check if tables exist
            $tables_to_check = ['patients', 'doctors', 'appointments'];
            foreach ($tables_to_check as $table) {
                $check_stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = '$table')");
                $exists = $check_stmt->fetchColumn();
                if ($exists) {
                    echo "   ✅ Table '$table' exists\n";
                } else {
                    echo "   ❌ Table '$table' does not exist\n";
                }
            }
        } else {
            echo "   ❌ Failed to create database tables\n";
        }
    } else {
        echo "   ❌ Plugin database class failed to connect\n";
    }
    
    // Test 9: Configuration review
    echo "\n9. Configuration Review:\n";
    
    // Check for any configuration issues
    $config_issues = [];
    
    // Check connection parameters
    if ($host === 'localhost' || $host === '127.0.0.1') {
        $config_issues[] = "Using localhost - ensure PostgreSQL is running locally";
    }
    
    if ($port != '5432') {
        $config_issues[] = "Non-standard PostgreSQL port: $port";
    }
    
    if (strlen($password) < 8) {
        $config_issues[] = "Password might be too short for security";
    }
    
    if (empty($config_issues)) {
        echo "   ✅ No configuration issues detected\n";
    } else {
        echo "   ⚠️  Configuration notes:\n";
        foreach ($config_issues as $issue) {
            echo "      - $issue\n";
        }
    }
    
    echo "\n=== CONNECTION TEST COMPLETED SUCCESSFULLY ===\n";
    echo "✅ Database is fully operational and ready for use!\n";
    
} catch (PDOException $e) {
    $connection_time = round((microtime(true) - $start_time) * 1000, 2);
    echo "   ❌ CONNECTION FAILED! (after {$connection_time}ms)\n\n";
    
    echo "ERROR DETAILS:\n";
    echo "   Error Code: " . $e->getCode() . "\n";
    echo "   Error Message: " . $e->getMessage() . "\n";
    echo "   SQL State: " . $e->errorInfo[0] ?? 'Unknown' . "\n";
    
    echo "\nPOSSIBLE CAUSES:\n";
    
    $error_code = $e->getCode();
    $error_message = $e->getMessage();
    
    if (strpos($error_message, 'timeout') !== false || strpos($error_message, 'Connection timed out') !== false) {
        echo "   🔍 TIMEOUT ERROR:\n";
        echo "      - Network connectivity issues\n";
        echo "      - Firewall blocking connection\n";
        echo "      - PostgreSQL server not responding\n";
        echo "      - High server load causing delays\n";
    } elseif (strpos($error_message, 'Connection refused') !== false) {
        echo "   🔍 CONNECTION REFUSED:\n";
        echo "      - PostgreSQL server is not running\n";
        echo "      - Wrong port number ($port)\n";
        echo "      - Server not accepting connections\n";
    } elseif (strpos($error_message, 'authentication failed') !== false || $error_code == 7) {
        echo "   🔍 AUTHENTICATION ERROR:\n";
        echo "      - Incorrect username: $user\n";
        echo "      - Incorrect password\n";
        echo "      - User doesn't have access to database: $dbname\n";
        echo "      - PostgreSQL authentication method mismatch\n";
    } elseif (strpos($error_message, 'database') !== false && strpos($error_message, 'does not exist') !== false) {
        echo "   🔍 DATABASE ERROR:\n";
        echo "      - Database '$dbname' does not exist\n";
        echo "      - User doesn't have access to the database\n";
    } elseif (strpos($error_message, 'host') !== false) {
        echo "   🔍 HOST ERROR:\n";
        echo "      - Incorrect hostname: $host\n";
        echo "      - DNS resolution issues\n";
        echo "      - Host not reachable\n";
    } else {
        echo "   🔍 GENERAL ERROR:\n";
        echo "      - Check all connection parameters\n";
        echo "      - Verify PostgreSQL server status\n";
        echo "      - Check network connectivity\n";
        echo "      - Review PostgreSQL logs\n";
    }
    
    echo "\nTROUBLESHOoting STEPS:\n";
    echo "   1. Verify PostgreSQL server is running\n";
    echo "   2. Check if host $host is reachable\n";
    echo "   3. Verify port $port is open\n";
    echo "   4. Confirm database '$dbname' exists\n";
    echo "   5. Verify user '$user' has proper permissions\n";
    echo "   6. Check PostgreSQL configuration (pg_hba.conf)\n";
    echo "   7. Review PostgreSQL server logs\n";
    
    exit(1);
}
?>