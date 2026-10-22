<?php

require_once "config/database.php";

$new_password = "kolkar@123";

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = '$hashed_password' WHERE id = 1";

if ($conn->query($sql) === TRUE) {
    echo "Password reset successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();

?>