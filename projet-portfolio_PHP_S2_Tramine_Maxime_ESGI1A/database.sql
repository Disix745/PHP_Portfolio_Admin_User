-- Création de la base de données
CREATE DATABASE IF NOT EXISTS projetb2;
USE projetb2;

-- Création de l'utilisateur et attribution des droits
CREATE USER IF NOT EXISTS 'projetb2'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON projetb2.* TO 'projetb2'@'localhost';
FLUSH PRIVILEGES;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des compétences
CREATE TABLE IF NOT EXISTS skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table de liaison utilisateurs-compétences
CREATE TABLE IF NOT EXISTS user_skills (
    user_id INT,
    skill_id INT,
    level ENUM('débutant', 'intermédiaire', 'avancé', 'expert') DEFAULT 'débutant',
    PRIMARY KEY (user_id, skill_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

-- Table des projets
CREATE TABLE IF NOT EXISTS projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    external_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertion des données de test
-- Utilisateurs (mot de passe: 'password')
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('user2', 'user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Compétences
INSERT INTO skills (name) VALUES
('PHP'),
('MySQL'),
('HTML'),
('CSS'),
('JavaScript'),
('React'),
('Node.js'),
('Python'),
('Java'),
('C++');

-- Projets de test
INSERT INTO projects (user_id, title, description, external_link) VALUES
(1, 'Projet Admin 1', 'Description du projet admin 1', 'https://example.com/project1'),
(1, 'Projet Admin 2', 'Description du projet admin 2', 'https://example.com/project2'),
(1, 'Projet Admin 3', 'Description du projet admin 3', 'https://example.com/project3'),
(2, 'Projet User1 1', 'Description du projet user1 1', 'https://example.com/project4'),
(2, 'Projet User1 2', 'Description du projet user1 2', 'https://example.com/project5'),
(2, 'Projet User1 3', 'Description du projet user1 3', 'https://example.com/project6'),
(3, 'Projet User2 1', 'Description du projet user2 1', 'https://example.com/project7'),
(3, 'Projet User2 2', 'Description du projet user2 2', 'https://example.com/project8'),
(3, 'Projet User2 3', 'Description du projet user2 3', 'https://example.com/project9'); 