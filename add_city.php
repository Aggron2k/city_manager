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
    $name = $_POST['name'];

    $sql = "INSERT INTO cities (name) VALUES ('$name')";
    if ($conn->query($sql) === TRUE) {
        $city_id = $conn->insert_id;
        $sql = "INSERT INTO support (country_id, city_id) VALUES ($county_id, $city_id)";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success', 'id' => $city_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to link city with county.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add city.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing data.']);
}

$conn->close();
?>
