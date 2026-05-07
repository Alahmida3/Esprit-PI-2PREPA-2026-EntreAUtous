-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 07 mai 2026 à 14:15
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `garage_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `id_client` int(10) UNSIGNED NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `adresse` varchar(100) DEFAULT NULL,
  `date_inscription` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id_client`, `nom`, `prenom`, `email`, `telephone`, `mot_de_passe`, `adresse`, `date_inscription`) VALUES
(1, 'ben ahmed', 'insaf', 'insaf@gmail.com', '26558074', '$2y$10$Cv8v5lbHUPqbQwCB4keyj.PpU3ZXzPGuHG/acjwxNAj.B0stiJWKm', 'ariana', '2026-05-07 12:00:10');

-- --------------------------------------------------------

--
-- Structure de la table `client_face`
--

CREATE TABLE `client_face` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_client` int(10) UNSIGNED NOT NULL,
  `signature` char(64) NOT NULL COMMENT 'pHash 8x8 binaire (64 bits)',
  `miniature` mediumtext DEFAULT NULL COMMENT 'base64 JPEG 64x64 pour affichage admin',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `client_face`
--

INSERT INTO `client_face` (`id`, `id_client`, `signature`, `miniature`, `created_at`, `updated_at`) VALUES
(1, 1, '1111111111111111110000101001100010011000000000001000111111111111', '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', '2026-05-07 12:00:10', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `client_signature`
--

CREATE TABLE `client_signature` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_client` int(10) UNSIGNED NOT NULL,
  `signature_b64` mediumtext NOT NULL COMMENT 'Canvas PNG base64',
  `context` varchar(40) NOT NULL DEFAULT 'register' COMMENT 'register | profile_update | action',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_journey`
--

CREATE TABLE `user_journey` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_client` int(10) UNSIGNED NOT NULL,
  `step_profile` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Profil complété',
  `step_explore` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Services explorés',
  `step_garage` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Garage consulté',
  `step_vehicle` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Véhicule ajouté',
  `step_rdv` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Rendez-vous pris',
  `step_message` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Message envoyé',
  `score` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Nb étapes complétées / 6',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `step_diagnostic` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_journey`
--

INSERT INTO `user_journey` (`id`, `id_client`, `step_profile`, `step_explore`, `step_garage`, `step_vehicle`, `step_rdv`, `step_message`, `score`, `updated_at`, `step_diagnostic`) VALUES
(1, 1, 1, 0, 0, 0, 0, 0, 2, '2026-05-07 13:14:05', 1);

-- --------------------------------------------------------

--
-- Structure de la table `user_tracking`
--

CREATE TABLE `user_tracking` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_client` int(10) UNSIGNED NOT NULL,
  `page` varchar(120) NOT NULL,
  `duration_s` smallint(6) NOT NULL DEFAULT 0 COMMENT 'Temps passé sur la page en secondes',
  `visited_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_tracking`
--

INSERT INTO `user_tracking` (`id`, `id_client`, `page`, `duration_s`, `visited_at`) VALUES
(1, 1, 'home', 35, '2026-05-07 12:47:29'),
(2, 1, 'home', 84, '2026-05-07 13:12:42'),
(3, 1, 'home', 1, '2026-05-07 13:14:04'),
(4, 1, 'home', 9, '2026-05-07 13:14:13'),
(5, 1, 'home', 10, '2026-05-07 13:14:26'),
(6, 1, 'home', 5, '2026-05-07 13:14:31');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id_client`),
  ADD KEY `idx_client_email` (`email`),
  ADD KEY `idx_client_nom` (`nom`),
  ADD KEY `idx_client_date` (`date_inscription`);

--
-- Index pour la table `client_face`
--
ALTER TABLE `client_face`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_client_face` (`id_client`);

--
-- Index pour la table `client_signature`
--
ALTER TABLE `client_signature`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sig_client` (`id_client`);

--
-- Index pour la table `user_journey`
--
ALTER TABLE `user_journey`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_journey_client` (`id_client`);

--
-- Index pour la table `user_tracking`
--
ALTER TABLE `user_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tracking_client` (`id_client`),
  ADD KEY `idx_tracking_date` (`visited_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `client`
--
ALTER TABLE `client`
  MODIFY `id_client` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `client_face`
--
ALTER TABLE `client_face`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `client_signature`
--
ALTER TABLE `client_signature`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_journey`
--
ALTER TABLE `user_journey`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `user_tracking`
--
ALTER TABLE `user_tracking`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `client_face`
--
ALTER TABLE `client_face`
  ADD CONSTRAINT `fk_face_client` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `client_signature`
--
ALTER TABLE `client_signature`
  ADD CONSTRAINT `fk_sig_client` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_journey`
--
ALTER TABLE `user_journey`
  ADD CONSTRAINT `fk_journey_client` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_tracking`
--
ALTER TABLE `user_tracking`
  ADD CONSTRAINT `fk_tracking_client` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
