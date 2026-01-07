<?php
$password = '12345';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "New Password Hash for '12345':\n";
echo $hash;
?>