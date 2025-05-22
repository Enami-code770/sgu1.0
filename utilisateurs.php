<?php
require_once 'connexion.php';

try {
    $query = $conn->query("SELECT * FROM utilisateur");
    $utilisateurs = $query->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($utilisateurs);
    echo "</pre>";
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
}
?>
