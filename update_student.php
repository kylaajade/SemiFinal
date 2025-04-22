<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'dbconnection.php'; // Include your database connection

// Check if the required data is provided in the request
if (isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $user_address = $_POST['user_address'];
    $birthdate = $_POST['birthdate'];

    // Initialize the image path variable
    $profile_image = null;

    // Fetch the current profile image from the database
    $stmt = $connection->prepare("SELECT profile_image FROM css_tb WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $current_image = $stmt->fetchColumn();

    // Check if a file was uploaded
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
        $target_dir = "image/"; // Directory to store uploaded images
        $target_file = $target_dir . basename($_FILES["profileImage"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES["profileImage"]["tmp_name"]);
        if ($check === false) {
            echo json_encode(['res' => 'error', 'msg' => 'File is not an image.']);
            exit;
        }

        // Check file size (e.g., 2MB limit)
        if ($_FILES["profileImage"]["size"] > 2 * 1024 * 1024) {
            echo json_encode(['res' => 'error', 'msg' => 'File size must be less than 2MB.']);
            exit;
        }

        // Allow only specific file types
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            echo json_encode(['res' => 'error', 'msg' => 'Only JPG, JPEG, PNG, and GIF files are allowed.']);
            exit;
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $target_file)) {
            $profile_image = $target_file; // Save the file path

            // Delete the old image if it exists
            if ($current_image && file_exists($current_image)) {
                unlink($current_image); // Delete the old image
            }
        } else {
            echo json_encode(['res' => 'error', 'msg' => 'Error uploading file.']);
            exit;
        }
    }

    try {
        // Prepare the SQL statement to update the student data
        if ($profile_image) {
            // If a new image was uploaded, update the image path in the database
            $stmt = $connection->prepare("UPDATE css_tb SET first_name = ?, last_name = ?, email = ?, gender = ?, course = ?, user_address = ?, birthdate = ?, profile_image = ? WHERE student_id = ?");
            $stmt->execute([$first_name, $last_name, $email, $gender, $course, $user_address, $birthdate, $profile_image, $student_id]);
        } else {
            // If no new image was uploaded, just update the other fields
            $stmt = $connection->prepare("UPDATE css_tb SET first_name = ?, last_name = ?, email = ?, gender = ?, course = ?, user_address = ?, birthdate = ? WHERE student_id = ?");
            $stmt->execute([$first_name, $last_name, $email, $gender, $course, $user_address, $birthdate, $student_id]);
        }

        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            echo json_encode(['res' => 'success']);
        } else {
            echo json_encode(['res' => 'error', 'msg' => 'No changes made or student not found.']);
        }
    } catch (PDOException $e) {
        // Return error response
        echo json_encode(['res' => 'error', 'msg' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Return error response if no student ID was provided
    echo json_encode(['res' => 'error', 'msg' => 'No student ID provided.']);
}
?>