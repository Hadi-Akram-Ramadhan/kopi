<?php
$password = 'manajer2';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash;
?>