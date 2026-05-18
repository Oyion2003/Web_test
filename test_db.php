<?php

require_once "config/database.php";

try {

    $db = (new Database())->connect();

    echo "✅ Database Connected Successfully<br>";

    // TEST INSERT
    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash)
                          VALUES (?, ?, ?)");

    $result = $stmt->execute([
        "Test User",
        "test_" . rand(1000,9999) . "@mail.com",
        password_hash("12345678", PASSWORD_DEFAULT)
    ]);

    if ($result) {
        echo "✅ Insert Working: User added<br>";
    } else {
        echo "❌ Insert Failed";
    }

    // TEST SELECT
    $users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

    echo "👥 Total Users in DB: " . $users;

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}