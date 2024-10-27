<?php


// CSS Function
function getStyleCss(){
    if(file_exists(__DIR__.'/style.php')){
        include_once(__DIR__.'/style.php');
    }else{
        echo "<h2> Theme Style Not Found </h2>";
    }
}
//Script function
function getScripts($isLoggedIn){
    if(file_exists(__DIR__.'/scripts.php')){
        require_once(__DIR__.'/scripts.php');
        if($isLoggedIn == true){
            echo admin_javaScript();
        }else{
            echo userSignScript();
        }
        
    }else{
        echo "<h2> JavaScript not found";
    }
}
// Footer call function
function get_header(){
    if(file_exists(__DIR__.'/header.php')){
        require_once(__DIR__.'/header.php');
    }
}// Footer call function
function get_footer(){
    if(file_exists(__DIR__.'/footer.php')){
        require_once(__DIR__.'/footer.php');
    }
}
// Connect Database

if(file_exists(__DIR__.'/config.php')){
    require_once(__DIR__.'/config.php');
}

/*******************************************************
 * Section Name: User Authentication Class
 * -----------------------------------------------------
 * Description: 
 *    - This class handles user authentication.
 *    - Functions include login, logout, and session management.
 *    - Passwords are hashed and stored securely.
 * -----------------------------------------------------
 * Author:        Md. Jobaer Hossain
 * Date Created:  October 23, 2024
 * Last Modified: October 23, 2024
 *******************************************************/
// UserServices class in signup.php
class UserServices {
    private $username;
    private $password;
    private $email;
    public $error = array();
    public $success="";
    /*******************************************************
     * SignUp function of the user
     * -----------------------------------------------------
     *  *******************************************************/
    public function signup($email, $user, $password){
        $dbConnection = new DatabaseAccess();
        
        // Validate inputs
        if (!$this->validateEmail($email)) {
            $error['email'] = 'Invalid email format.';
        }
        if (empty($user)) {
            $error['username'] = 'Username is required.';
        }
        if(!preg_match('/^[a-z][a-z0-9_-]{2,15}$/', $user)){
            $error['username'] = 'Username is invalid';
        }
        if (!$this->validatePassword($password)) {
            $error['password'] = 'Password must be at least 8 characters long.';
        }
        // If there are errors, return them
        if (!empty($error)) {
            return $error;
        }
        // Sanitize inputs
        $this->email = htmlspecialchars(strip_tags($email));
        $this->username = htmlspecialchars(strip_tags($user));
        $this->password = htmlspecialchars(strip_tags($password));
        // Account existancy check


        $exists = "SELECT user_login, user_email FROM master_users WHERE user_email = :email OR user_login = :username";
        $exeExists = $dbConnection->conn->prepare($exists);

        // Bind the function parameters, assuming you're passing $email and $user as arguments
        $exeExists->bindParam(':email', $email);  // Assuming $email is a parameter to the function
        $exeExists->bindParam(':username', $user); // Assuming $user is a parameter to the function
        $exeExists->execute();

        // Fetch the result
        $result = $exeExists->fetch(PDO::FETCH_ASSOC);

        // Check if user_email or user_login already exists
        if (!empty($result['user_email'])) {
            $error['email'] = 'Email already exists: ' . $result['user_email'];
        }

        if (!empty($result['user_login'])) {
            $error['username'] = 'Username already exists: ' . $result['user_login'];
        }

        // If there are errors, return them
        if (!empty($error)) {
            return $error;
        }
        // Hash the password
        $this->password = md5($this->password);
        // Prepare query
        $query = "INSERT INTO " . $dbConnection->master_users . " (user_email, user_login, user_pass, user_registered) 
                  VALUES (:email, :username, :password, NOW())";
                
        $stmt = $dbConnection->conn->prepare($query);
        // Bind values
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        // Execute query
        if ($stmt->execute()) {
            $success = "Success! Login Now by Username or Email";
            header('Location: auth-signin.php');
            exit();
        } else {
            $this->error['database'] = 'Failed to create the user.';
            return $this->error;
        }
    }
    
    /*******************************************************
     * SingIn function of the user
     * -----------------------------------------------------
     *  *******************************************************/
    function signIn($email = '', $user = '', $password = ''){
        $query = '';
        $dbConnection = new DatabaseAccess();
        $this->email = htmlspecialchars(strip_tags($email));
        $this->username = htmlspecialchars(strip_tags($user));
        $this->password = htmlspecialchars(strip_tags($password));
        // Data encryption object
        $encrypt = new EncrypDecrypt();
        if(!empty($this->email)){
            $query = "SELECT user_email, user_pass FROM master_users WHERE user_email = :email";
            $execute = $dbConnection->conn->prepare($query);
            $execute->bindParam(':email', $this->email);
            $execute->execute();
            $result = $execute->fetch(PDO::FETCH_ASSOC);
            //Login conditions
            ob_start();
            session_start();

            if ($result['user_email'] == $this->email && $result['user_pass'] == md5($this->password)) {
                // Include and initialize your encryption class
                $encrypt = new EncrypDecrypt();
                // Set session variable
                $_SESSION['type'] = $encrypt->encode($this->email);
                header("location: index.php");
                exit();
            } else {
                return "Invalid Credentials";
            }
            
        }else if(!empty($this->username)){
            $query = "SELECT user_login, user_pass FROM master_users WHERE  user_login = :username";
            $execute = $dbConnection->conn->prepare($query);
            $execute->bindParam(':username', $this->username);
            $execute->execute();
            $result = $execute->fetch(PDO::FETCH_ASSOC);
            //Login conditions
            ob_start();
            session_start();
            if($result['user_login'] == $this->username && $result['user_pass'] == md5($this->password)){
                //Set session
                $_SESSION['type'] = $encrypt->encode($this->username);
                header("location: index.php");
            }else{
                return "Invalid Credentials";
            }
            
        }



    }

    // Email validation function
    public function validateEmail($email) {
        $email = trim($email);
        if (empty($email)) {
            return false;
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }

    // Password validation function
    public function validatePassword($password) {
        return strlen($password) >= 8;
    }
}


class EncrypDecrypt {
    private $key;
    private $iv;
    public function __construct() {
        // Generate a 32-byte encryption key using sha256 to make it compatible with AES-256
        $this->key = substr(hash('sha256', 'EnCRypT10nK#Y!RiSRNn'), 0, 32);
        // Use a 16-byte initialization vector (IV) for AES-256-CBC
        $this->iv = substr(hash('md5', 'unique_iv'), 0, 16);
    }
    public function encode($value) {
        if (!$value) {
            return false;
        }
        // Encrypt the value using AES-256-CBC
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $this->iv);
        // Encode the binary data in base64 and reverse it for obfuscation
        return strrev(base64_encode($encrypted));
    }
    public function decode($value) {
        if (!$value) {
            return false;
        }
        // Reverse the obfuscation and decode from base64
        $encrypted = base64_decode(strrev($value));
        
        // Decrypt the value using AES-256-CBC
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $this->iv);
    }
}
