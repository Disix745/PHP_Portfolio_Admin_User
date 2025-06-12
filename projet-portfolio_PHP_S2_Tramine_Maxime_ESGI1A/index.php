<?php
require_once 'includes/header.php';

// Gestion de la recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Récupérer les projets récents
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT p.*, u.username 
        FROM projects p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.title LIKE ? OR p.description LIKE ?
        ORDER BY p.created_at DESC 
        LIMIT 6
    ");
    $like = "%$search%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query("
        SELECT p.*, u.username 
        FROM projects p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 6
    ");
}
$recent_projects = $stmt->fetchAll();

// Récupérer les compétences les plus populaires
$stmt = $pdo->query("
    SELECT s.name, COUNT(us.skill_id) as count 
    FROM skills s 
    JOIN user_skills us ON s.id = us.skill_id 
    GROUP BY s.id 
    ORDER BY count DESC 
    LIMIT 5
");
$popular_skills = $stmt->fetchAll();
?>

<div class="jumbotron bg-light p-5 rounded">
    <h1 class="display-4">Bienvenue sur notre portfolio communautaire</h1>
    <p class="lead">Découvrez les projets et compétences de nos développeurs talentueux.</p>
    <?php if (!$isLoggedIn): ?>
        <hr class="my-4">
        <p>Rejoignez notre communauté pour partager vos projets et compétences.</p>
        <a class="btn btn-primary btn-lg" href="register.php" role="button">S'inscrire</a>
    <?php endif; ?>
</div>

<div class="row mt-5">
    <div class="col-md-8">
        <form method="get" action="" class="d-flex mb-3">
            <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un projet..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
        <h2>Projets Récents</h2>
        <div class="row">
            <?php foreach ($recent_projects as $project): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <?php if ($project['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($project['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project['title']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                            <p class="card-text"><small class="text-muted">Par <?php echo htmlspecialchars($project['username']); ?></small></p>
                            <?php if ($project['external_link']): ?>
                                <a href="<?php echo htmlspecialchars($project['external_link']); ?>" class="btn btn-primary" target="_blank">Voir le projet</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title h5 mb-0">Compétences Populaires</h3>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($popular_skills as $skill): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($skill['name']); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $skill['count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 