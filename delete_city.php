<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hungary_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $conn->begin_transaction();

    try {
        $sql_support = "DELETE FROM support WHERE city_id = ?";
        $stmt_support = $conn->prepare($sql_support);
        $stmt_support->bind_param("i", $id);
        $stmt_support->execute();

        $sql_city = "DELETE FROM cities WHERE id = ?";
        $stmt_city = $conn->prepare($sql_city);
        $stmt_city->bind_param("i", $id);
        $stmt_city->execute();

        $conn->commit();

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'A város törlése sikertelen.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Érvénytelen vagy hiányzó adatok.']);
}

$conn->close();
?>
