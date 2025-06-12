<?php
require_once '../includes/header.php';

// Vérifier si l'utilisateur est connecté et est administrateur
if (!$isLoggedIn || !$isAdmin) {
    $_SESSION['error_message'] = "Vous n'avez pas les droits d'accès à cette page.";
    header("Location: ../index.php");
    exit();
}

// Récupérer toutes les compétences
$stmt = $pdo->query("SELECT * FROM skills ORDER BY name");
$skills = $stmt->fetchAll();

// Traitement de l'ajout/modification de compétence
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $skill_id = filter_input(INPUT_POST, 'skill_id', FILTER_VALIDATE_INT);

    $errors = [];

    if (empty($name)) {
        $errors[] = "Le nom de la compétence est requis";
    }

    // Vérifier si la compétence existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE name = ? AND id != ?");
    $stmt->execute([$name, $skill_id ?? 0]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Cette compétence existe déjà";
    }

    if (empty($errors)) {
        try {
            if ($skill_id) {
                // Modification d'une compétence existante
                $stmt = $pdo->prepare("UPDATE skills SET name = ? WHERE id = ?");
                $stmt->execute([$name, $skill_id]);
                $_SESSION['success_message'] = "Compétence modifiée avec succès";
            } else {
                // Ajout d'une nouvelle compétence
                $stmt = $pdo->prepare("INSERT INTO skills (name) VALUES (?)");
                $stmt->execute([$name]);
                $_SESSION['success_message'] = "Compétence ajoutée avec succès";
            }
            header("Location: skills.php");
            exit();
        } catch (Exception $e) {
            $errors[] = "Une erreur est survenue lors de l'enregistrement de la compétence";
        }
    }
}

// Récupérer les informations d'une compétence pour modification
$edit_skill = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_skill = $stmt->fetch();
}

// Suppression d'une compétence
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success_message'] = "Compétence supprimée avec succès";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Impossible de supprimer cette compétence car elle est utilisée par des utilisateurs";
    }
    header("Location: skills.php");
    exit();
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0"><?php echo $edit_skill ? 'Modifier la compétence' : 'Ajouter une compétence'; ?></h2>
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
                    <?php if ($edit_skill): ?>
                        <input type="hidden" name="skill_id" value="<?php echo $edit_skill['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nom de la compétence</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_skill ? htmlspecialchars($edit_skill['name']) : ''; ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary"><?php echo $edit_skill ? 'Modifier' : 'Ajouter'; ?></button>
                    <?php if ($edit_skill): ?>
                        <a href="skills.php" class="btn btn-secondary">Annuler</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">Liste des compétences</h2>
            </div>
            <div class="card-body">
                <?php if (empty($skills)): ?>
                    <p class="text-muted">Aucune compétence n'a été ajoutée.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($skills as $skill): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($skill['name']); ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $skill['id']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                                            <a href="?delete=<?php echo $skill['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette compétence ?')">Supprimer</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 