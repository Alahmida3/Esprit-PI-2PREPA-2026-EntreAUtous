-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 12 mai 2026 à 02:11
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `integration`
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
(3, 'Hmida', 'ela', 'alahmida127@gmail.com', '55911880', '$2y$10$ATcTFrTkTNJhIKSicmF8n.SDeWu7JlEpuaWodF8QKt4gIhb75bxuu', 'ben arous', '2026-05-10 09:44:27'),
(11, 'ayachi', 'asma', 'asma@gmail.com', '36555444', '$2y$10$ggflQ8KWT1aNzH88dfKC4.h8l8tLg10B6x1tRm0r5ZuQDF1BlAIFW', '18, rue 6798, EL Omrane supErieur, Tunis, Tunisie', '2026-05-10 18:11:48'),
(13, 'ayachii', 'MOHAMED', 'admin@garage.com', '36555444', '$2y$10$7n.YxxJ0sEwix5ayBTrEp.JIV3RPRkVnG5T2ekprFWrEvMUAXS0Lu', '5 Le Clos de la Cathédrale\r\nLogement 6143', '2026-05-10 20:23:10');

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
-- Structure de la table `discussion`
--

CREATE TABLE `discussion` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `garage_id` int(10) UNSIGNED DEFAULT NULL,
  `objet` varchar(255) DEFAULT NULL,
  `ia_summary` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entre`
--

CREATE TABLE `entre` (
  `id_entretien` int(11) NOT NULL,
  `Matricule` varchar(50) NOT NULL,
  `date_entretien` date NOT NULL,
  `kilometrage` int(11) NOT NULL,
  `type_intervention` varchar(100) NOT NULL,
  `observations` varchar(100) NOT NULL,
  `statut` varchar(20) NOT NULL,
  `prochaine_echeance` date NOT NULL,
  `km_prochain` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `entre`
--

INSERT INTO `entre` (`id_entretien`, `Matricule`, `date_entretien`, `kilometrage`, `type_intervention`, `observations`, `statut`, `prochaine_echeance`, `km_prochain`, `deleted_at`) VALUES
(103, '156 TUN 7890', '2026-04-23', 1699, 'vidange', 'n,', 'en_cours', '2026-04-18', 15000, NULL),
(104, '88', '2026-04-27', 1699000, 'freinage', '............', 'en_cours', '2026-05-19', 19000, '2026-05-05 09:50:50'),
(105, 'tun217', '2026-05-30', 17000, 'freinage', 'ak,,', 'paye', '2026-08-01', 19100, NULL),
(106, '156 TUN 7890', '2026-10-30', 18000, 'freinage', 'eeeeeeeee', 'en_cours', '2026-11-01', 2000, '2026-04-26 15:13:07'),
(107, '22', '2026-10-30', 18000, 'freinage', 'qq', 'planifie', '2026-11-01', 20000, '2026-04-26 15:14:06'),
(108, '225', '2026-10-30', 18000, 'freinage', 'hhh', 'planifie', '2026-11-01', 89500, '2026-04-26 15:14:11'),
(109, '57', '2026-05-30', 18000, 'frein', 'aa', 'en_cours', '2026-04-22', 55, '2026-04-28 06:26:18'),
(112, '1', '2026-05-31', 90, 'VIDANGE', 'n', 'planifie', '2026-08-18', 600, '2026-04-28 06:26:04'),
(113, 'tun2144', '2026-07-31', 18000, 'freinage', 'nn', 'en_cours', '2026-09-18', 1000, NULL),
(114, 'tun123', '2026-07-31', 120, 'Climatisation', 'Recharge gaz + nettoyage effectué', 'paye', '2026-09-18', 1000, NULL),
(115, '99', '2026-07-31', 995, 'freinage', 'aa', 'en_cours', '2026-09-18', 1000, '2026-04-28 06:25:58'),
(117, '996', '2026-07-31', 995, 'test', 'tes', 'en_cours', '2026-09-18', 1000, '2026-04-28 06:26:08'),
(118, 'tun123', '2026-03-30', 511, 'vidange', 'aa', 'paye', '2026-11-30', 5500, NULL),
(119, 'tun2144', '2026-04-15', 5, 'vidange', '<<', 'termine', '2026-05-02', 977, NULL),
(120, 'tun2144', '2026-05-05', 99, 'VIDANGE', 'TRY', 'en_cours', '2026-04-24', 9, NULL),
(121, 'tun217', '2026-04-14', 56, 'VIDANGE', 'aa', 'annule', '2026-04-15', 66, NULL),
(122, 'tun123', '2026-04-21', 200, 'Liquide de refroidissement', 'Niveau ajusté, système OK', 'en_cours', '2026-05-09', 555, NULL),
(123, 'tun217', '2026-04-07', 88, 'oiling', 'bb', 'paye', '2026-10-24', 900, NULL),
(124, 'tun100', '2026-04-15', 63, 'changer la batterie', '', 'paye', '2026-11-16', 5500, NULL),
(125, 'tun100', '2026-07-30', 166, 'changer la batterie', 'Batterie faible, prévoir remplacement', 'en_cours', '2026-10-18', 1333, NULL),
(126, 'tun251', '2026-01-18', 90, 'changer les pneus', 'Pneus usés / pression vérifiée', 'en_cours', '2026-10-10', 133, NULL),
(127, 'tun2144', '2026-01-16', 87, 'Remplacement filtre à air', 'Filtre à air remplacé, moteur respire mieux', 'planifie', '2026-10-19', 133, NULL),
(128, 'tun251', '2026-07-16', 96, 'Remplacement bougies', 'Bougies changées, démarrage amélioré', 'paye', '2026-10-10', 150, NULL),
(129, 'tun100', '2026-07-26', 70, 'Batterie', 'Batterie remplacée / chargée, fonctionnement normal', 'planifie', '2026-10-30', 250, NULL),
(130, '136 TUN 7790', '2026-12-26', 600, 'Courroie de distribution', 'Courroie remplacée, sécurité assurée', 'paye', '2027-05-30', 21111, NULL),
(131, 'tun124', '2026-11-26', 60, 'Parallélisme', 'Parallélisme effectué, conduite stable', 'paye', '2027-02-22', 200, NULL),
(132, 'tun123', '2026-11-26', 60, 'Parallélisme', '', 'planifie', '2027-02-22', 200, NULL),
(133, '136 TUN 7790', '2026-04-23', 655, 'VIDANGE', 'aa', 'planifie', '2026-09-23', 5565, NULL),
(134, '136 TUN 7790', '2026-04-23', 655, 'VIDANGE', '11', 'en_cours', '2026-09-23', 5565, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `facture`
--

CREATE TABLE `facture` (
  `id_facture` int(11) NOT NULL,
  `ref_facture` varchar(60) NOT NULL,
  `date_emission` date NOT NULL,
  `montant_ht` decimal(10,0) NOT NULL,
  `taux_tva` decimal(10,0) NOT NULL,
  `montant_ttc` decimal(10,0) NOT NULL,
  `mode_paiement` varchar(20) NOT NULL,
  `etat_paiement` varchar(20) NOT NULL,
  `entretien` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `facture`
--

INSERT INTO `facture` (`id_facture`, `ref_facture`, `date_emission`, `montant_ht`, `taux_tva`, `montant_ttc`, `mode_paiement`, `etat_paiement`, `entretien`, `deleted_at`) VALUES
(1, 'Fac-44', '2026-04-02', 564, 7, 55, 'Carte Bancaire', 'Payée', 105, NULL),
(3, 'Fac-4', '2026-04-08', 567, 13, 850, 'Espèces', 'Payée', 104, NULL),
(5, 'Fac-48', '2026-04-02', 567, 19, 100, 'Espèces', 'Annulée', 106, NULL),
(6, 'Fac-488', '2026-04-02', 567, 19, 500, 'Espèces', 'En attente', 103, NULL),
(9, 'Fac-12', '2026-04-06', 567, 19, 850, 'Espèces', 'En attente', 107, NULL),
(10, 'Fac-4887', '2026-04-24', 567, 13, 850, 'Espèces', 'Payée', 108, NULL),
(14, 'Fac-7', '2026-03-30', 150, 19, 10, 'Chèque', 'Payée', 113, NULL),
(15, 'FAC-3', '2026-04-02', 90, 19, 23, 'Espèces', 'En attente', 117, NULL),
(18, 'FAC-70', '2026-04-02', 900, 13, 20, 'Espèces', 'En attente', 109, NULL),
(19, 'FAC-99', '2026-04-02', 98, 19, 23, 'Virement', 'Annulée', 115, NULL),
(20, 'FAC-95', '2026-04-02', 77, 19, 55, 'Carte Bancaire', 'Payée', 118, NULL),
(23, 'Fac-123', '2026-04-03', 55, 19, 88, 'Espèces', 'En attente', 120, NULL),
(25, 'Fac-16', '2026-02-22', 666, 19, 65, 'Espèces', 'Payée', 113, NULL),
(26, 'Fac-10', '2026-02-02', 100, 19, 13, 'Carte Bancaire', 'Payée', 130, NULL),
(28, 'Fac-17', '2026-03-15', 150, 13, 13, 'Carte Bancaire', 'Payée', 114, NULL),
(29, 'Fac-02', '2026-03-15', 200, 19, 19, 'Carte Bancaire', 'Payée', 131, NULL),
(30, 'Fac-08', '2026-03-15', 200, 19, 19, 'Carte Bancaire', 'Payée', 128, NULL),
(31, 'Fac-11', '2026-11-15', 811, 7, 13, 'Carte Bancaire', 'En attente', 125, NULL),
(33, 'Fac-789', '2026-12-15', 900, 7, 12, 'Carte Bancaire', 'Payée', 123, NULL),
(34, 'Fac-03', '2026-03-14', 9777, 13, 45, 'Carte Bancaire', 'Payée', 124, NULL),
(35, 'Fac-06', '2026-09-15', 9777, 7, 45, 'Carte Bancaire', 'En attente', 119, NULL),
(36, 'Fac-49999', '2026-02-22', 566, 7, 23, 'Espèces', 'En attente', 132, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `garages`
--

CREATE TABLE `garages` (
  `id-garage` int(11) NOT NULL,
  `id-responsable` int(11) NOT NULL,
  `nom_garage` varchar(300) NOT NULL,
  `adresse` varchar(300) NOT NULL,
  `email` varchar(300) NOT NULL,
  `telephone` int(11) NOT NULL,
  `heure-ouv` time NOT NULL,
  `heure_fer` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `garages`
--

INSERT INTO `garages` (`id-garage`, `id-responsable`, `nom_garage`, `adresse`, `email`, `telephone`, `heure-ouv`, `heure_fer`) VALUES
(123, 1234, 'AUTOauto', 'MHAMDIA BEN ARO', 'rayenben@salem.com', 28140987, '15:08:00', '03:08:00'),
(222, 123, 'AUTO', 'MHAMDIA BEN ARO', 'rayen@bensalem.com', 28140987, '06:57:00', '18:58:00'),
(1115, 1115, 'rayen', 'MHAMDIA BEN ARO', 'rayen@bensalem.com', 28140987, '10:14:00', '19:10:00'),
(1121, 12332, 'BEnSALem', 'MHAMDIA BEN ARO', 'rayen@bensalem.com', 28140987, '23:29:00', '11:29:00'),
(1432, 1324, 'rayen_ben_salem', 'MHAMDIA BEN ARO', 'rayen@bensalem.com', 28140987, '12:25:00', '00:25:00'),
(2221, 132, 'AUTO RAYEn', 'MHAMDIA BEN ARO', 'rayen.bensalem@esprit.tn', 28140987, '22:44:00', '10:44:00'),
(12347, 33333, 'BEN salem', 'MHAMDIA BEN ARO', 'rayen@bensalem.com', 28140987, '13:12:00', '01:12:00'),
(12349, 0, 'MH AUTO', 'MHAMDIA BEN AROUS', 'rayen.bensalem@esprit.tn', 28140987, '12:02:00', '00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `garagiste`
--

CREATE TABLE `garagiste` (
  `id_garagiste` int(10) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `nom` varchar(100) NOT NULL DEFAULT '',
  `prenom` varchar(100) NOT NULL DEFAULT '',
  `actif` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=actif, 0=désactivé',
  `cree_par_admin` varchar(100) NOT NULL DEFAULT 'admin@garage.com',
  `date_creation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `garagiste`
