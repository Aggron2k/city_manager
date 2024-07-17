<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hungary_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['country_id']) && !empty($_POST['name'])) {
    $id = $_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $country_id = $_POST['country_id'];

    $check_city_sql = "SELECT s.id, s.city_id, s.deleted FROM support s
                       INNER JOIN cities c ON s.city_id = c.id
                       WHERE c.name = '$name' AND s.country_id = $country_id AND s.city_id <> $id";
    $check_city_result = $conn->query($check_city_sql);

    if ($check_city_result->num_rows > 0) {
        $row = $check_city_result->fetch_assoc();
        $existing_city_id = $row['city_id'];
        $deleted = $row['deleted'];

        if ($deleted == 0) {
            echo json_encode(['status' => 'error', 'message' => 'A város már szerepel a megyében.']);
        } else {
            $conn->begin_transaction();
            try {
                $restore_city_sql = "UPDATE support SET deleted = 0 WHERE city_id = $existing_city_id AND country_id = $country_id";
                $delete_new_city_sql = "UPDATE support SET deleted = 1 WHERE city_id = $id AND country_id = $country_id";
                $conn->query($restore_city_sql);
                $conn->query($delete_new_city_sql);
                $conn->commit();
                echo json_encode(['status' => 'success']);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'A város módosítása sikertelen.']);
            }
        }
    } else {
        $conn->begin_transaction();
        try {
            $update_city_sql = "UPDATE cities SET name = '$name' WHERE id = $id";
            $conn->query($update_city_sql);

            $update_support_sql = "UPDATE support SET city_id = $id WHERE city_id = $id AND country_id = $country_id";
            $conn->query($update_support_sql);

            $conn->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'A város módosítása sikertelen.']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Érvénytelen vagy hiányzó adatok.']);
}

$conn->close();
?>
