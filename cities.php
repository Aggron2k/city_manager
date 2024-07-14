<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hungary_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['county_id']) && !empty($_POST['county_id']) && $_POST['county_id'] != "none") {
    $county_id = $_POST['county_id'];
    $cities_result = $conn->query("SELECT c.id, c.name FROM cities c JOIN support s ON c.id = s.city_id WHERE s.country_id = $county_id");

    if ($cities_result->num_rows > 0) {
        echo '<table id="cities-table" class="table table-striped">';
        echo '<thead><tr><th>Város név</th><th>Műveletek</th></tr></thead>';
        echo '<tbody>';
        while ($row = $cities_result->fetch_assoc()) {
            echo '<tr id="city-' . $row['id'] . '">';
            echo '<td><span class="city-name">' . $row['name'] . '</span></td>';
            echo '<td>';
            // echo '<button class="edit-city btn btn-sm btn-primary" data-id="' . $row['id'] . '">Módosítás</button>';
            // echo '<button class="delete-city btn btn-sm btn-danger ms-2" data-id="' . $row['id'] . '">Törlés</button>';
            // echo '<button class="cancel-edit btn btn-sm btn-secondary ms-2">Mégsem</button>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '<script type="text/javascript">document.getElementById("add-city-form").style.display = "block";</script>';
    } else {
        echo '<div class="alert alert-warning" role="alert">Nincs város a kiválasztott megyében.</div>';
        echo '<script type="text/javascript">document.getElementById("add-city-form").style.display = "block";</script>';
    }
} else if (isset($_POST['county_id']) && $_POST['county_id'] == "none") {

    echo '<div class="alert alert-danger" role="alert">Nincs kiválasztva megye.</div>';
    echo '<script type="text/javascript">document.getElementById("add-city-form").style.display = "none";</script>';

} else {
    echo '<div class="alert alert-danger" role="alert">Hiba történt.</div>';
}

$conn->close();
?>