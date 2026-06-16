<?php
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $campus = $_POST['campus'];
    $nature = $_POST['nature'];
    $message = $_POST['message'];

    $sql = "INSERT INTO feedback (name, email, phone, campus, nature, message)
            VALUES (:name, :email, :phone, :campus, :nature, :message)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'campus' => $campus,
        'nature' => $nature,
        'message' => $message
    ]);

    header("Location: thank-you.php");
    exit();
}
?>