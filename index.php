<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="refresh" content="5">
    <title>ENVIRONMENT AIR QUALITY MONITORING</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 1.8em;
            text-align: center;
        }

        .latest-data {
            width: 100%;
            max-width: 800px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .latest-data div {
            margin: 10px 0;
        }

        table {
            width: 100%;
            max-width: 800px;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        table th,
        table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 1em;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            color: #333;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 2px;
        }

        .pagination a.active {
            background-color: #333;
            color: #fff;
        }

        .pagination a:hover {
            background-color: #ddd;
        }

        .chart-container {
            width: 100%;
            max-width: 800px;
            margin: 20px 0;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            border-radius: 5px;
            padding: 15px;
        }

        .chart-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }

        canvas {
            background-color: #fff;
            border: 1px solid #ddd;
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 1.5em;
            }

            table th,
            table td {
                padding: 8px;
                font-size: 0.9em;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <h1>ENVIRONMENT AIR QUALITY MONITORING</h1>

    <div class="latest-data">
        <div><i class="fas fa-thermometer-half"></i><strong> Latest Temperature:</strong> <span id="latestTemp">-</span>
            °C</div>
        <div><i class="fas fa-tint"></i><strong> Latest Humidity:</strong> <span id="latestHumidity">-</span> %</div>
        <div><i class="fas fa-smog"></i><strong> Latest Gas Value:</strong> <span id="latestGas">-</span> PPM</div>
        <div><i class="fas fa-wind"></i><strong> Air Quality:</strong> <span id="airQuality">-</span></div>
    </div>


    <table>
        <tr>
            <th></th>
            <th>Temperature (°C)</th>
            <th>Humidity (%)</th>
            <th>Gas Value (PPM)</th>
        </tr>
        <tr>
            <td><strong>Average:</strong></td>
            <td><span id="avgTemp">-</span></td>
            <td><span id="avgHumidity">-</span></td>
            <td><span id="avgGas">-</span></td>
        </tr>
        <tr>
            <td><strong>Max:</strong></td>
            <td><span id="maxTemp">-</span></td>
            <td><span id="maxHumidity">-</span></td>
            <td><span id="maxGas">-</span></td>
        </tr>
        <tr>
            <td><strong>Min:</strong></td>
            <td><span id="minTemp">-</span></td>
            <td><span id="minHumidity">-</span></td>
            <td><span id="minGas">-</span></td>
        </tr>
    </table>

    <table>
        <tr>
            <th>Reading ID</th>
            <th>Temperature (°C)</th>
            <th>Humidity (%)</th>
            <th>Gas Value (PPM)</th>
            <th>Air Quality</th>
            <th>Timestamp</th>
        </tr>
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "environment_monitoring";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch the latest data
        $latestDataSql = "SELECT temperature, humidity, gas, quality FROM sensor_data ORDER BY id DESC LIMIT 1";
        $latestResult = $conn->query($latestDataSql);
        $latestData = $latestResult->fetch_assoc();

        // Pagination
        $limit = 10; // Number of entries to show in a page.
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
        $start_from = ($page - 1) * $limit;

        $sql = "SELECT id, datetime, temperature, humidity, gas, quality FROM sensor_data ORDER BY id DESC LIMIT $start_from, $limit";
        $result = $conn->query($sql);

        $timestamps = [];
        $temperatures = [];
        $humidities = [];
        $gasValues = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["id"] . "</td><td>" . $row["temperature"] . "</td><td>" . $row["humidity"] . "</td><td>" . $row["gas"] . "</td><td>" . $row["quality"] . "</td><td>" . $row["datetime"] . "</td></tr>";

                $timestamps[] = $row["datetime"];
                $temperatures[] = $row["temperature"];
                $humidities[] = $row["humidity"];
                $gasValues[] = $row["gas"];
            }

            // Calculate average, max, min temperature, humidity, and gas value
            $temp_avg = $conn->query("SELECT AVG(temperature) AS avg_temp FROM sensor_data")->fetch_assoc()['avg_temp'];
            $temp_max = $conn->query("SELECT MAX(temperature) AS max_temp FROM sensor_data")->fetch_assoc()['max_temp'];
            $temp_min = $conn->query("SELECT MIN(temperature) AS min_temp FROM sensor_data")->fetch_assoc()['min_temp'];

            $hum_avg = $conn->query("SELECT AVG(humidity) AS avg_humidity FROM sensor_data")->fetch_assoc()['avg_humidity'];
            $hum_max = $conn->query("SELECT MAX(humidity) AS max_humidity FROM sensor_data")->fetch_assoc()['max_humidity'];
            $hum_min = $conn->query("SELECT MIN(humidity) AS min_humidity FROM sensor_data")->fetch_assoc()['min_humidity'];

            $gas_avg = $conn->query("SELECT AVG(gas) AS avg_gas FROM sensor_data")->fetch_assoc()['avg_gas'];
            $gas_max = $conn->query("SELECT MAX(gas) AS max_gas FROM sensor_data")->fetch_assoc()['max_gas'];
            $gas_min = $conn->query("SELECT MIN(gas) AS min_gas FROM sensor_data")->fetch_assoc()['min_gas'];

        } else {
            echo "<tr><td colspan='6'>No data found</td></tr>";
            $temp_avg = "-";
            $temp_max = "-";
            $temp_min = "-";
            $hum_avg = "-";
            $hum_max = "-";
            $hum_min = "-";
            $gas_avg = "-";
            $gas_max = "-";
            $gas_min = "-";
        }

        // Pagination links
        $sql = "SELECT COUNT(id) FROM sensor_data";
        $result = $conn->query($sql);
        $row = $result->fetch_row();
        $total_records = $row[0];
        $total_pages = ceil($total_records / $limit);

        echo '</table>'; // Close the table before the pagination
        
        echo '<div class="pagination">';
        if ($page > 1) {
            echo '<a href="?page=' . ($page - 1) . '" class="active">&laquo; Previous</a>';
        }

        // Display fewer page links (e.g., show up to 5 pages)
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);

        for ($i = $start_page; $i <= $end_page; $i++) {
            echo '<a href="?page=' . $i . '"';
            if ($i == $page) {
                echo ' class="active"';
            }
            echo '>' . $i . '</a>';
        }

        if ($page < $total_pages) {
            echo '<a href="?page=' . ($page + 1) . '" class="active">Next &raquo;</a>';
        }
        echo '</div>';

        $conn->close();
        ?>

        <div class="chart-container">
            <div class="chart-title">Temperature Changes Over Time</div>
            <canvas id="temperatureChart"></canvas>
        </div>
        <div class="chart-container">
            <div class="chart-title">Humidity Changes Over Time</div>
            <canvas id="humidityChart"></canvas>
        </div>
        <div class="chart-container">
            <div class="chart-title">Gas Value Changes Over Time</div>
            <canvas id="gasChart"></canvas>
        </div>

        <script>
            const labels = <?php echo json_encode($timestamps); ?>;
            const temperatureData = <?php echo json_encode($temperatures); ?>;
            const humidityData = <?php echo json_encode($humidities); ?>;
            const gasData = <?php echo json_encode($gasValues); ?>;

            const temperatureConfig = {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Temperature (°C)',
                        data: temperatureData,
                        borderColor: 'red',
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'DateTime'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Temperature (°C)'
                            }
                        }
                    }
                }
            };

            const humidityConfig = {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Humidity (%)',
                        data: humidityData,
                        borderColor: 'blue',
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'DateTime'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Humidity (%)'
                            }
                        }
                    }
                }
            };

            const gasConfig = {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Gas Value',
                        data: gasData,
                        borderColor: 'green',
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'DateTime'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Gas Value'
                            }
                        }
                    }
                }
            };

            const temperatureChart = new Chart(
                document.getElementById('temperatureChart'),
                temperatureConfig
            );

            const humidityChart = new Chart(
                document.getElementById('humidityChart'),
                humidityConfig
            );

            const gasChart = new Chart(
                document.getElementById('gasChart'),
                gasConfig
            );

            // Update stats in the DOM
            document.getElementById('avgTemp').innerText = "<?php echo number_format($temp_avg, 2); ?>";
            document.getElementById('maxTemp').innerText = "<?php echo number_format($temp_max, 2); ?>";
            document.getElementById('minTemp').innerText = "<?php echo number_format($temp_min, 2); ?>";
            document.getElementById('avgHumidity').innerText = "<?php echo number_format($hum_avg, 2); ?>";
            document.getElementById('maxHumidity').innerText = "<?php echo number_format($hum_max, 2); ?>";
            document.getElementById('minHumidity').innerText = "<?php echo number_format($hum_min, 2); ?>";
            document.getElementById('avgGas').innerText = "<?php echo number_format($gas_avg, 2); ?>";
            document.getElementById('maxGas').innerText = "<?php echo number_format($gas_max, 2); ?>";
            document.getElementById('minGas').innerText = "<?php echo number_format($gas_min, 2); ?>";

            // Update the latest data in the DOM
            document.getElementById('latestTemp').innerText = "<?php echo $latestData['temperature']; ?>";
            document.getElementById('latestHumidity').innerText = "<?php echo $latestData['humidity']; ?>";
            document.getElementById('latestGas').innerText = "<?php echo $latestData['gas']; ?>";
            document.getElementById('airQuality').innerText = "<?php echo $latestData['quality']; ?>";
        </script>
</body>

</html>