a<?php
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

// Create orders table
$sql_orders = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    address TEXT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql_orders) === TRUE) {
    echo "Orders table created successfully<br>";
} else {
    echo "Error creating orders table: " . $conn->error . "<br>";
}

// Create order_items table
$sql_order_items = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";

if ($conn->query($sql_order_items) === TRUE) {
    echo "Order items table created successfully<br>";
} else {
    echo "Error creating order items table: " . $conn->error . "<br>";
}

$conn->close();
echo "Database setup complete.";
?>
