<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

$message = '';

// Ajout d’un rôle
if (isset($_POST['ajouter'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO role (nom, description) VALUES (?, ?)");
    $stmt->execute([$nom, $description]);

    $message = "✅ Rôle ajouté avec succès.";
}

// Suppression d’un rôle
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $conn->prepare("DELETE FROM role WHERE id_role = ?");
    $stmt->execute([$id]);

    $message = "🗑️ Rôle supprimé avec succès.";
}

// Récupérer les rôles
$roles = $conn->query("SELECT * FROM role")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>

<h2>Gestion des rôles</h2>

<?php if (!empty($message)): ?>
    <p style="color: green;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>Gestion des rôles</title>
</head>
<form method="POST" class="form-ajout">
    <input type="text" name="nom" placeholder="Nom du rôle" required>
    <input type="text" name="description" placeholder="Description" required>
    <button type="submit" name="ajouter">Ajouter</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($roles as $role): ?>
            <tr>
                <td><?= $role['id_role'] ?></td>
                <td><?= htmlspecialchars($role['nom']) ?></td>
                <td><?= htmlspecialchars($role['description']) ?></td>
                <td>
                    <a href="roles.php?supprimer=<?= $role['id_role'] ?>" onclick="return confirm('Supprimer ce rôle ?')"><i
                            class="bi bi-trash" style="color: red;"> Supprimer</i></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>

<style>
    /* Formulaire d'ajout de rôles */

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

    .form-ajout input[type="text"] {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 500px;
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
