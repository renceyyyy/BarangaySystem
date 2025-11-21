<?php
class DatabaseConnection {
    private static $instance = null;
    private $connection;
    
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "barangaydb";
    
    private function __construct() {
        // Create connection
        $this->connection = new mysqli(
            $this->servername, 
            $this->username, 
            $this->password, 
            $this->dbname
        );
        
        // Check connection
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        
        // Set charset to utf8
        $this->connection->set_charset("utf8");
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    // Prevent cloning and unserializing
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function to get database connection
if (!function_exists('getDBConnection')) {
    function getDBConnection() {
        return DatabaseConnection::getInstance()->getConnection();
    }
}

// Helper function to escape strings
if (!function_exists('db_escape')) {
    function db_escape($string) {
        $conn = getDBConnection();
        return $conn->real_escape_string($string);
    }
}

// Helper function for prepared statements
if (!function_exists('db_prepare')) {
    function db_prepare($sql) {
        $conn = getDBConnection();
        return $conn->prepare($sql);
    }
}

// Helper function for queries
if (!function_exists('db_query')) {
    function db_query($sql) {
        $conn = getDBConnection();
        return $conn->query($sql);
    }
}
?>
