-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 12 juin 2025 à 10:50
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_ecole`
--

-- --------------------------------------------------------

--
-- Structure de la table `bulletin`
--

DROP TABLE IF EXISTS `bulletin`;
CREATE TABLE IF NOT EXISTS `bulletin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int DEFAULT NULL,
  `id_classe` int DEFAULT NULL,
  `annee_scolaire` varchar(9) DEFAULT NULL,
  `sequence` enum('Séquence 1','Séquence 2','Séquence 3','Séquence 4','Séquence 5','Séquence 6') NOT NULL,
  `moyenne` decimal(5,2) DEFAULT NULL,
  `rang` int DEFAULT NULL,
  `code_bulletin` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_bulletin` (`code_bulletin`),
  UNIQUE KEY `id_etudiant` (`id_etudiant`,`id_classe`,`annee_scolaire`,`sequence`),
  KEY `id_classe` (`id_classe`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `bulletin`
--

INSERT INTO `bulletin` (`id`, `id_etudiant`, `id_classe`, `annee_scolaire`, `sequence`, `moyenne`, `rang`, `code_bulletin`) VALUES
(3, 7, 1, '2024-2025', 'Séquence 1', 19.16, 1, 'BULLETIN_684AA494CCF');

-- --------------------------------------------------------

--
-- Structure de la table `classe`
--

DROP TABLE IF EXISTS `classe`;
CREATE TABLE IF NOT EXISTS `classe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `niveau` varchar(50) DEFAULT NULL,
  `capacite` int NOT NULL,
  `nombre` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `classe`
--

INSERT INTO `classe` (`id`, `nom`, `niveau`, `capacite`, `nombre`) VALUES
(1, 'B1C', '1', 50, 0);

-- --------------------------------------------------------

--
-- Structure de la table `classe_matiere`
--

