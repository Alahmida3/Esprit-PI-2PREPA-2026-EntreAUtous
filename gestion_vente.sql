-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 10 mai 2026 à 01:34
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
-- Base de données : `gestion_vente`
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
(1, 'ben ahmed', 'insaf', 'insaf@gmail.com', '26558074', '$2y$10$Cv8v5lbHUPqbQwCB4keyj.PpU3ZXzPGuHG/acjwxNAj.B0stiJWKm', 'ariana', '2026-05-07 12:00:10'),
(2, 'ben salem', 'rayen', 'rayen.bensalem@esprit.tn', '28140987', '$2y$10$B8YibnWgUoCQ.ErU7gIx9uEgl/UbfFaC85VOmLvtMPfksZxk/DY1.', 'MHAMDIA BEN AROUS', '2026-05-07 22:50:24');

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
(111, 12333, 'RAYEnAUto', 'sidi hassine', 'rayen@bensalem.com', 28140988, '08:00:00', '18:50:00'),
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
(1, 'garagiste@garage.com', '$2y$10$SuImWEoc9P/e59kFtHFCWuulYX/qf9c.kU6drNCnpFF8nrj.PUbpm', 'aryena', 'garagiste', 1, 'admin@garage.com', '2026-05-10 00:11:10');

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
(7, 'MODIFICATION', 'GARAGE', 'Garage \"RAYEnAUto\" modifié.', '2026-05-08 00:53:57', 0);

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
(50, 'Vitres électriques', 'REF050', 'Moteur vitre', 110.00, 21, 'Électrique', 'https://img.freepik.com/photos-premium/boutons-vitres-electriques-avant-arriere-abaisser-fermer-vitres-voiture_176402-8299.jpg', 'Valeo', '2026-04-22 11:31:02');

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
(22229, 111, 'reglage turbo', 350),
(22231, 111, 'Prestations Fréquentes', 1800),
(22232, 111, 'Prestations Fréquentes', 1800),
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
(1, 1, 1, 0, 0, 0, 0, 0, 2, '2026-05-07 13:14:05', 1),
(14, 2, 1, 0, 0, 0, 0, 0, 1, '2026-05-07 22:50:34', 0);

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
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id-service`),
  ADD KEY `id_garage` (`id_garage`);

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
  MODIFY `id_client` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT pour la table `garages`
--
ALTER TABLE `garages`
  MODIFY `id-garage` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12350;

--
-- AUTO_INCREMENT pour la table `garagiste`
--
ALTER TABLE `garagiste`
  MODIFY `id_garagiste` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `historique`
--
ALTER TABLE `historique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `piece`
--
ALTER TABLE `piece`
  MODIFY `id_piece` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id-service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22238;

--
-- AUTO_INCREMENT pour la table `user_journey`
--
ALTER TABLE `user_journey`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `user_tracking`
--
ALTER TABLE `user_tracking`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
