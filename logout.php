<?php
session_start();
session_unset(); // Supprime toutes les variables de session
session_destroy(); // Détruit la session

// Redirection vers la page de connexion
header("Location: index.php?logout=1");
exit;
?>