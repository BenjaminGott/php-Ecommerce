<?php
function headerComponent() {
    return '
    <header style="padding: 10px; background: #f0f0f0;">
        <button onclick="location.href=\'/\'">Go Home</button>
        <button onclick="location.href=\'/add\'">Go Add</button>
        <button onclick="location.href=\'/profile\'">Go Profile</button>
    </header>';
}
