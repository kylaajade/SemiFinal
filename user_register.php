<?php
require 'vendor/autoload.php'; // Load PHPMailer
require 'dbconnection.php'; // Include your database connection file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input from the form
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $user_address = $_POST['user_address'];
    $birthdate = $_POST['birthdate'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $verification_code = bin2hex(random_bytes(16)); // Generate a random verification code

    // Insert user into the database
    $stmt = $connection->prepare("INSERT INTO users_tb (first_name, last_name, user_address, birthdate, email, user_password, verification_code) VALUES (:first_name, :last_name, :user_address, :birthdate, :email, :user_password, :verification_code)");
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':user_address', $user_address);
    $stmt->bindParam(':birthdate', $birthdate);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':user_password', $password);
    $stmt->bindParam(':verification_code', $verification_code);

    if ($stmt->execute()) {
        // Send verification email
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'kylajadeagua@gmail.com'; // SMTP username
            $mail->Password = 'dcla bbqo advo xmhj'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('kylajadeagua@gmail.com', 'Mailer');
            $mail->addAddress($email); // Add a recipient

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Account Verification';
            $mail->Body    = 'Click the link to verify your account: <a href="http://localhost/Agua_semiFinal/verify.php?code=' . $verification_code . '">Verify Account</a>';

            $mail->send();
            echo 'Registration successful! Please check your email to verify your account.';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: Could not register user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>User Registration</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .text-center {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2 class="text-center">Create an Account</h2>
    <form method="post" action="user_register.php">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>
        <div class="form-group">
            <label for="user_address">Address</label>
            <input type="text" class="form-control" id="user_address" name="user_address" required>
        </div>
        <div class="form-group">
            <label for="birthdate">Birthdate</label>
            <input type="date" class="form-control" id="birthdate" name="birthdate" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>
    <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>