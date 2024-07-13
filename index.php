<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hungary_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$counties_result = $conn->query("SELECT * FROM country");

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Város nyilvántartó</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</head>

<body>
    <div class="container mt-5">
        <h1>Város nyilvántartó</h1>
        <div class="form-group">
            <label for="county-select">Válasszon megyét:</label>
            <select id="county-select" class="form-select">
                <option value="">Válasszon</option>
                <?php while ($row = $counties_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div id="cities-container" class="mt-4"></div>
    </div>
</body>

</html>