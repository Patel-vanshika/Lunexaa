<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shopcart";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
        echo "All fields are required";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        exit();
    }

    if (strlen($password) < 6) {
        echo "Password must be at least 6 characters";
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Email already registered";
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $mobile, $hashed_password);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Registration failed: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method";
}

$conn->close();
?>
