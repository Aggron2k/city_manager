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

    // Ellenőrizzük, hogy a város már létezik-e
    $check_city_sql = "SELECT id FROM cities WHERE name = '$name'";
    $check_city_result = $conn->query($check_city_sql);

    if ($check_city_result->num_rows > 0) {
        $city_data = $check_city_result->fetch_assoc();
        $city_id = $city_data['id'];

        // Ellenőrizzük, hogy van-e már aktív kapcsolat a város és bármelyik megye között
        $check_existing_active_sql = "SELECT id FROM support WHERE city_id = $city_id AND deleted = 0";
        $check_existing_active_result = $conn->query($check_existing_active_sql);

        if ($check_existing_active_result->num_rows > 0) {
            // Van már aktív kapcsolat, hiba
            echo json_encode(['status' => 'error', 'message' => 'A város már kapcsolva van egy másik megyéhez.']);
        } else {
            // Ellenőrizzük, hogy van-e már kapcsolat a város és a megye között, bármilyen állapotban
            $check_existing_sql = "SELECT id, deleted FROM support WHERE city_id = $city_id AND country_id = $county_id";
            $check_existing_result = $conn->query($check_existing_sql);

            if ($check_existing_result->num_rows > 0) {
                $existing_data = $check_existing_result->fetch_assoc();
                $support_id = $existing_data['id'];
                $deleted = $existing_data['deleted'];

                if ($deleted == 0) {
                    // A kapcsolat már aktív, hibát jelez
                    echo json_encode(['status' => 'error', 'message' => 'A város már kapcsolva van ehhez a megyéhez.']);
                } else {
                    // A kapcsolat törölt, visszaállítjuk
                    $update_support_sql = "UPDATE support SET deleted = 0 WHERE id = $support_id";
                    if ($conn->query($update_support_sql) === TRUE) {
                        echo json_encode(['status' => 'success', 'id' => $city_id]);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'A város és megye kapcsolatának visszaállítása sikertelen.']);
                    }
                }
            } else {
                // Nincs meglévő kapcsolat, hozzáadjuk az új kapcsolatot
                $insert_support_sql = "INSERT INTO support (country_id, city_id, deleted) VALUES ($county_id, $city_id, 0)";
                if ($conn->query($insert_support_sql) === TRUE) {
                    echo json_encode(['status' => 'success', 'id' => $city_id]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'A város összekapcsolása megyével sikertelen.']);
                }
            }
        }
    } else {
        // A város nem létezik, hozzáadjuk új városként és a kapcsolatot is
        $insert_city_sql = "INSERT INTO cities (name, deleted) VALUES ('$name', 0)";
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
