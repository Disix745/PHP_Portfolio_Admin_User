<?php
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!$isLoggedIn) {
    $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
    header("Location: login.php");
    exit();
}

// Gestion de la recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Récupérer les projets
if ($isAdmin) {
    if ($search !== '') {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username 
            FROM projects p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.title LIKE ? OR p.description LIKE ?
            ORDER BY p.created_at DESC
        ");
        $like = "%$search%";
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $pdo->query("
            SELECT p.*, u.username 
            FROM projects p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC
        ");
    }
} else {
    if ($search !== '') {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? AND (title LIKE ? OR description LIKE ?) ORDER BY created_at DESC");
        $like = "%$search%";
        $stmt->execute([$_SESSION['user_id'], $like, $like]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
    }
}
$projects = $stmt->fetchAll();

// Traitement de l'ajout/modification de projet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $external_link = filter_input(INPUT_POST, 'external_link', FILTER_SANITIZE_URL);
    $project_id = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);

    $errors = [];

    if (empty($title)) {
        $errors[] = "Le titre est requis";
    }

    if (empty($description)) {
        $errors[] = "La description est requise";
    }

    // Gestion de l'upload d'image
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Le format de l'image n'est pas supporté. Formats acceptés : JPG, PNG, GIF, WEBP";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = "L'image est trop volumineuse. Taille maximale : 5MB";
        } else {
            $upload_dir = 'uploads/projects/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = $target_path;
            } else {
                $errors[] = "Erreur lors de l'upload de l'image";
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($project_id) {
                // Modification d'un projet existant
                $sql = "UPDATE projects SET title = ?, description = ?, external_link = ?";
                $params = [$title, $description, $external_link];

                if ($image_path) {
                    $sql .= ", image_path = ?";
                    $params[] = $image_path;
                }

                // Les admins peuvent modifier n'importe quel projet
                if ($isAdmin) {
                    $sql .= " WHERE id = ?";
                    $params[] = $project_id;
                } else {
                    $sql .= " WHERE id = ? AND user_id = ?";
                    $params[] = $project_id;
                    $params[] = $_SESSION['user_id'];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $_SESSION['success_message'] = "Projet modifié avec succès";
            } else {
                // Ajout d'un nouveau projet
                $stmt = $pdo->prepare("INSERT INTO projects (user_id, title, description, image_path, external_link) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $title, $description, $image_path, $external_link]);
                $_SESSION['success_message'] = "Projet ajouté avec succès";
            }
            header("Location: projects.php");
            exit();
        } catch (Exception $e) {
            $errors[] = "Une erreur est survenue lors de l'enregistrement du projet";
        }
    }
}

// Récupérer les informations d'un projet pour modification
$edit_project = null;
if (isset($_GET['edit'])) {
    if ($isAdmin) {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['edit'], $_SESSION['user_id']]);
    }
    $edit_project = $stmt->fetch();
}

// Suppression d'un projet
if (isset($_GET['delete'])) {
    if ($isAdmin) {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    }
    $_SESSION['success_message'] = "Projet supprimé avec succès";
    header("Location: projects.php");
    exit();
}
?>

<!-- Barre de recherche -->
<div class="row mb-4">
    <div class="col-md-8 offset-md-4">
        <form method="get" action="" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un projet..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0"><?php echo $edit_project ? 'Modifier le projet' : 'Ajouter un projet'; ?></h2>
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

                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($edit_project): ?>
                        <input type="hidden" name="project_id" value="<?php echo $edit_project['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">Titre</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $edit_project ? htmlspecialchars($edit_project['title']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo $edit_project ? htmlspecialchars($edit_project['description']) : ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if ($edit_project && $edit_project['image_path']): ?>
                            <div class="mt-2">
                                <img src="<?php echo htmlspecialchars($edit_project['image_path']); ?>" alt="Image actuelle" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="external_link" class="form-label">Lien externe</label>
                        <input type="url" class="form-control" id="external_link" name="external_link" value="<?php echo $edit_project ? htmlspecialchars($edit_project['external_link']) : ''; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary"><?php echo $edit_project ? 'Modifier' : 'Ajouter'; ?></button>
                    <?php if ($edit_project): ?>
                        <a href="projects.php" class="btn btn-secondary">Annuler</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0"><?php echo $isAdmin ? 'Tous les Projets' : 'Mes Projets'; ?></h2>
            </div>
            <div class="card-body">
                <?php if (empty($projects)): ?>
                    <p class="text-muted">Aucun projet trouvé.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($projects as $project): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <?php if ($project['image_path']): ?>
                                        <img src="<?php echo htmlspecialchars($project['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project['title']); ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                                        <?php if ($isAdmin): ?>
                                            <p class="card-text"><small class="text-muted">Par <?php echo htmlspecialchars($project['username']); ?></small></p>
                                        <?php endif; ?>
                                        <?php if ($project['external_link']): ?>
                                            <a href="<?php echo htmlspecialchars($project['external_link']); ?>" class="btn btn-primary btn-sm" target="_blank">Voir le projet</a>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <?php if ($isAdmin || $project['user_id'] == $_SESSION['user_id']): ?>
                                                <a href="?edit=<?php echo $project['id']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                                                <a href="?delete=<?php echo $project['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?')">Supprimer</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 