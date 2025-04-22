<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
// Today's Date
$currentDate = date('D, F j'); // Example: "Thu, April 3"

include 'dbconnection.php';

// Query to get user info based on session email
$queryUserInfo = "SELECT profile_image, first_name, last_name, email FROM users_tb WHERE email = :email";
$stmtUserInfo = $connection->prepare($queryUserInfo);
$stmtUserInfo->execute(['email' => $_SESSION['email']]);
$userInfo = $stmtUserInfo->fetch(PDO::FETCH_ASSOC);

// Query to count total students
$query = "SELECT COUNT(*) as total_students FROM css_tb";
$stmt = $connection->prepare($query);
$stmt->execute();
$totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'];

// Query to get the number of students created over time (e.g., by year)
$queryCreated = "SELECT YEAR(date_created) as year, COUNT(*) as count FROM css_tb GROUP BY year ORDER BY year";
$stmtCreated = $connection->prepare($queryCreated);
$stmtCreated->execute();
$studentCreationData = $stmtCreated->fetchAll(PDO::FETCH_ASSOC);

// Query for course enrollment
$queryCourse = "SELECT course, COUNT(*) as count FROM css_tb GROUP BY course";
$stmtCourse = $connection->prepare($queryCourse);
$stmtCourse->execute();
$courseEnrollment = $stmtCourse->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch all users from users_tb
$queryUsers = "SELECT user_id, first_name, last_name, email, gender, course, is_verified FROM users_tb";
$stmtUsers = $connection->prepare($queryUsers);
$stmtUsers->execute();
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Query to get verification trends by year
$queryVerifiedTrends = "SELECT 
    YEAR(created_at) as year,
    SUM(is_verified = 1) as verified_count,
    SUM(is_verified = 0) as unverified_count
    FROM users_tb 
    GROUP BY year 
    ORDER BY year";
$stmtVerifiedTrends = $connection->prepare($queryVerifiedTrends);
$stmtVerifiedTrends->execute();
$verificationTrends = $stmtVerifiedTrends->fetchAll(PDO::FETCH_ASSOC);

// Count total verified and unverified users
$queryVerificationCounts = "SELECT 
    SUM(is_verified = 1) as total_verified,
    SUM(is_verified = 0) as total_unverified
    FROM users_tb";
$stmtVerificationCounts = $connection->prepare($queryVerificationCounts);
$stmtVerificationCounts->execute();
$verificationCounts = $stmtVerificationCounts->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<div class="sidebar">
    <button id="sidebar-toggle" class="btn btn-dark">
        <i class="fas fa-bars"></i>  <!-- Hamburger Icon -->
    </button>

    <!-- User Info Section -->
    <div class="profile-section">
        <div class="h2">Croods</div> <!-- Text logo (correct as per your intention) -->

        <?php if (isset($userInfo['profile_image']) && !empty($userInfo['profile_image'])): ?>
            <img src="uploads/<?= $userInfo['profile_image'] ?>" alt="Profile Image" class="profile-img">
        <?php else: ?>
            <!-- Use Font Awesome icon as a temporary profile image -->
            <i class="fas fa-user-circle fa-5x default-icon"></i>
        <?php endif; ?>

        <h5 class="full-name user-full-name"><?= htmlspecialchars($userInfo['first_name'] . ' ' . $userInfo['last_name']) ?></h5>
        <p class="email"><?= htmlspecialchars($userInfo['email']) ?></p>
    </div>

    <!-- Sidebar Links Section -->
    <div class="actions-section">
        <!-- Dashboard Link with Icon -->
        <a href="dash.php" class="nav-item">
        <i class="fas fa-chart-line"></i>
            <span class="text">Dashboard</span>
        </a>
        <!-- <div id="student-table-container"> -->
    <!-- The table content will be loaded here -->
</div>

        <!-- Students Link with Icon -->
        <a href="javascript:void(0);" class="nav-item" id="loadStudents">
    <i class="fas fa-users icon"></i>
    <span class="text">Students</span>
</a>
        <!-- Settings Link with Icon -->
        <a href="section-title" data-toggle="modal" data-target="#updateProfileModal" style="cursor: pointer;" class="nav-item">
        <i class="fas fa-wrench"></i>
            <span class="text">Settings</span>
        </a>
        <!-- Bottom Actions -->
        <div class="bottom-actions">
            <!-- <a href="logout.php" class="section-title nav-item">
                <i class="fas fa-sign-out-alt icon"></i> 
                <span class="text">Log Out</span>
            </a> -->
        </div>
    </div>
</div>
    <div class="content">
        <!-- Date and Logout positioned at the top-right -->
    <div class="top-right">
        <!-- Calendar Icon and Date -->
        <div class="date">
            <i class="fa-solid fa-calendar-day"></i> <?= $currentDate ?>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
    </div>

