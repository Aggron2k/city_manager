<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hungary_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$response = [];

try {
    if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['country_id']) && !empty($_POST['name'])) {
        $id = $_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $country_id = $_POST['country_id'];

        // Ellenőrizze, hogy van-e már ilyen nevű város az adott megyében
        $check_city_sql = "SELECT s.id, s.city_id, s.deleted FROM support s
                           INNER JOIN cities c ON s.city_id = c.id
                           WHERE c.name = '$name' AND s.country_id = $country_id AND s.city_id <> $id";
        $check_city_result = $conn->query($check_city_sql);

        if ($check_city_result->num_rows > 0) {
            $row = $check_city_result->fetch_assoc();
            $existing_city_id = $row['city_id'];
            $deleted = $row['deleted'];

            if ($deleted == 0) {
                $response = ['status' => 'error', 'message' => 'A város már szerepel a megyében.'];
            } else {
                $conn->begin_transaction();
                try {
                    $restore_city_sql = "UPDATE support SET deleted = 0 WHERE city_id = $existing_city_id AND country_id = $country_id";
                    $conn->query($restore_city_sql);

                    $delete_new_city_sql = "UPDATE support SET deleted = 1 WHERE city_id = $id AND country_id = $country_id";
                    $conn->query($delete_new_city_sql);

                    $update_city_sql = "UPDATE cities SET name = '$name' WHERE id = $existing_city_id";
                    $conn->query($update_city_sql);

                    $conn->commit();
                    $response = ['status' => 'success'];
                } catch (Exception $e) {
                    $conn->rollback();
                    error_log("Transaction failed: " . $e->getMessage());
                    $response = ['status' => 'error', 'message' => 'A város módosítása sikertelen: ' . $e->getMessage()];
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
                $response = ['status' => 'success'];
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Transaction failed: " . $e->getMessage());
                $response = ['status' => 'error', 'message' => 'A város módosítása sikertelen: ' . $e->getMessage()];
            }
        }
    } else {
        $missingData = [];
        if (!isset($_POST['id'])) $missingData[] = 'id';
        if (!isset($_POST['name'])) $missingData[] = 'name';
        if (!isset($_POST['country_id'])) $missingData[] = 'country_id';
        if (empty($_POST['name'])) $missingData[] = 'empty name';
    
        $response = ['status' => 'error', 'message' => 'Érvénytelen vagy hiányzó adatok: ' . implode(', ', $missingData)];
    }
} catch (Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => 'Váratlan hiba történt: ' . $e->getMessage()];
}

echo json_encode($response);
$conn->close();
?>
