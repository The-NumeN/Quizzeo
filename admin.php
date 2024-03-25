<?php
session_start();
include 'classes.php';
$db = new BDD();
$adminSite = new AdminSite($db);
$users = new AdminSite($db);

$password = '';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: 403.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset ($_POST["user_id"]) && isset ($_POST["status"])) {
    $user_id = $_POST["user_id"];
    $status = $_POST["status"];
    $adminSite->updateUserStatus($user_id, $status);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset ($_POST["submit"])) {
    $pseudo = $_POST["pseudo"];
    $email = $_POST["email"];
    $hashed_password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = 5;
    $compte = 1;

    if (empty ($pseudo) || empty ($email) || empty ($_POST["password"])) {
        $error = "Tous les champs sont requis.";
    } else {
        $existingUser = $users->getUserByEmail($email);
        if ($existingUser) {
            $error = "Cet email est déjà utilisé. Veuillez choisir un autre.";
        } else {
            $query = "INSERT INTO users (pseudo, email, password, statut_compte, id_role) VALUES (:pseudo, :email, :password, :compte, :id_role)";
            $params = array(':pseudo' => $pseudo, ':email' => $email, ':password' => $hashed_password, ':compte' => $compte, ':id_role' => $role);
            $statement = $db->executeQuery($query, $params);

            if ($statement) {
                echo '<script>alert("Utilisateur ajouté avec succès.");</script>';
            } else {
                $error = "Une erreur s'est produite lors de l'ajout de l'utilisateur.";
            }
        }
    }
}
if (!empty ($error)) {
    echo "<script>alert('$error');</script>";
}

$listeUtilisateurs = $adminSite->Users();
$utilisateursCo = $adminSite->getUsersByStatus(1);
$listeQuizzes = $adminSite->Quizzes();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<?php include 'nav.php'; ?>

<body data-barba="wrapper">
    <div class="pages" data-barba="container" data-barba-namespace="home">
        <h1><span>A</span><span>d</span><span>m</span><span>i</span><span>n</span> Dashboard</h1>
        <div class="bigcard">
            <div class="card">
                <h2>Liste des utilisateurs créés :</h2>
                <ul>
                    <?php foreach ($listeUtilisateurs as $utilisateur): ?>
                        <li>
                            <?= $utilisateur['pseudo'] ?> -
                            <?= $utilisateur['email'] ?>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="user_id" value="<?= $utilisateur['id_user'] ?>">
                                <button type="submit" name="status"
                                    value="<?= $utilisateur['statut_compte'] ? 'inactive' : 'active' ?>">
                                    <?= $utilisateur['statut_compte'] ? 'Désactiver' : 'Activer' ?>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h2>Utilisateurs connectés en ce moment :</h2>
                <ul>
                    <?php foreach ($utilisateursCo as $utilisateur): ?>
                        <li>
                            <?= $utilisateur['pseudo'] ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h2>Créer un compte</h2>
                <button onclick="showForm()">Cliquer ici</button>
                <div id="creationForm" style="display: none;">
                    <form id="userCreationForm" method="post"
                        action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <label for="pseudo">Pseudo:</label>
                        <input type="text" id="pseudo" name="pseudo" required><br><br>

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required><br><br>

                        <label for="password">Mot de passe :</label>
                        <input type="password" id="password" name="password" required><br><br>

                        <label for="generatedPassword">Mot de passe généré:</label>
                        <span id="generatedPassword"></span><br><br>

                        <button onclick="generatePassword()">Générer un mot de passe</button><br><br>

                        <input type="submit" name="submit" value="Ajouter l'utilisateur">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="https://unpkg.com/@barba/core"></script>
    <script src="app.js"></script>
    <script src="script.js"></script>
</body>

</html>