<div id="main-content">
        <h3>overview</h3>
        <!-- Overview Section -->
        <div class="row">
            <div class="col-md-4 col-sm-12 overview-card"> 
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title"></h5>
                        <div class="chart-container">
                            <canvas id="studentCreationChart"></canvas>
                        </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-12 overview-card">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title"></h5>
                <div class="chart-container">
                    <canvas id="courseChart"></canvas>
                </div>
            </div>
        </div>
    </div>
        <div class="col-md-4 col-sm-12 overview-card">
            <div class="card text-white bg-purple">
                <div class="card-body">
                    <h5 class="card-title"></h5>
                    <div class="chart-container">
                        <canvas id="verificationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Users Table Section -->
        <div class="table-container">
            <h5>Users Management</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Gender</th>
                            <th>Course</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr class="<?php echo $user['is_verified'] ? 'verified' : 'not-verified'; ?>">
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td>
                                <span class="full-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['gender']); ?></td>
                            <td><?php echo htmlspecialchars($user['course']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $user['is_verified'] ? 'verified' : 'not-verified'; ?>">
                                    <?php echo $user['is_verified'] ? 'Verified' : 'Not Verified'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="student-table-container" class="table-container d-none"></div>
    <!-- Modal for Updating Profile -->
    <div class="modal fade" id="updateProfileModal" tabindex="-1" role="dialog" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateProfileModalLabel">Update Profile</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="update_profile.php" method="POST">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($userInfo['first_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($userInfo['last_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($userInfo['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <a href="change_password.php">Change Password</a>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$('#sidebar-toggle').click(function() {
$('.sidebar').toggleClass('open');
});
$('#loadStudents').on('click', function() {
    // Add active class to highlight the selected menu item
    $('.nav-item').removeClass('active');
    $(this).addClass('active');
    
    // Hide main content and show student container
    $('#main-content').addClass('d-none');
    $('#student-table-container').removeClass('d-none');
    
    // Only load if container is empty
    if ($('#student-table-container').is(':empty')) {
        $.ajax({
            url: 'load_students.php',
            method: 'GET',
            success: function(response) {
                $('#student-table-container').html(`
    <div class="student-table-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4>Student Records</h4>
            <a href="index.php" class="btn btn-primary btn-sm">View All Data</a>
        </div>
        <div class="table-responsive">
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
                <tbody>${response}</tbody>
            </table>
        </div>
    </div>
`);

            },
            error: function() {
                alert('Error loading student data.');
            }
        });
    }
});

// Add this to your dashboard link to show main content again
$('a[href="dash.php"]').on('click', function(e) {
    e.preventDefault();
    $('#main-content').removeClass('d-none');
    $('#student-table-container').addClass('d-none');
    $('.nav-item').removeClass('active');
    $(this).addClass('active');
    window.history.pushState({}, '', 'dash.php');
});
        // Student creation over time chart
        const ctxCreation = document.getElementById('studentCreationChart').getContext('2d');
        const creationLabels = <?php echo json_encode(array_column($studentCreationData, 'year')); ?>;
        const creationCounts = <?php echo json_encode(array_column($studentCreationData, 'count')); ?>;

        const studentCreationChart = new Chart(ctxCreation, {
            type: 'line',
            data: {
                labels: creationLabels,
                datasets: [{
                    label: 'Number of Students Created',
                    data: creationCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: false,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: true,
                        text: 'Over all total students: <?php echo $totalStudents; ?>'
                    }
                },
                scales: {
                    y: {
                        display: false,
                        beginAtZero: true
                    },
                    x: {                         display: false,
                    }
                }
            }
        });

        // Course enrollment chart
        const ctxCourse = document.getElementById('courseChart').getContext('2d');
        const courseLabels = <?php echo json_encode(array_column($courseEnrollment, 'course')); ?>;
        const courseCounts = <?php echo json_encode(array_column($courseEnrollment, 'count')); ?>;

        const courseChart = new Chart(ctxCourse, {
            type: 'bar',
            data: {
                labels: courseLabels,
                datasets: [{
                    label: 'Number of Students',
                    data: courseCounts,
                    backgroundColor: '#4BC0C0',
                    borderColor: '#FFFFFF',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Course Enrollment'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: false,
                        }
                    },
                    x: {
                        ticks: {
                            display: false
                        },
                        title: {
                            display: false,
                        }
                    }
                }
            }
        });

        // Verification trends chart (modified to use a bar graph)
        const ctxVerification = document.getElementById('verificationChart').getContext('2d');
        const verificationYears = <?php echo json_encode(array_column($verificationTrends, 'year')); ?>;
        const verifiedCounts = <?php echo json_encode(array_column($verificationTrends, 'verified_count')); ?>;
        const unverifiedCounts = <?php echo json_encode(array_column($verificationTrends, 'unverified_count')); ?>;

        const verificationChart = new Chart(ctxVerification, {
            type: 'bar',
            data: {
                labels: verificationYears,
                datasets: [
                    {
                        label: 'Verified Users',
                        data: verifiedCounts,
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 2,
                    },
                    {
                        label: 'Unverified Users',
                        data: unverifiedCounts,
                        backgroundColor: 'rgba(220, 53, 69, 0.5)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: `Verified: <?php echo $verificationCounts['total_verified']; ?> | Unverified: <?php echo $verificationCounts['total_unverified']; ?>`
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            // text: 'Number of Users'
                        }
                    },
                    x: {
                        title: {
                            display: false
                            // text: 'Year'
                        }
                    }
                }
            }
        });
    </script>
    <script src="script.js"></script>
</body>
</html>
