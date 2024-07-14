<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hungary_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['id']) && isset($_POST['name']) && !empty($_POST['name'])) {
    $id = $_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);

    $check_city_sql = "SELECT id FROM cities WHERE name = '$name'";
    $check_city_result = $conn->query($check_city_sql);

    if ($check_city_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'A város már létezik.']);
    } else {
        $sql = "UPDATE cities SET name = '$name' WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'A város módosítása sikertelen.']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Érvénytelen vagy hiányzó adatok.']);
}

$conn->close();
?>