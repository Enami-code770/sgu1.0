<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

require_once 'includes/db.php';

// Ajouter ou modifier un utilisateur
if (isset($_POST['ajouter']) || isset($_POST['modifier'])) {
    $login = $_POST['login'];
    $mot_de_passe = !empty($_POST['mot_de_passe']) ? password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT) : null;
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $id_role = $_POST['id_role'];

    if (isset($_POST['ajouter'])) {
        $stmt = $conn->prepare("INSERT INTO utilisateur (login, mot_de_passe, nom, prenom, email, id_role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$login, $mot_de_passe, $nom, $prenom, $email, $id_role]);
    } else {
        $id_user = $_POST['id_user'];
        if ($mot_de_passe) {
            $stmt = $conn->prepare("UPDATE utilisateur SET login=?, mot_de_passe=?, nom=?, prenom=?, email=?, id_role=? WHERE id_user=?");
            $stmt->execute([$login, $mot_de_passe, $nom, $prenom, $email, $id_role, $id_user]);
        } else {
            $stmt = $conn->prepare("UPDATE utilisateur SET login=?, nom=?, prenom=?, email=?, id_role=? WHERE id_user=?");
            $stmt->execute([$login, $nom, $prenom, $email, $id_role, $id_user]);
        }
    }
    header("Location: utilisateurs.php");
    exit;
}

// Supprimer un utilisateur
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $conn->prepare("DELETE FROM utilisateur WHERE id_user = ?")->execute([$id]);
    header("Location: utilisateurs.php");
    exit;
}

// Pré-remplir pour modification
$editUser = null;
if (isset($_GET['modifier'])) {
    $id = $_GET['modifier'];
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id_user = ?");
    $stmt->execute([$id]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Filtrage par recherche
$recherche = $_GET['recherche'] ?? '';
if (!empty($recherche)) {
    $stmt = $conn->prepare("SELECT u.*, r.nom AS role FROM utilisateur u 
                            LEFT JOIN role r ON u.id_role = r.id_role 
                            WHERE u.nom LIKE :search OR u.email LIKE :search");
    $searchTerm = '%' . $recherche . '%';
    $stmt->bindParam(':search', $searchTerm);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $users = $conn->query("SELECT u.*, r.nom AS role FROM utilisateur u 
                           LEFT JOIN role r ON u.id_role = r.id_role")->fetchAll(PDO::FETCH_ASSOC);
}

$roles = $conn->query("SELECT * FROM role")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>

<head>
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<h2>Gestion des utilisateurs</h2>

<!-- Formulaire pour ajouter ou modifier un utilisateur -->
<form method="POST" class="form-ajout">
    <input type="hidden" name="id_user" value="<?= $editUser['id_user'] ?? '' ?>">
    <input type="text" name="login" placeholder="Login" value="<?= $editUser['login'] ?? '' ?>" required>
    <input type="password" name="mot_de_passe"
        placeholder="Mot de passe <?= $editUser ? '(laisser vide si inchangé)' : '' ?>">
    <input type="text" name="nom" placeholder="Nom" value="<?= $editUser['nom'] ?? '' ?>" required>
    <input type="text" name="prenom" placeholder="Prénom" value="<?= $editUser['prenom'] ?? '' ?>" required>
    <input type="email" name="email" placeholder="Email" value="<?= $editUser['email'] ?? '' ?>" required>

    <select name="id_role" required>
        <option value="">-- Choisir un rôle --</option>
        <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id_role'] ?>" <?= (isset($editUser['id_role']) && $editUser['id_role'] == $r['id_role']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($r['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="<?= $editUser ? 'modifier' : 'ajouter' ?>">
        <?= $editUser ? 'Modifier' : 'Ajouter' ?>
    </button>
</form>

<!-- Barre de recherche -->
<form method="GET" action="utilisateurs.php" style="margin-bottom: 20px;" class="search-form">
    <input type="text" name="recherche" placeholder="Rechercher par nom ou email..."
        value="<?= htmlspecialchars($recherche) ?>">
    <button type="submit"
        style="background-color: #688fe9; width: 19%; color: white; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; box-sizing: border-box; font-size: 14px;">🔍
        Rechercher</button>
</form>

<!-- Tableau des utilisateurs -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Login</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id_user'] ?></td>
                <td><?= htmlspecialchars($u['login']) ?></td>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td><?= htmlspecialchars($u['prenom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td>
                    <a href="utilisateurs.php?modifier=<?= $u['id_user'] ?>"><i class="bi bi-pencil"
                            style="color: green;"></i></a>
                    &nbsp;
                    <a href="utilisateurs.php?supprimer=<?= $u['id_user'] ?>"
                        onclick="return confirm('Supprimer cet utilisateur ?')"><i class="bi bi-trash"
                            style="color: red;"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>
