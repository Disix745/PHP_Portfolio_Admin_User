<?php
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!$isLoggedIn) {
    $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
    header("Location: login.php");
    exit();
}

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Récupérer les compétences de l'utilisateur
$stmt = $pdo->prepare("
    SELECT s.*, us.level 
    FROM skills s 
    JOIN user_skills us ON s.id = us.skill_id 
    WHERE us.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user_skills = $stmt->fetchAll();

// Récupérer toutes les compétences disponibles
$stmt = $pdo->query("SELECT * FROM skills ORDER BY name");
$all_skills = $stmt->fetchAll();

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $errors = [];

        // Validation des champs
        if (empty($username)) {
            $errors[] = "Le nom d'utilisateur est requis";
        }

        if (empty($email)) {
            $errors[] = "L'email est requis";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide";
        }

        // Vérifier si l'email ou le nom d'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $stmt->execute([$email, $username, $_SESSION['user_id']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Cet email ou nom d'utilisateur est déjà utilisé";
        }

        // Si un nouveau mot de passe est fourni
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $errors[] = "Le mot de passe actuel est requis pour changer le mot de passe";
            } elseif (!password_verify($current_password, $user['password'])) {
                $errors[] = "Le mot de passe actuel est incorrect";
            } elseif (strlen($new_password) < 8) {
                $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "Les nouveaux mots de passe ne correspondent pas";
            }
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Mise à jour des informations de base
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $email, $_SESSION['user_id']]);

                // Mise à jour du mot de passe si nécessaire
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                }

                $pdo->commit();
                $_SESSION['success_message'] = "Profil mis à jour avec succès";
                $_SESSION['username'] = $username;
                header("Location: profile.php");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Une erreur est survenue lors de la mise à jour du profil";
            }
        }
    }
    // Traitement de l'ajout/modification des compétences
    elseif (isset($_POST['update_skills'])) {
        $skills = $_POST['skills'] ?? [];
        $levels = $_POST['levels'] ?? [];

        try {
            $pdo->beginTransaction();

            // Supprimer toutes les compétences actuelles
            $stmt = $pdo->prepare("DELETE FROM user_skills WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            // Ajouter les nouvelles compétences
            $stmt = $pdo->prepare("INSERT INTO user_skills (user_id, skill_id, level) VALUES (?, ?, ?)");
            foreach ($skills as $skill_id) {
                if (isset($levels[$skill_id])) {
                    $stmt->execute([$_SESSION['user_id'], $skill_id, $levels[$skill_id]]);
                }
            }

            $pdo->commit();
            $_SESSION['success_message'] = "Compétences mises à jour avec succès";
            header("Location: profile.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Une erreur est survenue lors de la mise à jour des compétences";
        }
    }
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">Mon Profil</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <hr>
                    <h3 class="h5">Changer le mot de passe</h3>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Mettre à jour le profil</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">Mes Compétences</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <?php foreach ($all_skills as $skill): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="<?php echo $skill['id']; ?>" id="skill_<?php echo $skill['id']; ?>"
                                    <?php echo in_array($skill['id'], array_column($user_skills, 'id')) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="skill_<?php echo $skill['id']; ?>">
                                    <?php echo htmlspecialchars($skill['name']); ?>
                                </label>
                                <select class="form-select form-select-sm ms-3" name="levels[<?php echo $skill['id']; ?>]" style="width: 150px; display: inline-block;">
                                    <option value="débutant" <?php echo isset($user_skills[$skill['id']]) && $user_skills[$skill['id']]['level'] === 'débutant' ? 'selected' : ''; ?>>Débutant</option>
                                    <option value="intermédiaire" <?php echo isset($user_skills[$skill['id']]) && $user_skills[$skill['id']]['level'] === 'intermédiaire' ? 'selected' : ''; ?>>Intermédiaire</option>
                                    <option value="avancé" <?php echo isset($user_skills[$skill['id']]) && $user_skills[$skill['id']]['level'] === 'avancé' ? 'selected' : ''; ?>>Avancé</option>
                                    <option value="expert" <?php echo isset($user_skills[$skill['id']]) && $user_skills[$skill['id']]['level'] === 'expert' ? 'selected' : ''; ?>>Expert</option>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="update_skills" class="btn btn-primary">Mettre à jour les compétences</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 