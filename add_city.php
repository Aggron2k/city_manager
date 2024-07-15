<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hungary_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['county_id']) && isset($_POST['name']) && !empty($_POST['name'])) {
    $county_id = $_POST['county_id'];
    $name = $conn->real_escape_string($_POST['name']);

    $check_city_sql = "SELECT id, deleted FROM cities WHERE name = '$name'";
    $check_city_result = $conn->query($check_city_sql);

    if ($check_city_result->num_rows > 0) {
        $city_data = $check_city_result->fetch_assoc();
        $city_id = $city_data['id'];
        $deleted = $city_data['deleted'];

        if ($deleted == 0) {
            $check_existing_sql = "SELECT id FROM support WHERE city_id = $city_id AND country_id != $county_id AND deleted = 0";
            $check_existing_result = $conn->query($check_existing_sql);

            if ($check_existing_result->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'A város már kapcsolva van egy másik megyéhez.']);
            } else {
                $insert_support_sql = "INSERT INTO support (country_id, city_id) VALUES ($county_id, $city_id)";
                if ($conn->query($insert_support_sql) === TRUE) {
                    echo json_encode(['status' => 'success', 'id' => $city_id]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'A város összekapcsolása megyével sikertelen.']);
                }
            }
        } else {
            $update_city_sql = "UPDATE cities SET deleted = 0 WHERE id = $city_id";
            if ($conn->query($update_city_sql) === TRUE) {
                $insert_support_sql = "INSERT INTO support (country_id, city_id) VALUES ($county_id, $city_id)";
                if ($conn->query($insert_support_sql) === TRUE) {
                    echo json_encode(['status' => 'success', 'id' => $city_id]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'A város összekapcsolása megyével sikertelen.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'A város visszaállítása sikertelen.']);
            }
        }
    } else {
        $insert_city_sql = "INSERT INTO cities (name, deleted) VALUES ('$name', 0)";
        if ($conn->query($insert_city_sql) === TRUE) {
            $city_id = $conn->insert_id;
            $insert_support_sql = "INSERT INTO support (country_id, city_id) VALUES ($county_id, $city_id)";
            if ($conn->query($insert_support_sql) === TRUE) {
                echo json_encode(['status' => 'success', 'id' => $city_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'A város összekapcsolása megyével sikertelen.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'A város hozzáadása sikertelen.']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Érvénytelen vagy hiányzó adatok.']);
}

$conn->close();
?>