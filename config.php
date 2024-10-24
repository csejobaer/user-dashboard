<?php
class DatabaseAccess {
    public $master_users = 'master_users';
  

    function __construct(){
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "master_project";

        try {
            $this->conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}

