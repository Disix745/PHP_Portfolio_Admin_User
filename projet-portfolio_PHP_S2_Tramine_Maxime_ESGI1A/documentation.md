# Documentation du projet Portfolio PHP

## Présentation générale

Ce projet est un portfolio communautaire développé en PHP, permettant à des utilisateurs de s'inscrire, de présenter leurs projets, de gérer leurs compétences et de découvrir les réalisations des autres membres. Il intègre une gestion des rôles (utilisateur/admin), une interface moderne et responsive, et des fonctionnalités avancées pour l'administration.

---

## Organisation du projet

```
├── admin/
│   └── skills.php                # Gestion des compétences (admin)
├── assets/
│   └── css/
│       └── style.css             # Feuille de style principale (moderne/geek)
├── config/
│   └── database.php              # Connexion à la base de données
├── includes/
│   ├── header.php                # En-tête commun (navigation, liens, session)
│   └── footer.php                # Pied de page commun
├── uploads/
│   └── projects/                 # Images uploadées pour les projets
├── index.php                     # Page d'accueil (projets récents, recherche)
├── projects.php                  # Gestion des projets (CRUD, recherche)
├── profile.php                   # Gestion du profil utilisateur
├── login.php                     # Connexion
├── register.php                  # Inscription
├── logout.php                    # Déconnexion
├── database.sql                  # Structure de la base de données
├── sujet.md                      # Sujet du projet
├── sujet-portefolio.html         # Cahier des charges ou consignes
├── README.md                     # Présentation rapide
└── documentation.md              # Documentation détaillée (ce fichier)
```

---

## Fonctionnalités principales

- Authentification (inscription, connexion, déconnexion)
- Gestion des rôles (admin/utilisateur)
- Ajout, modification, suppression de projets (avec upload d'images JPG, PNG, GIF, WebP)
- Gestion des compétences (admin)
- Modification du profil et du mot de passe
- Recherche de projets (page d'accueil et page projets)
- Interface moderne (Google Fonts, couleurs néon, responsive)
- Sécurité : mots de passe hashés, requêtes préparées, validation des données, protection XSS/SQLi

---

## Explications des principaux fichiers et fonctions

### 1. `config/database.php`
- Initialise la connexion PDO à la base de données MySQL.
- Centralise les paramètres de connexion pour tout le projet.

### 2. `includes/header.php`
- Démarre la session (si besoin) et gère la navigation.
- Affiche les liens selon le rôle (admin ou utilisateur).
- Gère les chemins relatifs pour compatibilité avec les sous-dossiers.
- Affiche les messages de succès/erreur de session.

### 3. `index.php`
- Affiche les projets récents (6 derniers) avec une barre de recherche.
- Affiche les compétences les plus populaires.
- Utilise une requête SQL avec jointure pour récupérer les projets et les utilisateurs.

### 4. `projects.php`
- Permet à l'utilisateur de gérer ses projets (CRUD).
- Les admins peuvent voir, modifier et supprimer tous les projets.
- Upload sécurisé d'images (taille, format, dossier dédié).
- Barre de recherche pour filtrer les projets par titre ou description.
- Utilisation de requêtes préparées pour la sécurité.

### 5. `profile.php`
- Permet à l'utilisateur de modifier son profil, son email et son mot de passe.
- Permet de gérer ses compétences (niveau, ajout/suppression).
- Validation des champs et gestion des erreurs.

### 6. `admin/skills.php`
- Accessible uniquement aux admins.
- Permet d'ajouter, modifier ou supprimer des compétences globales.

### 7. `assets/css/style.css`
- Utilisation de variables CSS pour faciliter la personnalisation.

---

## Commentaires sur le code et bonnes pratiques

- **Sécurité** :
  - Utilisation de `password_hash()` et `password_verify()` pour les mots de passe.
  - Requêtes SQL préparées pour éviter les injections.
  - Filtrage et échappement des entrées utilisateur (`htmlspecialchars`, `filter_input`).
- **Organisation** :
  - Séparation claire entre la logique (PHP), la présentation (HTML/CSS) et la configuration.
  - Utilisation de fichiers d'inclusion (`header.php`, `footer.php`) pour éviter la duplication de code.
- **Gestion des rôles** :
  - Les pages sensibles vérifient le rôle de l'utilisateur avant d'autoriser l'accès ou l'action.
- **Expérience utilisateur** :
  - Messages d'erreur et de succès clairs.
  - Interface responsive et agréable.
  - Recherche rapide et efficace sur les projets.
- **Upload d'images** :
  - Contrôle du type MIME et de la taille.
  - Dossier dédié pour les images de projets.

---

## Fonctions PHP et concepts utilisés

- `session_start()`, `$_SESSION` : gestion des sessions et des rôles
- `PDO`, `prepare()`, `execute()` : accès sécurisé à la base de données
- `password_hash()`, `password_verify()` : sécurité des mots de passe
- `filter_input()`, `htmlspecialchars()` : validation et sécurisation des entrées
- `move_uploaded_file()` : gestion de l'upload d'images
- `header('Location: ...')` : redirections après action
- `isset()`, `empty()`, `trim()` : vérifications courantes
- Requêtes SQL avec jointures (`JOIN`) pour lier projets et utilisateurs



