<?php
include_once '../includes/db_connect.php';

$result = $mysqli->query("SELECT * FROM User");

while ($row = $result->fetch_assoc()) {
    echo "<p>" . htmlspecialchars($row['nom']) . "</p>";
}
?>
