<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<div class="dashboard-container">
    <h1>Bienvenue, <?= htmlspecialchars($_SESSION['user']['prenom']) ?> 👋</h1>
    <p>Vous êtes connecté en tant que <strong><?= htmlspecialchars($_SESSION['user']['login']) ?></strong>.</p>

    <div class="card-grid">
        <div class="card">
            <h2>Utilisateurs</h2>
            <p>Gérer les comptes utilisateurs</p>
            <a href="utilisateurs.php">Voir</a>
        </div>

        <div class="card">
            <h2>Comptes</h2>
            <p>Voir et gérer les comptes créés</p>
            <a href="comptes.php">Voir</a>
        </div>

        <div class="card">
            <h2>Rôles</h2>
            <p>Liste des rôles définis</p>
            <a href="roles.php">Voir</a>
        </div>
    </div>
    <br>
    <p>Téléchargez la documentation complète de l'application SGU au format PDF.</p>
    <a href="files/DOCUMENTATION PROJET PISCINE.pdf" style="text-decoration: none;">
    <button class="doc-btn">
    📄 Télécharger la documentation
    </button>
    </a>
    <br>
    <a href="logout.php" class="logout-btn">Se déconnecter</a>

    
</div>

<?php include 'includes/footer.php'; ?>
