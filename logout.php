<?php
session_start();
session_destroy();

// Redirect to login.html after logout
header("Location: login.html");
exit();

