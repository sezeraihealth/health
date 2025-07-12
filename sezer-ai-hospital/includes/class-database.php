<?php
/**
 * Database Connection and Management
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SezerAIHospital_Database {
    
    private static $instance = null;
    private $pdo = null;
    private $connected = false;
    
    private function __construct() {
        $this->connect();
    }
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            $host = SezerAIHospital_EnvLoader::get('DB_HOST', 'localhost');
            $port = SezerAIHospital_EnvLoader::get('DB_PORT', '5432');
            $dbname = SezerAIHospital_EnvLoader::get('DB_NAME', '');
            $user = SezerAIHospital_EnvLoader::get('DB_USER', '');
            $password = SezerAIHospital_EnvLoader::get('DB_PASSWORD', '');
            
            if (empty($dbname) || empty($user)) {
                error_log('SEZER AI Hospital: Database credentials not configured');
                return;
            }
            
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
            
            $this->pdo = new PDO($dsn, $user, $password, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ));
            
            $this->connected = true;
            
        } catch (PDOException $e) {
            error_log('SEZER AI Hospital Database Error: ' . $e->getMessage());
            $this->connected = false;
        }
    }
    
    public function is_connected() {
        return $this->connected;
    }
    
    public function query($sql, $params = array()) {
        if (!$this->connected) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('SEZER AI Hospital Query Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function insert($table, $data) {
        if (!$this->connected) {
            return false;
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('SEZER AI Hospital Insert Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function update($table, $data, $where, $where_params = array()) {
        if (!$this->connected) {
            return false;
        }
        
        $set_clause = array();
        foreach (array_keys($data) as $column) {
            $set_clause[] = "{$column} = :{$column}";
        }
        $set_clause = implode(', ', $set_clause);
        
        $sql = "UPDATE {$table} SET {$set_clause} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $params = array_merge($data, $where_params);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('SEZER AI Hospital Update Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function create_tables() {
        if (!$this->connected) {
            return false;
        }
        
        $tables = array(
            'patients' => "
                CREATE TABLE IF NOT EXISTS patients (
                    id SERIAL PRIMARY KEY,
                    patient_id VARCHAR(50) UNIQUE NOT NULL,
                    user_id INTEGER DEFAULT 0,
                    first_name VARCHAR(100) NOT NULL,
                    last_name VARCHAR(100) NOT NULL,
                    date_of_birth DATE,
                    gender VARCHAR(20),
                    phone VARCHAR(20),
                    address TEXT,
                    emergency_contact_name VARCHAR(100),
                    emergency_contact_phone VARCHAR(20),
                    medical_history TEXT,
                    allergies TEXT,
                    current_medications TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'doctors' => "
                CREATE TABLE IF NOT EXISTS doctors (
                    id SERIAL PRIMARY KEY,
                    doctor_id VARCHAR(50) UNIQUE NOT NULL,
                    user_id INTEGER NOT NULL,
                    first_name VARCHAR(100) NOT NULL,
                    last_name VARCHAR(100) NOT NULL,
                    specialization VARCHAR(100),
                    license_number VARCHAR(50),
                    phone VARCHAR(20),
                    email VARCHAR(100),
                    department VARCHAR(100),
                    consultation_fee DECIMAL(10,2) DEFAULT 0,
                    bio TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'appointments' => "
                CREATE TABLE IF NOT EXISTS appointments (
                    id SERIAL PRIMARY KEY,
                    appointment_id VARCHAR(50) UNIQUE NOT NULL,
                    patient_id INTEGER REFERENCES patients(id),
                    doctor_id INTEGER REFERENCES doctors(id),
                    appointment_date TIMESTAMP NOT NULL,
                    duration INTEGER DEFAULT 30,
                    type VARCHAR(50) DEFAULT 'consultation',
                    status VARCHAR(20) DEFAULT 'scheduled',
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            "
        );
        
        foreach ($tables as $table_name => $sql) {
            try {
                $this->pdo->exec($sql);
            } catch (PDOException $e) {
                error_log("SEZER AI Hospital: Failed to create table {$table_name}: " . $e->getMessage());
            }
        }
        
        return true;
    }
}