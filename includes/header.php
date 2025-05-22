<?php
function headerComponent() {
    return '
    <header style="padding: 10px; background: #f0f0f0;">
        <button onclick="location.href=\'index.php\'">Go Home</button>
        <button onclick="location.href=\'add.php\'">Go Add</button>
        <button onclick="location.href=\'pagnier.php\'">Go Pagnier</button>
        <button onclick="location.href=\'profile.php\'">Go Profile</button>
    </header>';
}