DROP TABLE IF EXISTS `classe_matiere`;
CREATE TABLE IF NOT EXISTS `classe_matiere` (
  `id_classe` int NOT NULL DEFAULT '0',
  `id_matiere` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_classe`,`id_matiere`),
  KEY `id_matiere` (`id_matiere`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `classe_matiere`
--

INSERT INTO `classe_matiere` (`id_classe`, `id_matiere`) VALUES
(1, 3),
(1, 4),
(1, 5),
(1, 6);

-- --------------------------------------------------------

--
-- Structure de la table `config_paiement`
--

DROP TABLE IF EXISTS `config_paiement`;
CREATE TABLE IF NOT EXISTS `config_paiement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `montantfinal` int NOT NULL,
  `date_modif` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `config_paiement`
--

INSERT INTO `config_paiement` (`id`, `montantfinal`, `date_modif`) VALUES
(1, 1200000, '2025-06-10 14:15:08'),
(2, 2000000, '2025-06-11 09:40:05'),
(3, 2000000, '2025-06-11 09:40:54');

-- --------------------------------------------------------

--
-- Structure de la table `emploi_temp`
--

DROP TABLE IF EXISTS `emploi_temp`;
CREATE TABLE IF NOT EXISTS `emploi_temp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_classe` int NOT NULL,
  `date` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `id_matiere` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_classe` (`id_classe`),
  KEY `id_matiere` (`id_matiere`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `emploi_temp`
--

INSERT INTO `emploi_temp` (`id`, `id_classe`, `date`, `heure_debut`, `heure_fin`, `id_matiere`) VALUES
(2, 1, '2025-06-10', '15:47:00', '16:47:00', 3),
(3, 1, '2025-06-12', '07:00:00', '09:00:00', 4);

-- --------------------------------------------------------

--
-- Structure de la table `enseignant`
--

DROP TABLE IF EXISTS `enseignant`;
CREATE TABLE IF NOT EXISTS `enseignant` (
  `id` int NOT NULL,
  `specialite` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `enseignant`
--

INSERT INTO `enseignant` (`id`, `specialite`) VALUES
(8, 'programmation'),
(9, 'programmation');

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

DROP TABLE IF EXISTS `etudiant`;
CREATE TABLE IF NOT EXISTS `etudiant` (
  `id` int NOT NULL,
  `matricule` varchar(9) NOT NULL,
  `date_inscription` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`id`, `matricule`, `date_inscription`) VALUES
(4, 'ET0000001', '2025-06-10'),
(5, 'ET0000002', '2025-06-11'),
(6, 'ET0000003', '2025-06-11'),
(7, 'ET0000004', '2025-06-11'),
(10, 'ET0000005', '2025-06-11');

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

DROP TABLE IF EXISTS `inscription`;
CREATE TABLE IF NOT EXISTS `inscription` (
  `id_etudiant` int NOT NULL DEFAULT '0',
  `id_classe` int NOT NULL DEFAULT '0',
  `annee_scolaire` varchar(9) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_etudiant`,`id_classe`,`annee_scolaire`),
  KEY `id_classe` (`id_classe`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `inscription`
--

INSERT INTO `inscription` (`id_etudiant`, `id_classe`, `annee_scolaire`) VALUES
(4, 1, '2024-2025'),
(5, 1, '2024-2025'),
(6, 1, '2025-2026'),
(7, 1, '2025-2026'),
(10, 1, '2025-2026');

-- --------------------------------------------------------

--
-- Structure de la table `matiere`
--

DROP TABLE IF EXISTS `matiere`;
CREATE TABLE IF NOT EXISTS `matiere` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `coefficient` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `matiere`
--

INSERT INTO `matiere` (`id`, `nom`, `coefficient`) VALUES
(3, 'programmation c', 4),
(4, 'Algèbre', 4),
(5, 'SQL', 2),
(6, 'VBA EXCEL', 5);

-- --------------------------------------------------------

--
-- Structure de la table `matiere_enseignant`
--

DROP TABLE IF EXISTS `matiere_enseignant`;
CREATE TABLE IF NOT EXISTS `matiere_enseignant` (
  `matiere_id` int NOT NULL DEFAULT '0',
  `enseignant_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`matiere_id`,`enseignant_id`),
  KEY `enseignant_id` (`enseignant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `matiere_enseignant`
--

INSERT INTO `matiere_enseignant` (`matiere_id`, `enseignant_id`) VALUES
(3, 8),
(4, 8),
(5, 8),
(6, 8);

-- --------------------------------------------------------

--
-- Structure de la table `note`
--

DROP TABLE IF EXISTS `note`;
CREATE TABLE IF NOT EXISTS `note` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int DEFAULT NULL,
  `id_matiere` int DEFAULT NULL,
  `annee_scolaire` varchar(9) DEFAULT NULL,
  `sequence` enum('Séquence 1','Séquence 2','Séquence 3','Séquence 4','Séquence 5','Séquence 6') NOT NULL,
  `type_examen` enum('Devoir','Composition') NOT NULL,
  `note` decimal(5,2) DEFAULT NULL,
  `id_classe` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_etudiant` (`id_etudiant`),
  KEY `id_matiere` (`id_matiere`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `note`
--

INSERT INTO `note` (`id`, `id_etudiant`, `id_matiere`, `annee_scolaire`, `sequence`, `type_examen`, `note`, `id_classe`) VALUES
(1, 4, 4, '2024-2025', 'Séquence 1', 'Devoir', 0.00, 1),
(2, 5, 4, '2024-2025', 'Séquence 1', 'Devoir', 0.00, 1),
(3, 6, 4, '2024-2025', 'Séquence 1', 'Devoir', 15.00, 1),
(4, 7, 4, '2024-2025', 'Séquence 1', 'Devoir', 16.00, 1),
(5, 10, 4, '2024-2025', 'Séquence 1', 'Devoir', 16.00, 1),
(6, 4, 3, '2024-2025', 'Séquence 1', 'Devoir', 15.00, 1),
(7, 5, 3, '2024-2025', 'Séquence 1', 'Devoir', 16.00, 1),
(8, 6, 3, '2024-2025', 'Séquence 1', 'Devoir', 17.00, 1),
(9, 7, 3, '2024-2025', 'Séquence 1', 'Devoir', 20.00, 1),
(10, 10, 3, '2024-2025', 'Séquence 1', 'Devoir', 20.00, 1),
(11, 4, 5, '2024-2025', 'Séquence 1', 'Devoir', 15.00, 1),
(12, 5, 5, '2024-2025', 'Séquence 1', 'Devoir', 10.00, 1),
(13, 6, 5, '2024-2025', 'Séquence 1', 'Devoir', 16.00, 1),
(14, 7, 5, '2024-2025', 'Séquence 1', 'Devoir', 20.00, 1),
(15, 10, 5, '2024-2025', 'Séquence 1', 'Devoir', 20.00, 1),
(16, 4, 6, '2024-2025', 'Séquence 1', 'Devoir', 15.00, 1),
(17, 5, 6, '2024-2025', 'Séquence 1', 'Devoir', 16.00, 1),
(18, 6, 6, '2024-2025', 'Séquence 1', 'Devoir', 15.00, 1),
(19, 7, 6, '2024-2025', 'Séquence 1', 'Devoir', 20.00, 1),
(20, 10, 6, '2024-2025', 'Séquence 1', 'Devoir', 20.00, 1),
(21, 4, 3, '2024-2025', 'Séquence 1', 'Devoir', 15.00, 1),
(22, 5, 3, '2024-2025', 'Séquence 1', 'Devoir', 16.00, 1),
(23, 6, 3, '2024-2025', 'Séquence 1', 'Devoir', 17.00, 1),
(24, 7, 3, '2024-2025', 'Séquence 1', 'Devoir', 20.00, 1),
(25, 10, 3, '2024-2025', 'Séquence 1', 'Devoir', 20.00, 1);

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

DROP TABLE IF EXISTS `paiement`;
CREATE TABLE IF NOT EXISTS `paiement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date_paiement` date DEFAULT NULL,
  `type_paiement` enum('Inscription','Mensualité','Autre') DEFAULT NULL,
  `montantfinal` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_etudiant` (`id_etudiant`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `paiement`
--

INSERT INTO `paiement` (`id`, `id_etudiant`, `montant`, `date_paiement`, `type_paiement`, `montantfinal`) VALUES
(1, 4, 200000.00, '2025-06-10', 'Inscription', 1200000),
(2, 4, 5000.00, '2025-06-10', '', 1200000),
(3, 4, 1500.00, '2025-06-10', '', 1200000),
(4, 4, 150000.00, '2025-06-10', '', 1200000),
(5, 4, 50000.00, '2025-06-10', '', 1200000),
(6, 4, 15000.00, '2025-06-10', '', 1200000),
(7, 4, 5000.00, '2025-06-10', '', 1200000),
(8, 4, 773500.00, '2025-06-10', '', 1200000),
(9, 5, 200000.00, '2025-06-11', 'Inscription', 1200000),
(10, 5, 50000.00, '2025-06-11', '', 1200000),
(11, 6, 120.00, '2025-06-11', 'Mensualité', 2000000),
(12, 7, 15000000.00, '2025-06-11', 'Autre', 2000000),
(13, 10, 1500000.00, '2025-06-11', 'Autre', 2000000);

-- --------------------------------------------------------

--
-- Structure de la table `presence`
--

DROP TABLE IF EXISTS `presence`;
CREATE TABLE IF NOT EXISTS `presence` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int DEFAULT NULL,
  `date` date DEFAULT NULL,
  `etat` enum('Present','Absent','Justifier') NOT NULL,
  `id_matiere` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_etudiant` (`id_etudiant`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `presence`
--

INSERT INTO `presence` (`id`, `id_etudiant`, `date`, `etat`, `id_matiere`) VALUES
(30, 4, '2025-06-10', 'Present', 3),
(31, 6, '2025-06-10', 'Present', 3),
(32, 7, '2025-06-10', 'Present', 3),
(33, 10, '2025-06-10', 'Present', 3);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `genre` enum('M','F') DEFAULT NULL,
  `adresse` text,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type_utilisateur` enum('Etudiant','Enseignant','Administrateur') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `prenom`, `date_naissance`, `genre`, `adresse`, `telephone`, `email`, `password`, `type_utilisateur`) VALUES
(1, 'Admin', 'Principal', '1980-01-01', 'M', 'Siège école', '687516771', 'admin@gmail.com', 'f865b53623b121fd34ee5426c792e5c33af8c227', 'Administrateur'),
(4, 'EVINA ZE', 'ROMARIC', '2008-04-12', 'M', 'odza', '698526532', 'evina@gmail.com', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 'Etudiant'),
(5, 'jeukang', 'gre', '2007-05-12', 'F', 'ekounou', '687516771', 'Julietchokote@icloud.com', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 'Etudiant'),
(6, 'Kammadam', 'Wilfried', '2006-05-12', 'M', 'Yaounde, Ngousso', '621270674', 'admin@univ.com', 'f865b53623b121fd34ee5426c792e5c33af8c227', 'Etudiant'),
(7, 'Kammadam', 'Ange', '2000-01-15', 'M', 'Ngousso', '621270674', 'wkammadam@icloud.com', '7c222fb2927d828af22f592134e8932480637c0d', 'Etudiant'),
(8, 'Kammadam', 'Wilfried', '1985-05-12', 'M', 'Yaounde, Ngousso', '621270674', 'kammadamwil@gmail.com', '7c222fb2927d828af22f592134e8932480637c0d', 'Enseignant'),
(9, 'Kammadam', 'Wilfried', '1985-05-12', 'M', 'Ngousso', '621270674', 'angewilfried@gmail.com', '7c222fb2927d828af22f592134e8932480637c0d', 'Enseignant'),
(10, 'Kammadam', 'wilfried', '2006-05-12', 'M', 'Ngousso', '621270674', 'kammadam@gmail.com', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'Etudiant');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bulletin`
--
ALTER TABLE `bulletin`
  ADD CONSTRAINT `bulletin_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulletin_ibfk_2` FOREIGN KEY (`id_classe`) REFERENCES `classe` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `classe_matiere`
--
ALTER TABLE `classe_matiere`
  ADD CONSTRAINT `classe_matiere_ibfk_1` FOREIGN KEY (`id_classe`) REFERENCES `classe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classe_matiere_ibfk_2` FOREIGN KEY (`id_matiere`) REFERENCES `matiere` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `emploi_temp`
--
ALTER TABLE `emploi_temp`
  ADD CONSTRAINT `emploi_temp_ibfk_1` FOREIGN KEY (`id_classe`) REFERENCES `classe` (`id`),
  ADD CONSTRAINT `emploi_temp_ibfk_2` FOREIGN KEY (`id_matiere`) REFERENCES `matiere` (`id`);

--
-- Contraintes pour la table `enseignant`
--
ALTER TABLE `enseignant`
  ADD CONSTRAINT `enseignant_ibfk_1` FOREIGN KEY (`id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `etudiant`
--
ALTER TABLE `etudiant`
  ADD CONSTRAINT `etudiant_ibfk_1` FOREIGN KEY (`id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `inscription_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscription_ibfk_2` FOREIGN KEY (`id_classe`) REFERENCES `classe` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `matiere_enseignant`
--
ALTER TABLE `matiere_enseignant`
  ADD CONSTRAINT `matiere_enseignant_ibfk_1` FOREIGN KEY (`matiere_id`) REFERENCES `matiere` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matiere_enseignant_ibfk_2` FOREIGN KEY (`enseignant_id`) REFERENCES `enseignant` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `note`
--
ALTER TABLE `note`
  ADD CONSTRAINT `note_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_ibfk_2` FOREIGN KEY (`id_matiere`) REFERENCES `matiere` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD CONSTRAINT `paiement_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `presence`
--
ALTER TABLE `presence`
  ADD CONSTRAINT `presence_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
