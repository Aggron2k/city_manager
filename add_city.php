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

    $check_city_sql = "SELECT id FROM cities WHERE name = '$name'";
    $check_city_result = $conn->query($check_city_sql);

    if ($check_city_result->num_rows > 0) {
        $city_data = $check_city_result->fetch_assoc();
        $city_id = $city_data['id'];

        $check_existing_active_sql = "SELECT id FROM support WHERE city_id = $city_id AND deleted = 0";
        $check_existing_active_result = $conn->query($check_existing_active_sql);

        if ($check_existing_active_result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'A város már kapcsolva van egy megyéhez.']);
        } else {
            $check_existing_sql = "SELECT id, deleted FROM support WHERE city_id = $city_id AND country_id = $county_id";
            $check_existing_result = $conn->query($check_existing_sql);

            if ($check_existing_result->num_rows > 0) {
                $existing_data = $check_existing_result->fetch_assoc();
                $support_id = $existing_data['id'];
                $deleted = $existing_data['deleted'];

                if ($deleted == 0) {
                    echo json_encode(['status' => 'error', 'message' => 'A város már kapcsolva van ehhez a megyéhez.']);
                } else {
                    $update_support_sql = "UPDATE support SET deleted = 0 WHERE id = $support_id";
                    if ($conn->query($update_support_sql) === TRUE) {
                        echo json_encode(['status' => 'success', 'id' => $city_id]);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'A város és megye kapcsolatának visszaállítása sikertelen.']);
                    }
                }
            } else {
                $insert_support_sql = "INSERT INTO support (country_id, city_id, deleted) VALUES ($county_id, $city_id, 0)";
                if ($conn->query($insert_support_sql) === TRUE) {
                    echo json_encode(['status' => 'success', 'id' => $city_id]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'A város összekapcsolása megyével sikertelen.']);
                }
            }
        }
    } else {
        $insert_city_sql = "INSERT INTO cities (name) VALUES ('$name')";
        if ($conn->query($insert_city_sql) === TRUE) {
            $city_id = $conn->insert_id;
            $insert_support_sql = "INSERT INTO support (country_id, city_id, deleted) VALUES ($county_id, $city_id, 0)";
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
