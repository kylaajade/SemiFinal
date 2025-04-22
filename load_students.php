<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

include 'dbconnection.php';
$query = "SELECT student_id, first_name, last_name, email, gender, course, user_address, 
          EXTRACT(YEAR FROM CURRENT_DATE) - EXTRACT(YEAR FROM birthdate) AS age, 
          profile_image 
          FROM css_tb 
          LIMIT 6"; // ✅ Apply LIMIT here
$stmt = $connection->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate table rows for the students
foreach ($students as $student) {
    echo "<tr>";
    echo "<td>{$student['student_id']}</td>";
    echo "<td>";
    if (!empty($student['profile_image'])) {
        echo "<img src='{$student['profile_image']}' alt='Profile Image' width='50'>";
    } else {
        echo "No Image";
    }
    echo "</td>";
    echo "<td>{$student['first_name']}</td>";
    echo "<td>{$student['last_name']}</td>";
    echo "<td>{$student['email']}</td>";
    echo "<td>{$student['gender']}</td>";
    echo "<td>{$student['course']}</td>";
    echo "<td>{$student['user_address']}</td>";
    echo "<td>{$student['age']}</td>";
    echo "<td>
            <button class='btn btn-warning btn-sm btnEditStudent' data-id='{$student['student_id']}'>Edit</button>
            <button class='btn btn-danger btn-sm btnDeleteStudent' data-id='{$student['student_id']}'>Delete</button>
          </td>";
    echo "</tr>";
}
?>