--

INSERT INTO `garagiste` (`id_garagiste`, `email`, `mot_de_passe`, `nom`, `prenom`, `actif`, `cree_par_admin`, `date_creation`) VALUES
(1, 'amin@garage.com', 'admin123', 'Amin', 'Garage', 1, 'admin@garage.com', '2026-05-10 12:05:41'),
(3, 'admin@garage.com', '$2y$10$kvi5QfoRdeiNCMFoqI/I6u595/gKznnLCF9t8343oxjTFiXGOphBK', 'Ayachi', 'asma', 1, 'admin@garage.com', '2026-05-10 18:55:24');

-- --------------------------------------------------------

--
-- Structure de la table `historique`
--

CREATE TABLE `historique` (
  `id` int(11) NOT NULL,
  `action` varchar(20) NOT NULL,
  `nom_garage` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `date_action` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `discussion_id` int(11) DEFAULT NULL,
  `expediteur_id` int(11) DEFAULT NULL,
  `expediteur_type` enum('user','garage') DEFAULT NULL,
  `type` enum('text','image','audio') DEFAULT 'text',
  `contenu` text DEFAULT NULL,
  `ia_analysis` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ia_analysis`)),
  `date_envoi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type_action` varchar(20) NOT NULL,
  `type_entite` varchar(20) NOT NULL,
  `message` varchar(255) NOT NULL,
  `date_notif` datetime DEFAULT current_timestamp(),
  `lu` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `type_action`, `type_entite`, `message`, `date_notif`, `lu`) VALUES
(1, 'MODIFICATION', 'GARAGE', 'Garage \"RAYEnAUto\" modifié.', '2026-05-04 21:50:08', 1),
(2, 'MODIFICATION', 'SERVICE', 'Service \"change bougie / reparation moteur\" (ID 22223) modifié.', '2026-05-04 21:53:36', 1),
(3, 'AJOUT', 'SERVICE', 'Service \"Disque\" ajouté au garage ID 12349.', '2026-05-04 22:03:46', 1),
(4, 'MODIFICATION', 'GARAGE', 'Garage \"RAYEnAUto\" modifié.', '2026-05-04 22:05:31', 1),
(5, 'MODIFICATION', 'GARAGE', 'Garage \"RAYEnAUto\" modifié.', '2026-05-04 22:09:30', 1),
(6, 'MODIFICATION', 'GARAGE', 'Garage \"rayen\" modifié.', '2026-05-04 22:10:17', 1),
(7, 'MODIFICATION', 'GARAGE', 'Garage \"RAYEnAUto\" modifié.', '2026-05-08 00:53:57', 0),
(8, 'SUPPRESSION', 'GARAGE', 'Garage \"RAYEnAUto\" supprimé.', '2026-05-10 18:50:04', 0);

-- --------------------------------------------------------

--
-- Structure de la table `piece`
--

CREATE TABLE `piece` (
  `id_piece` int(11) NOT NULL,
  `nom_piece` varchar(255) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `quantite_stock` int(11) DEFAULT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `fourniseur` varchar(255) DEFAULT NULL,
  `date_ajout` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `piece`
--

INSERT INTO `piece` (`id_piece`, `nom_piece`, `reference`, `description`, `prix`, `quantite_stock`, `categorie`, `image`, `fourniseur`, `date_ajout`) VALUES
(2, 'Filtre à air', 'REF002', 'Filtre air moteur', 30.00, 62, 'Moteur', 'https://www.atoutfret.fr/wp-content/uploads/2024/07/filtre-a-air-voiture.jpg', 'Bosch', '2026-04-22 11:24:43'),
(3, 'Plaquette de frein', 'REF003', 'Plaquette avant', 75.00, 59, 'Freinage', 'https://revolttechnology.com/wp-content/uploads/2025/09/Plaquette-de-frein-Ferodo-comment-choisir-la-bonne-reference-pour-votre-voiture.jpg', 'Brembo', '2026-04-22 11:24:43'),
(4, 'Disque de frein', 'REF004', 'Disque avant', 120.00, 40, 'Freinage', 'https://www.mober.paris/wp-content/uploads/2024/09/Freinage_haute_performance__tout_savoir_sur_le_choix_de_vos_disques_de_frein.webp', 'Brembo', '2026-04-22 11:24:43'),
(5, 'Batterie', 'REF005', 'Batterie 12V', 150.00, 29, 'Électrique', 'https://www.turbo.fr/sites/default/files/2019-03/batterie-6_0.png', 'Varta', '2026-04-22 11:24:43'),
(6, 'Bougie', 'REF006', 'Bougie allumage', 15.00, 184, 'Moteur', 'https://mecazen.fr/storage/wordpress/shared/2024/07/AdobeStock_176715253_11zon-1024x576.jpeg', 'NGK', '2026-04-22 11:24:43'),
(7, 'Amortisseur', 'REF007', 'Amortisseur arrière', 90.00, 93, 'Suspension', 'https://img.freepik.com/photos-premium/closeup-ressorts-amortisseurs-chocs-rad-amortisseurs-chocs-se-concentrent-suspension_1040474-850.jpg', 'Monroe', '2026-04-22 11:24:43'),
(8, 'Radiateur', 'REF008', 'Radiateur moteur', 200.00, 19, 'Refroidissement', 'https://2.bp.blogspot.com/-j_HgptPp3EQ/WaBxV5vp5YI/AAAAAAAAgK0/crHn6-IrWlMC5AlNsSxOPsdiBUtSoel1QCLcBGAs/s1600/Comment-fonctionne-un-radiateur-de-voiture.jpg', 'Valeo', '2026-04-22 11:24:43'),
(9, 'Pompe à eau', 'REF009', 'Pompe refroidissement', 85.00, 42, 'Refroidissement', 'https://3.bp.blogspot.com/-giPCrPP33mQ/WNL6AiK2BVI/AAAAAAAABlE/PGSroCvKF2MpJNLOz23fTQEPXCiZg0-kACLcB/s1600/pompe-a-eau-hs%25281%2529.jpg', 'Valeo', '2026-04-22 11:24:43'),
(10, 'Courroie', 'REF010', 'Courroie distribution', 60.00, 4, 'Moteur', 'https://tse3.mm.bing.net/th/id/OIP.jYvDPT3sxC4PGCJHSAmwgAHaE8?pid=Api&h=220&P=0', 'Gates', '2026-04-22 11:24:43'),
(11, 'Alternateur', 'REF011', 'Alternateur voiture', 250.00, 15, 'Électrique', 'https://tse2.mm.bing.net/th/id/OIP.g1sn_sdpZaH3IwPiQp2vuQHaE8?pid=Api&h=220&P=0', 'Bosch', '2026-04-22 11:24:43'),
(12, 'Démarreur', 'REF012', 'Démarreur moteur', 220.00, 54, 'Électrique', 'https://www.sport-cars.org/wp-content/uploads/2022/08/demarreur-voiture-moteur-1024x587.jpg', 'Bosch', '2026-04-22 11:24:43'),
(13, 'Clignotant', 'REF013', 'Feu clignotant', 20.00, 57, 'Éclairage', 'https://www.oovango.com/wp-content/uploads/2023/01/Clignotants.jpg', 'Hella', '2026-04-22 11:24:43'),
(14, 'Phare', 'REF014', 'Phare avant', 180.00, 25, 'Éclairage', 'https://tse2.mm.bing.net/th/id/OIP.zlrno_Nm750OYGjKH0iX0gHaE8?pid=Api&h=220&P=0', 'Hella', '2026-04-22 11:24:43'),
(15, 'Rétroviseur', 'REF015', 'Rétroviseur gauche', 70.00, 39, 'Carrosserie', 'https://tse4.mm.bing.net/th/id/OIP.hDnAHF4RB5bgevv5Y2cXOgHaE8?pid=Api&h=220&P=0', 'Valeo', '2026-04-22 11:24:43'),
(16, 'Pare-choc', 'REF016', 'Pare-choc avant', 300.00, 4, 'Carrosserie', 'https://www.france-piece-auto.com/files/media/piece-carrosserie/pare-chocs-avant-1.jpg', 'Toyota', '2026-04-22 11:24:43'),
(17, 'Capot', 'REF017', 'Capot moteur', 400.00, 5, 'Carrosserie', 'https://static.wixstatic.com/media/bd0279_5ec47b36dd7b458a914beae19730809c.jpg/v1/fill/w_980,h_654,al_c,q_85,usm_0.66_1.00_0.01,enc_avif,quality_auto/bd0279_5ec47b36dd7b458a914beae19730809c.jpg', 'Toyota', '2026-04-22 11:24:43'),
(18, 'Essuie-glace', 'REF018', 'Balais essuie-glace', 18.00, 120, 'Accessoires', 'https://groupealso.fr/wp-content/uploads/2025/02/balais-essuie-glace.webp', 'Bosch', '2026-04-22 11:24:43'),
(19, 'Volant', 'REF019', 'Volant direction', 150.00, 12, 'Intérieur', 'https://wordpress-content.vroomly.com/wp-content/uploads/2023/03/shutterstock_278092436.jpg', 'BMW', '2026-04-22 11:24:43'),
(20, 'Siège', 'REF020', 'Siège conducteur', 350.00, 3, 'Intérieur', 'https://i.pinimg.com/originals/97/7d/56/977d56d2601ac2ad7f8e9cb8f21c441c.jpg', 'Audi', '2026-04-22 11:24:43'),
(21, 'Pneu', 'REF021', 'Pneu 16 pouces', 110.00, 60, 'Roues', 'https://la-voiture.fr/wp-content/uploads/2023/04/pneu_choisir_la_voiture.jpg', 'Michelin', '2026-04-22 11:24:43'),
(22, 'Jante', 'REF022', 'Jante aluminium', 200.00, 28, 'Roues', 'https://cdn.prod.website-files.com/6413856d54d41b5f298d5953/653bcaedbc2af952d16889f6_mur-jantes-neuves.jpeg', 'Michelin', '2026-04-22 11:24:43'),
(23, 'Capteur ABS', 'REF023', 'Capteur roue', 65.00, 45, 'Électronique', 'https://www.flauraud.fr/wp-content/uploads/2018/11/capteur-ABS-2.jpg', 'Bosch', '2026-04-22 11:24:43'),
(24, 'Injecteur', 'REF024', 'Injecteur carburant', 130.00, 25, 'Moteur', 'https://wordpress-content.vroomly.com/wp-content/uploads/2023/03/symptome_injecteur_qui_fuit.jpg', 'Bosch', '2026-04-22 11:24:43'),
(25, 'Turbo', 'REF025', 'Turbo moteur', 600.00, 3, 'Moteur', 'https://tse4.mm.bing.net/th/id/OIP.VR2eanc0H2dUAYdDc0SFlgHaEL?pid=Api&h=220&P=0', 'Garrett', '2026-04-22 11:24:43'),
(26, 'Filtre habitacle', 'REF026', 'Filtre climatisation', 22.00, 90, 'Climatisation', 'https://tse1.mm.bing.net/th/id/OIP.qfrqjlBtzKiPiWGokIeHsAHaEo?pid=Api&h=220&P=0', 'Valeo', '2026-04-22 11:31:02'),
(27, 'Compresseur clim', 'REF027', 'Compresseur AC', 320.00, 12, 'Climatisation', 'https://tse3.mm.bing.net/th/id/OIP._BjDBTu-7ChCC8omnc-zGwHaE7?pid=Api&h=220&P=0', 'Denso', '2026-04-22 11:31:02'),
(28, 'Condenseur', 'REF028', 'Condenseur climatisation', 180.00, 20, 'Climatisation', 'https://www.fiches-auto.fr/images/illustrations_definitions/88-condenseur-de-climatisation.jpg', 'Valeo', '2026-04-22 11:31:02'),
(29, 'Étrier frein', 'REF029', 'Étrier avant', 140.00, 25, 'Freinage', 'https://tse1.mm.bing.net/th/id/OIP.KnTDnjVYglJ1GbrWDFFtcgHaE8?pid=Api&h=220&P=0', 'Brembo', '2026-04-22 11:31:02'),
(30, 'Maître-cylindre', 'REF030', 'Maître cylindre frein', 95.00, 18, 'Freinage', 'https://wordpress-content.vroomly.com/wp-content/uploads/2023/03/iStock-1071514854.jpg', 'ATE', '2026-04-22 11:31:02'),
(31, 'Liquide de frein', 'REF031', 'Liquide DOT4', 12.00, 200, 'Freinage', 'https://liquimoly.cloudimg.io/v7/https://www.liqui-moly.com/fileadmin/_processed_/4/2/csm_20241002_bremsfluessigkeit_uebersicht_Teaser_0742038068.png?force_format=webp%2Coriginal', 'Total', '2026-04-22 11:31:02'),
(32, 'Cardan', 'REF032', 'Cardan transmission', 210.00, 15, 'Transmission', 'https://www.opisto.fr/fr/entreprise/wp-content/uploads/2023/11/cardan-voiture.png', 'SKF', '2026-04-22 11:31:02'),
(33, 'Boîte de vitesse', 'REF033', 'Boîte manuelle', 1200.00, 20, 'Transmission', 'https://tse4.mm.bing.net/th/id/OIP.iDFJdgqbZpx9l3E-OoANmAHaEO?pid=Api&h=220&P=0', 'ZF', '2026-04-22 11:31:02'),
(34, 'Embrayage', 'REF034', 'Kit embrayage', 250.00, 20, 'Transmission', 'https://canalcarro.com/wp-content/uploads/2025/11/embreagem.webp', 'Valeo', '2026-04-22 11:31:02'),
(35, 'Volant moteur', 'REF035', 'Volant moteur', 350.00, 10, 'Transmission', 'https://abmecasport.fr/wp-content/uploads/2022/04/volant-moteur-allege-acier-forge-moteur-tu-184mm.jpg', 'LUK', '2026-04-22 11:31:02'),
(36, 'Sonde lambda', 'REF036', 'Capteur O2', 85.00, 39, 'Électronique', 'https://image.ceneostatic.pl/data/products/189264101/i-auto-partner-sa-sonda-lambda-kia-carens-ceed-proceed-rio-sportage-stonic-venga-10-es21465-12b1.jpg', 'Bosch', '2026-04-22 11:31:02'),
(37, 'Capteur PMH', 'REF037', 'Capteur vilebrequin', 60.00, 50, 'Électronique', 'https://www.meca-bv.fr/wp-content/uploads/2025/04/Capteur-PMH-point-mort-haut-a-quoi-sert-il-.webp', 'Bosch', '2026-04-22 11:31:02'),
(38, 'Faisceau électrique', 'REF038', 'Câblage voiture', 180.00, 12, 'Électrique', 'https://static5.unitrailer.be/hpeciai/550f0e7fd433e95e3a071649ec5e68aa/fre_pl_Faisceau-electrique-MANTES-remorque-3-8-m-FICHE-BAIONNETTE-7-BROCHES-2x-5-BROCHES-5104_6.png', 'Valeo', '2026-04-22 11:31:02'),
(39, 'Fusible', 'REF039', 'Fusible 10A', 2.00, 499, 'Électrique', 'https://thumbs.dreamstime.com/b/fusibles-de-voitures-sur-la-table-noire-les-accessoires-automobiles-ont-d%C3%BB-r%C3%A9parer-le-syst%C3%A8me-%C3%A9lectrique-fond-sombre-166334431.jpg', 'Hella', '2026-04-22 11:31:02'),
(40, 'Relais', 'REF040', 'Relais électrique', 15.00, 150, 'Électrique', 'https://m.media-amazon.com/images/I/71wAK0q2hhL._AC_.jpg', 'Bosch', '2026-04-22 11:31:02'),
(41, 'Ventilateur radiateur', 'REF041', 'Ventilateur moteur', 130.00, 20, 'Refroidissement', 'https://img.freepik.com/photos-premium/installation-ventilateur-refroidissement-du-moteur-voiture-reparation-entretien-automobile_326821-5032.jpg', 'Valeo', '2026-04-22 11:31:02'),
(42, 'Thermostat', 'REF042', 'Thermostat moteur', 35.00, 70, 'Refroidissement', 'https://www.actualidadmotor.com/wp-content/uploads/2018/04/termostato-1024x614.jpg', 'Valeo', '2026-04-22 11:31:02'),
(43, 'Réservoir carburant', 'REF043', 'Réservoir essence', 400.00, 5, 'Carburant', 'https://wordpress-content.vroomly.com/wp-content/uploads/2023/03/iStock-853369054.jpg', 'Toyota', '2026-04-22 11:31:02'),
(44, 'Pompe carburant', 'REF044', 'Pompe essence', 150.00, 25, 'Carburant', 'https://image.made-in-china.com/2f0j10iZeaFRUMnLoS/-Pompe-essence-lectrique-pour-la-voiture-.jpg', 'Bosch', '2026-04-22 11:31:02'),
(45, 'Injecteur diesel', 'REF045', 'Injecteur diesel', 180.00, 30, 'Carburant', 'https://iturbo.fr/wp/wp-content/uploads/2024/05/shutterstock_1191489310-1.jpg', 'Bosch', '2026-04-22 11:31:02'),
(46, 'Ceinture sécurité', 'REF046', 'Ceinture avant', 90.00, 40, 'Sécurité', 'https://wordpress-content.vroomly.com/wp-content/uploads/2023/03/iStock-1198542986.jpg', 'Autoliv', '2026-04-22 11:31:02'),
(47, 'Airbag', 'REF047', 'Airbag conducteur', 500.00, 16, 'Sécurité', 'https://uploads.vrum.com.br/2023/08/19a1adb2-1.jpg', 'Autoliv', '2026-04-22 11:31:02'),
(48, 'Serrure porte', 'REF048', 'Serrure voiture', 55.00, 34, 'Carrosserie', 'https://qctop.com/wp-content/uploads/2023/04/La-serrure-de-la-porte-arriere-de-la-voiture-a-une-fonction-meconnue-qui-peut-vous-eviter-de-gros-problemes-FB-1200x628-1024x536.jpg', 'Valeo', '2026-04-22 11:31:02'),
(49, 'Poignée porte', 'REF049', 'Poignée extérieure', 25.00, 60, 'Carrosserie', 'https://img.freepik.com/vecteurs-premium/poignee-porte-voiture-grise_33869-170.jpg', 'Toyota', '2026-04-22 11:31:02'),
(50, 'Vitres électriques', 'REF050', 'Moteur vitre', 110.00, 21, 'Électrique', 'https://img.freepik.com/photos-premium/boutons-vitres-electriques-avant-arriere-abaisser-fermer-vitres-voiture_176402-8299.jpg', 'Valeo', '2026-04-22 11:31:02'),
(57, 'Batterie', 'REF008222', '...', 12000.00, 500, 'Électrique', 'https://www.atoutfret.fr/wp-content/uploads/2024/07/filtre-a-air-voiture.jpg', 'asma', '2026-05-10 19:59:14');

-- --------------------------------------------------------

--
-- Structure de la table `rendezvous`
--

CREATE TABLE `rendezvous` (
  `idRDV` int(11) NOT NULL,
  `dateRDV` date DEFAULT NULL,
  `heureRDV` time DEFAULT NULL,
  `type_serviceRDV` varchar(300) DEFAULT NULL,
  `statutRDV` varchar(25) DEFAULT 'en attente',
  `idVehicule` int(11) DEFAULT NULL,
  `idclientRDV` int(10) UNSIGNED NOT NULL,
  `descriptionRDV` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rendezvous`
--

INSERT INTO `rendezvous` (`idRDV`, `dateRDV`, `heureRDV`, `type_serviceRDV`, `statutRDV`, `idVehicule`, `idclientRDV`, `descriptionRDV`) VALUES
(207, '2026-05-11', '20:17:00', 'vidange', 'En attente', 63, 3, 'nh'),
(209, '2026-05-20', '22:23:00', 'Moteur', 'En attente', 64, 3, 'nbh'),
(210, '2026-11-20', '10:00:00', 'Moteur', 'annulé', 64, 3, 'Prochain RDV prédit automatiquement'),
(211, '2026-05-12', '21:23:00', 'change bougie / reparation moteur', 'En attente', 63, 3, 'njh');

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id-service` int(11) NOT NULL,
  `id_garage` int(11) NOT NULL,
  `nom_service` varchar(300) NOT NULL,
  `prix` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`id-service`, `id_garage`, `nom_service`, `prix`) VALUES
(1222, 123, 'Moteur', 3500),
(2344, 222, 'vidange', 35),
(22222, 1121, 'Pompe', 120),
(22223, 1432, 'change bougie / reparation moteur', 60),
(22224, 222, 'vidange', 30),
(22228, 123, 'Moteur', 3500),
(22233, 1115, 'Lavage', 12),
(22234, 2221, 'Moteur_KIA', 2200),
(22235, 1115, 'Lavage', 12),
(22236, 1115, 'Lavage', 12),
(22237, 12349, 'Disque', 600);

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
(25, 3, 1, 0, 0, 0, 0, 0, 1, '2026-05-10 09:49:41', 0),
(43, 11, 1, 0, 0, 0, 0, 0, 1, '2026-05-10 18:12:20', 0),
(65, 13, 0, 0, 0, 0, 0, 0, 0, '2026-05-10 20:23:10', 0);

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

-- --------------------------------------------------------

--
-- Structure de la table `vehicule`
--

CREATE TABLE `vehicule` (
  `idVehicule` int(11) NOT NULL,
  `marqueV` varchar(30) DEFAULT NULL,
  `date_ajoutV` date DEFAULT NULL,
  `kilometrageV` float DEFAULT NULL,
  `nomclient` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idclient` int(10) UNSIGNED NOT NULL,
  `imageVoiture` varchar(300) NOT NULL,
  `matriculevoiture` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vehicule`
--

INSERT INTO `vehicule` (`idVehicule`, `marqueV`, `date_ajoutV`, `kilometrageV`, `nomclient`, `idclient`, `imageVoiture`, `matriculevoiture`) VALUES
(63, 'BMW', '2026-05-10', 56789, 'Hmida', 3, '1778377827_69ffe463a3bcb.jpg', '156 TUN 7890'),
(64, 'CLA180', '2026-05-10', 7777780, 'Hmida', 3, '1778379364_69ffea6420c02.jpg', '136 TUN 7790');

-- --------------------------------------------------------

--
-- Structure de la table `vente`
--

CREATE TABLE `vente` (
  `id` int(11) NOT NULL,
  `id_piece` int(10) UNSIGNED NOT NULL,
  `quantite` int(11) NOT NULL,
  `date_vente` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vente`
--

INSERT INTO `vente` (`id`, `id_piece`, `quantite`, `date_vente`) VALUES
(4, 6, 1, '2026-04-23 17:31:05'),
(5, 22, 1, '2026-04-27 23:54:59'),
(6, 36, 1, '2026-04-27 23:55:15'),
(7, 6, 1, '2026-04-27 23:55:25'),
(8, 5, 1, '2026-04-28 14:28:42'),
(9, 39, 1, '2026-04-28 14:30:23'),
(10, 22, 1, '2026-04-28 15:31:00'),
(11, 50, 1, '2026-05-02 23:48:54'),
(12, 7, 1, '2026-05-02 23:49:06'),
(13, 3, 1, '2026-05-02 23:55:56'),
(14, 6, 1, '2026-05-02 23:55:56'),
(15, 9, 1, '2026-05-02 23:55:56'),
(16, 8, 1, '2026-05-02 23:55:56'),
(17, 48, 1, '2026-05-03 00:24:36'),
(18, 2, 9, '2026-05-03 00:24:47'),
(19, 2, 9, '2026-05-03 00:26:34'),
(20, 6, 10, '2026-05-03 01:25:44'),
(21, 6, 3, '2026-05-04 12:49:10'),
(22, 9, 34, '2026-05-04 13:06:07'),
(23, 7, 49, '2026-05-05 11:12:39'),
(24, 12, 10, '2026-05-05 11:24:58'),
(25, 13, 33, '2026-05-05 12:17:58'),
(26, 9, 1, '2026-05-08 02:34:14'),
(27, 15, 1, '2026-05-08 02:39:18');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id_client`),
  ADD UNIQUE KEY `idx_nom_unique` (`nom`),
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
-- Index pour la table `discussion`
--
ALTER TABLE `discussion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_discussion_client` (`user_id`),
  ADD KEY `fk_discussion_garagiste` (`garage_id`);

--
-- Index pour la table `entre`
--
ALTER TABLE `entre`
  ADD PRIMARY KEY (`id_entretien`);

--
-- Index pour la table `facture`
--
ALTER TABLE `facture`
  ADD PRIMARY KEY (`id_facture`),
  ADD UNIQUE KEY `ref_facture` (`ref_facture`),
  ADD KEY `fk_entretien` (`entretien`) USING BTREE;

--
-- Index pour la table `garages`
--
ALTER TABLE `garages`
  ADD PRIMARY KEY (`id-garage`);

--
-- Index pour la table `garagiste`
--
ALTER TABLE `garagiste`
  ADD PRIMARY KEY (`id_garagiste`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `historique`
--
ALTER TABLE `historique`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_message_discussion` (`discussion_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `piece`
--
ALTER TABLE `piece`
  ADD PRIMARY KEY (`id_piece`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- Index pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  ADD PRIMARY KEY (`idRDV`),
  ADD KEY `fk_IDvehiculeRDV` (`idVehicule`),
  ADD KEY `fk_idclient_rdv` (`idclientRDV`),
  ADD KEY `fk_service_rdv` (`type_serviceRDV`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id-service`),
  ADD KEY `id_garage` (`id_garage`),
  ADD KEY `nom_service` (`nom_service`);

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
-- Index pour la table `vehicule`
--
ALTER TABLE `vehicule`
  ADD PRIMARY KEY (`idVehicule`),
  ADD UNIQUE KEY `matriculevoiture` (`matriculevoiture`),
  ADD KEY `fk_vehicule_client` (`idclient`),
  ADD KEY `fk_vehicule_nomclient` (`nomclient`);

--
-- Index pour la table `vente`
--
ALTER TABLE `vente`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `client`
--
ALTER TABLE `client`
  MODIFY `id_client` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `client_face`
--
ALTER TABLE `client_face`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `client_signature`
--
ALTER TABLE `client_signature`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `discussion`
--
ALTER TABLE `discussion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `entre`
--
ALTER TABLE `entre`
  MODIFY `id_entretien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT pour la table `facture`
--
ALTER TABLE `facture`
  MODIFY `id_facture` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `garages`
--
ALTER TABLE `garages`
  MODIFY `id-garage` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12350;

--
-- AUTO_INCREMENT pour la table `garagiste`
--
ALTER TABLE `garagiste`
  MODIFY `id_garagiste` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `historique`
--
ALTER TABLE `historique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `piece`
--
ALTER TABLE `piece`
  MODIFY `id_piece` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  MODIFY `idRDV` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id-service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22238;

--
-- AUTO_INCREMENT pour la table `user_journey`
--
ALTER TABLE `user_journey`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT pour la table `user_tracking`
--
ALTER TABLE `user_tracking`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `vehicule`
--
ALTER TABLE `vehicule`
  MODIFY `idVehicule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT pour la table `vente`
--
ALTER TABLE `vente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
-- Contraintes pour la table `discussion`
--
ALTER TABLE `discussion`
  ADD CONSTRAINT `fk_discussion_client` FOREIGN KEY (`user_id`) REFERENCES `client` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_discussion_garagiste` FOREIGN KEY (`garage_id`) REFERENCES `garagiste` (`id_garagiste`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_message_discussion` FOREIGN KEY (`discussion_id`) REFERENCES `discussion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  ADD CONSTRAINT `fk_idclient_rdv` FOREIGN KEY (`idclientRDV`) REFERENCES `client` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_service_rdv` FOREIGN KEY (`type_serviceRDV`) REFERENCES `services` (`nom_service`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_id_garage` FOREIGN KEY (`id_garage`) REFERENCES `garages` (`id-garage`) ON DELETE CASCADE ON UPDATE CASCADE;

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

--
-- Contraintes pour la table `vehicule`
--
ALTER TABLE `vehicule`
  ADD CONSTRAINT `fk_vehicule_client` FOREIGN KEY (`idclient`) REFERENCES `client` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehicule_nomclient` FOREIGN KEY (`nomclient`) REFERENCES `client` (`nom`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
