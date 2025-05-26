<?php
session_start();
require_once 'includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE login = :login");
    $stmt->bindParam(':login', $login);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['id_role'] == 1 && $user['mot_de_passe'] === $password) {
        // Remplace === par password_verify($password, $user['mot_de_passe']) si tu utilises un hash ""
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit;
    } elseif ($user && $user['id_role'] != 1) {
        $error = "⛔ Accès refusé : seuls les administrateurs peuvent se connecter.";
    } else {
        $error = "❌ Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Connexion</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <?php if (isset($_GET['logout'])): ?>
        <p style="color: green;">✅ Vous avez été déconnecté avec succès.</p>
    <?php endif; ?>

    <h2>Connexion</h2>
    <form method="POST" action="">
        <input type="text" name="login" placeholder="Nom d'utilisateur" required><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br>
        <button type="submit">Se connecter</button>
    </form>

    <?php if (!empty($error))
        echo "<p style='color:red;'>$error</p>"; ?>

    <?php include 'includes/footer.php'; ?>
</body>

</html>

<style>
    form {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        width: 300px;
        text-align: center;
        margin: auto;
        margin-top: 50px;
        margin-bottom: 50px;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 14px;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    button {
        width: 100%;
        padding: 10px;
        background-color: #688fe9;
        border: none;
        color: white;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #0056b3;
    }

    p {
        font-size: 14px;
        margin: 10px 0;
        text-align: center;
    }
</style>