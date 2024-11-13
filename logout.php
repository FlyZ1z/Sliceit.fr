<?php
session_start();
session_destroy();  // Détruire toutes les sessions
header("Location: ../index.php");  // Redirection vers la page d'accueil
exit;
?>