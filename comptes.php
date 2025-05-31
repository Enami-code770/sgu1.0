<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

// Messages
$message = "";

// Ajout d’un compte
if (isset($_POST['ajouter'])) {
    $numero = $_POST['numero'];
    $date = $_POST['date_creation'];
    $id_user = $_POST['id_user'];

    $stmt = $conn->prepare("INSERT INTO compte (numero_compte, date_de_creation, id_user) VALUES (?, ?, ?)");
    if ($stmt->execute([$numero, $date, $id_user])) {
        $message = "✅ Compte ajouté avec succès.";
    } else {
        $message = "❌ Erreur lors de l’ajout du compte.";
    }
}

// Suppression d’un compte
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $conn->prepare("DELETE FROM compte WHERE numero_compte = ?");
    if ($stmt->execute([$id])) {
        $message = "🗑️ Compte supprimé avec succès.";
    } else {
        $message = "❌ Erreur lors de la suppression.";
    }
}

// Récupération des comptes
$comptes = $conn->query("SELECT compte.*, utilisateur.nom, utilisateur.prenom FROM compte
                         JOIN utilisateur ON compte.id_user = utilisateur.id_user")
    ->fetchAll(PDO::FETCH_ASSOC);

// Récupération des utilisateurs pour la sélection
$utilisateurs = $conn->query("SELECT id_user, nom, prenom FROM utilisateur")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>

<h2>Gestion des comptes</h2>

<?php if ($message): ?>
    <p style="color:green;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>Gestion des comptes</title>
</head>
<form method="POST" class="form-ajout">
    <input type="text" name="numero" placeholder="Numéro de compte" required>
    <input type="date" name="date_creation" required>
    <select name="id_user" required>
        <option value="">-- Sélectionner un utilisateur --</option>
        <?php foreach ($utilisateurs as $user): ?>
            <option value="<?= $user['id_user'] ?>">
                <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="ajouter">Ajouter</button>
</form>

<table>
    <thead>
        <tr>
            <th>Numéro</th>
            <th>Date de création</th>
            <th>Utilisateur</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($comptes as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['numero_compte']) ?></td>
                <td><?= htmlspecialchars($c['date_de_creation']) ?></td>
                <td><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></td>
                <td>
                    <a href="comptes.php?supprimer=<?= $c['numero_compte'] ?>"
                        onclick="return confirm('Supprimer ce compte ?')"><i class="bi bi-trash" style="color: red;">
                            Supprimer</i></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>

<style>
    /* Formulaire d'ajout de comptes */

    .form-ajout {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        padding: 20px;
        background: #786b6b;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);

    }

    .form-ajout input[type="text"],
    .form-ajout input[type="date"],
    .form-ajout select {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 300px;
        box-sizing: border-box;
    }

    .form-ajout button {
        padding: 10px;
        background-color: #688fe9;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 16px;
        width: 20%;
        margin-top: 20px;
    }

    .form-ajout button:hover {
        background-color: #0056b3;
    }

    /* Messages de feedback */

    .message {
        margin: 20px 0;
        padding: 10px;
        border-radius: 5px;
        text-align: center;
        font-size: 14px;
    }

    .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
