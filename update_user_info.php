<?php 

// Update the data with ajax
if(file_exists(__DIR__.'/config.php')){
require_once(__DIR__.'/config.php'); 
}

$dbConnection = new DatabaseAccess();
$response = ['success' => false];

// Input Validation
function validateInput($data) {
    return htmlspecialchars(trim($data));
}

// Validate and collect inputs
$firstName = validateInput($_POST['first_name'] ?? '');
$lastName = validateInput($_POST['last_name'] ?? '');
$phone = validateInput($_POST['phone'] ?? '');
$skill = validateInput($_POST['skill'] ?? '');
$joiningDate = validateInput($_POST['joining_date'] ?? '');
$designation = validateInput($_POST['designation'] ?? '');
$city = validateInput($_POST['city'] ?? '');
$country = validateInput($_POST['country'] ?? '');
$zipCode = validateInput($_POST['zip_code'] ?? '');
$description = validateInput($_POST['description'] ?? '');

// Check required fields
if (!$firstName || !$lastName || !$phone || !$joiningDate || !$designation || !$city || !$country) {
    echo json_encode($response);
    exit;
}

// Prepare and bind SQL statement
try {
    $query = "UPDATE master_users 
              SET first_name = :first_name, last_name = :last_name, phone = :phone, skill = :skill, 
                  joining_date = :joining_date, designation = :designation, city = :city, 
                  country = :country, zip_code = :zip_code, description = :description 
              WHERE ID = :user_id";
              
    $stmt = $dbConnection->conn->prepare($query);
    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':skill', $skill);
    $stmt->bindParam(':joining_date', $joiningDate);
    $stmt->bindParam(':designation', $designation);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':zip_code', $zipCode);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':user_id', $_SESSION['user_id']); // Assumes user ID is stored in session

    // Execute update
    if ($stmt->execute()) {
        $response['success'] = true;

        // Handle image uploads
        if (!empty($_FILES['user_url']['name'])) {
            $profilePath = 'uploads/profile/' . basename($_FILES['user_url']['name']);
            if (move_uploaded_file($_FILES['user_url']['tmp_name'], $profilePath)) {
                $response['user_url'] = $profilePath;
            }
        }

        if (!empty($_FILES['user_cover_url']['name'])) {
            $coverPath = 'uploads/cover/' . basename($_FILES['user_cover_url']['name']);
            if (move_uploaded_file($_FILES['user_cover_url']['tmp_name'], $coverPath)) {
                $response['user_cover_url'] = $coverPath;
            }
        }
    }
} catch (PDOException $e) {
    error_log("Database update error: " . $e->getMessage());
}

echo json_encode($response);

