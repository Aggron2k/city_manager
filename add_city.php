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
        echo json_encode(['status' => 'error', 'message' => 'A város már létezik.']);
    } else {
        $sql = "INSERT INTO cities (name) VALUES ('$name')";
        if ($conn->query($sql) === TRUE) {
            $city_id = $conn->insert_id;
            $sql = "INSERT INTO support (country_id, city_id) VALUES ($county_id, $city_id)";
            if ($conn->query($sql) === TRUE) {
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