# **Portfolio PHP**

Un système de gestion de portfolio développé en PHP permettant aux utilisateurs de gérer leurs compétences et projets.

## *Fonctionnalités principales*

- Authentification (inscription, connexion, déconnexion)
- Gestion des rôles (admin/utilisateur)
- Ajout, modification, suppression de projets (avec upload d'images JPG, PNG, GIF, WebP)
- Gestion des compétences (admin)
- Modification du profil et du mot de passe
- Recherche de projets (page d'accueil et page projets)
- Interface moderne (Google Fonts, couleurs néon, responsive)
- Sécurité : mots de passe hashés, requêtes préparées, validation des données, protection XSS/SQLi

# ATTENTION ⚠️⚠️⚠️

Utilisation d'un .env pour la production ! (database.php)
Inutile dans le cas de ce prototype

## *Prérequis*

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx)

## *Installation*

1. Importez la base de données :
```bash
mysql -u root -p < database.sql
```

2. Configurez les accès à la base de données dans `config/database.php`

3. Assurez-vous que le serveur web pointe vers le dossier du projet

## *Comptes de test*

### Administrateur
- Email : admin@example.com
- Mot de passe : password

### Utilisateurs
- Email : user1@example.com
- Mot de passe : password

- Email : user2@example.com
- Mot de passe : password

## *Technologies utilisées*

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- Bootstrap 5

## *Structure du projet*

```
├── admin/
│   └── skills.php
├── assets/
│   └── css/
│       └── style.css
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   └── footer.php
├── uploads/
│   └── projects/
│       ├── [images des projets]
├── index.php
├── projects.php
├── profile.php
├── login.php
├── register.php
├── logout.php
├── database.sql
└── README.md
└──  documentation.md   
```

## *Sécurité*

- Protection contre les injections SQL
- Protection XSS
- Mots de passe hashés
- Sessions sécurisées
- Validation des données 

## *Commentaires généraux sur le code*


- **Organisation** : Les fichiers sont bien rangés dans des dossiers dédiés (`config/`, `includes/`, `uploads/`), ce qui facilite la maintenance.
- **Connexion à la base de données** : Centralisée dans `config/database.php` avec PDO, ce qui est sécurisé et réutilisable.
- **Sécurité** :
  - Les mots de passe sont hashés avec `password_hash()`.
  - Les requêtes SQL sont préparées pour éviter les injections.
  - Les entrées utilisateurs sont filtrées et échappées pour limiter les failles XSS.
- **Gestion des sessions et des rôles** :
  - Les sessions permettent de gérer l'authentification et les rôles (admin/utilisateur).
  - Les accès aux pages sensibles sont protégés.
- **Fonctionnalités** :
  - Inscription, connexion, gestion du profil, des compétences et des projets.
  - Upload d'images sécurisé (taille, format).
  - Interface responsive avec Bootstrap et style moderne.
  - Recherche de projets intégrée.
- **Affichage** :
  - Les messages d'erreur et de succès sont clairs pour l'utilisateur.
  - Les formulaires sont bien structurés.


