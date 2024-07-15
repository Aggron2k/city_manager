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
        // Get the country_ids associated with the city
        $sql_get_countries = "SELECT DISTINCT country_id FROM support WHERE city_id = ?";
        $stmt_get_countries = $conn->prepare($sql_get_countries);
        $stmt_get_countries->bind_param("i", $id);
        $stmt_get_countries->execute();
        $result_countries = $stmt_get_countries->get_result();
        $country_ids = [];
        while ($row_countries = $result_countries->fetch_assoc()) {
            $country_ids[] = $row_countries['country_id'];
        }

        // Delete from support table
        $sql_support = "DELETE FROM support WHERE city_id = ?";
        $stmt_support = $conn->prepare($sql_support);
        $stmt_support->bind_param("i", $id);
        $stmt_support->execute();

        // Delete from cities table
        $sql_city = "DELETE FROM cities WHERE id = ?";
        $stmt_city = $conn->prepare($sql_city);
        $stmt_city->bind_param("i", $id);
        $stmt_city->execute();

        $conn->commit();

        $no_cities = false;
        foreach ($country_ids as $country_id) {
            // Check if there are any cities left in the country
            $sql_check = "SELECT COUNT(*) as city_count FROM support WHERE country_id = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("i", $country_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $row_check = $result_check->fetch_assoc();
            
            if ($row_check['city_count'] == 0) {
                $no_cities = true;
                break;
            }
        }

        if ($no_cities) {
            echo json_encode(['status' => 'success', 'no_cities' => true]);
        } else {
            echo json_encode(['status' => 'success', 'no_cities' => false]);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'A város törlése sikertelen.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Érvénytelen vagy hiányzó adatok.']);
}

$conn->close();
?>
