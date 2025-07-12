<?php
/**
 * Environment Variables Loader
 * 
 * @package SezerAIHospital
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SezerAIHospital_EnvLoader {
    
    private static $env_vars = array();
    private static $loaded = false;
    
    public static function init() {
        if (!self::$loaded) {
            self::load_env_file();
            self::$loaded = true;
        }
    }
    
    private static function load_env_file() {
        $env_file = SEZER_AI_HOSPITAL_PLUGIN_DIR . '.env';
        
        if (!file_exists($env_file)) {
            return;
        }
        
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') === false) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            self::$env_vars[$name] = $value;
        }
    }
    
    public static function get($key, $default = null) {
        return isset(self::$env_vars[$key]) ? self::$env_vars[$key] : $default;
    }
    
    public static function all() {
        return self::$env_vars;
    }
}