<?php
/**
 * Déconnexion
 * SunuJob Étudiant
 */

session_start();
$_SESSION = array();
session_destroy();

header('Location: /login.php');
exit;
?>
