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
                <option value="none">Válasszon</option>
                <?php while ($row = $counties_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div id="cities-container" class="mt-4">
            <!-- Dinamikus tartalom -->
            <div class="alert alert-danger" role="alert">Nincs kiválasztva megye.</div>
        </div>


        <form id="add-city-form">
            <!-- Dinamikus tartalom -->
        </form>
    </div>
</body>
<script>
    $(document).ready(function () {
        $('#county-select').change(function () {
            var countyId = $(this).val();
            if (countyId) {
                $.ajax({
                    url: 'cities.php',
                    type: 'post',
                    data: { county_id: countyId },
                    success: function (response) {
                        $('#cities-container').html(response);
                        $('#add-city-form').html('<h4 id="add-city-h4">Új város hozzáadása</h4><input type="text" id="new-city-name" class="form-control mb-2" placeholder="Város neve"><button type="submit" class="btn btn-primary">Hozzáadás</button>');
                    }
                });
            } else {
                $('#cities-container').html('');
                $('#add-city-form').html('');
            }
        });

        $(document).on('click', '.city-name', function () {
            var cityId = $(this).closest('tr').attr('id').split('-')[1];
            var cityName = $(this).text();
            var cityActions = `
                <td><input type="text" class="form-control city-name-input" value="${cityName}"></td>
                <td>
                    <button class="save-city btn btn-sm btn-primary" data-id="${cityId}">Módosítás</button>
                    <button class="delete-city btn btn-sm btn-danger ms-2" data-id="${cityId}">Törlés</button>
                    <button class="cancel-edit btn btn-sm btn-secondary ms-2" data-id="${cityId}">Mégsem</button>
                </td>`;
            $(this).closest('tr').html(cityActions);
        });

        $(document).on('click', '.save-city', function () {
            var cityId = $(this).data('id');
            var newCityName = $(this).closest('tr').find('.city-name-input').val();
            $.ajax({
                url: 'edit_city.php',
                type: 'POST',
                data: { id: cityId, name: newCityName },
                success: function (response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        var updatedRow = `
                            <tr id="city-${cityId}">
                                <td><span class="city-name">${newCityName}</span></td>
                                <td></td>
                            </tr>`;
                        $('#city-' + cityId).replaceWith(updatedRow);
                    } else {
                        alert('Módosítás sikertelen: ' + res.message);
                    }
                }
            });
        });

        $(document).on('click', '.delete-city', function () {
            var cityId = $(this).data('id');
            $.ajax({
                url: 'delete_city.php',
                type: 'POST',
                data: { id: cityId },
                success: function (response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        $('#city-' + cityId).remove();
                    } else {
                        alert('Törlés sikertelen: ' + res.message);
                    }
                }
            });
        });

        $(document).on('click', '.cancel-edit', function () {
            var cityId = $(this).data('id');
            $.ajax({
                url: 'cities.php',
                type: 'POST',
                data: { county_id: $('#county-select').val() },
                success: function (response) {
                    $('#cities-container').html(response);
                }
            });
        });
    });

    $(document).on('submit', '#add-city-form', function (e) {
        e.preventDefault();
        var countyId = $('#county-select').val();
        var cityName = $('#new-city-name').val();
        $.ajax({
            url: 'add_city.php',
            type: 'POST',
            data: { county_id: countyId, name: cityName },
            success: function (response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    var newCityRow = `
                        <tr id="city-${res.id}">
                            <td><span class="city-name">${cityName}</span></td>
                            <td></td>
                        </tr>`;
                    if ($('#cities-table').length == 0) {
                        var citiesTable = `
                            <table id="cities-table" class="table table-striped">
                                <thead><tr><th>Város név</th><th>Műveletek</th></tr></thead>
                                <tbody></tbody>
                            </table>`;
                        $('#cities-container').html(citiesTable);
                    }
                    $('#cities-table tbody').append(newCityRow);
                    $('#new-city-name').val('');
                } else {
                    alert('Hozzáadás sikertelen: ' + res.message);
                }
            }
        });
    });
</script>

</html>