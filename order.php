<?php
session_start();

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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and validate form data
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $payment_method = trim($_POST['payment_method']);
    $cart_items_json = trim($_POST['cart_items']);

    // Validate required fields
    if (empty($name) || empty($email) || empty($mobile) || empty($address) || empty($payment_method) || empty($cart_items_json)) {
        die("All fields are required");
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    // Validate mobile (basic check for digits)
    if (!preg_match('/^[0-9]{10,15}$/', $mobile)) {
        die("Invalid mobile number");
    }

    // Decode cart items
    $cart_items = json_decode($cart_items_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($cart_items)) {
        die("Invalid cart data");
    }

    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        if (!isset($item['name'], $item['price'], $item['quantity'])) {
            die("Invalid item data");
        }
        $total += $item['price'] * $item['quantity'];
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, mobile, address, total, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssds", $user_id, $name, $email, $mobile, $address, $total, $payment_method);

        if (!$stmt->execute()) {
            throw new Exception("Error placing order: " . $stmt->error);
        }

        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insert order items
        $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
        if (!$stmt_items) {
            error_log("Prepare failed for order_items: " . $conn->error);
            die("Database error: Unable to prepare statement for order items. Error: " . $conn->error);
        }
        foreach ($cart_items as $item) {
            if (!isset($item['name'], $item['price'], $item['quantity'])) {
                die("Invalid item data");
            }
            // Cast price to float and quantity to int explicitly
            $price = floatval($item['price']);
            $quantity = intval($item['quantity']);
            $stmt_items->bind_param("isdi", $order_id, $item['name'], $price, $quantity);
            if (!$stmt_items->execute()) {
                error_log("Error inserting order item: " . $stmt_items->error);
                die("Database error: Unable to insert order item. Error: " . $stmt_items->error);
            }
        }
        $stmt_items->close();

        // Commit transaction
        $conn->commit();

        // Clear cart (optional, can be done on client side)
        // Redirect to thank you page
        header("Location: thankyou.html");
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        die($e->getMessage());
    }
} else {
    die("Invalid request method");
}

$conn->close();
?>
