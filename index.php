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
          FROM css_tb";
$stmt = $connection->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current date
$currentDate = date('D, F j'); // Example: "Thu, April 3"
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Link to your custom CSS file -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
        <div class="top-right d-flex justify-content-between align-items-center mb-4">
            <!-- Back Button (Positioned on the left) -->
            <a href="dash.php" class="btn btn-secondary" style="position: relative; left: -66rem; top: -5px; margin-top: 10px;">Back</a>

            <!-- Calendar Icon and Date (Positioned on the right) -->
            <div class="date">
                <i class="fa-solid fa-calendar-day"></i> <?= $currentDate ?>
            </div>

            <!-- Logout Button (Positioned on the right) -->
            <a href="logout.php" class="btn btn-danger logout-btn">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
        </div>

        <h1>Student CRUD System</h1>

        <!-- Add Student Modal Trigger -->
        <button class="btn btn-primary mb-4" id="btnCreateStudent">Add Student</button>

        <!-- Add Student Modal -->
        <div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="addStudentForm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="ProfileImage">Profile Image</label>
                                <input type="file" class="form-control" id="ProfileImage" name="profileImage" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="course">Course</label>
                                <input type="text" class="form-control" id="course" name="course" required>
                            </div>
                            <div class="form-group">
                                <label for="user_address">Address</label>
                                <input type="text" class="form-control" id="user_address" name="user_address">
                            </div>
                            <div class="form-group">
                                <label for="birthdate">Birthdate</label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="btnSubmitStudent">Add Student</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Student</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editStudentForm" enctype="multipart/form-data">
                            <input type="hidden" id="editStudentId" name="student_id">
                            <div class="form-group">
                                <label for="editFirstName">First Name</label>
                                <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="editLastName">Last Name</label>
                                <input type="text" class="form-control" id="editLastName" name="last_name" required>
                            </div>
                            <div class="form-group">
                                <label for="editEmail">Email</label>
                                <input type="email" class="form-control" id="editEmail" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="editGender">Gender</label>
                                <select class="form-control" id="editGender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="editCourse">Course</label>
                                <input type="text" class="form-control" id="editCourse" name="course" required>
                            </div>
                            <div class="form-group">
                                <label for="editAddress">Address</label>
                                <input type="text" class="form-control" id="editAddress" name="user_address">
                            </div>
                            <div class="form-group">
                                <label for="editBirthdate">Birthdate</label>
                                <input type="date" class="form-control" id="editBirthdate" name="birthdate" required>
                            </div>
                            <div class="form-group">
                                <label for="editProfileImage">Profile Image</label>
                                <input type="file" class="form-control" id="editProfileImage" name="profileImage">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="btnUpdateStudent">Update Student</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table of Students -->
        <table class="table table-dark table-striped mt-3">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Profile</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Course</th>
                    <th>Address</th>
                    <th>Age</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tablebody">
                <?php
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
            </tbody>
        </table>
    </div>

    <script src="script.js"></script>
</body>
</html>
