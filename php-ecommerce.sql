-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 26 mai 2025 à 15:46
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
-- Base de données : `php-ecommerce`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,0) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `categorie` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `name`, `description`, `price`, `published_at`, `author_id`, `image_url`, `categorie`) VALUES
(1, 'Tasse en céramique', 'Une tasse artisanale parfaite pour le café.', 13, '2025-05-22 16:00:22', 2, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQZOeKBvrhkFha-z7PrHtUPKWob8TvPS3WZVg&s', 'Maison'),
(2, 'Lampe LED minimaliste', 'Éclairez votre bureau avec style.', 35, '2025-05-22 16:00:22', 2, 'https://lideetoulouse.fr/2864-large_default/lampe-ampoule-led-sans-fil-rainbow-batterie-usb-suck-uk.jpg', 'Électronique'),
(3, 'Sac à dos urbain', 'Compact et pratique pour la ville.', 45, '2025-05-22 00:00:00', 2, 'https://static.kiabi.com/images/sac-a-dos-eastpak-padded-pakr-rouge-coi93_1_hd1.jpg?width=800', 'Mode'),
(4, 'Clavier mécanique RGB', 'Clavier rétroéclairé idéal pour les gamers avec switches tactiles.', 90, '2025-05-22 16:06:44', 2, 'https://m.media-amazon.com/images/I/61pEl7nRJML.jpg', 'Ordinateurs / Tablettes'),
(5, 'Montre en bois écologique', 'Montre faite à la main à partir de bois recyclé.', 60, '2025-05-22 16:06:44', 2, 'https://www.tout-en-bois.fr/wp-content/uploads/2024/08/montre-en-bois-homme-personnalisable-bois-de-noyer-3.jpeg', 'Accessoires de mode'),
(6, 'Tondeuse thermique 4 temps', 'Tondeuse puissante pour entretenir de grands jardins.', 199, '2025-05-22 16:06:44', 2, 'https://m.media-amazon.com/images/I/814NTDPfZNL.jpg', 'Matériel de jardinage'),
(10, 'Unity Reynolds', 'Harum consequuntur v', 569, '2025-05-23 16:23:04', 6, 'Consequatur corporis', 'Produits de beauté / cosmétiques');

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `favorite`
--

CREATE TABLE `favorite` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `favorite`
--

INSERT INTO `favorite` (`id`, `user_id`, `article_id`, `created_at`) VALUES
(6, 5, 10, '2025-05-26 15:25:13');

-- --------------------------------------------------------

--
-- Structure de la table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `history`
--

INSERT INTO `history` (`id`, `user_id`, `article_id`, `quantity`, `order_date`) VALUES
(1, 5, 3, 5, '2025-05-23 14:50:28'),
(2, 5, 3, 20, '2025-05-23 14:56:13'),
(3, 5, 2, 6, '2025-05-23 14:56:13'),
(4, 5, 3, 15, '2025-05-23 14:56:37'),
(5, 5, 3, 11, '2025-05-23 14:58:13'),
(6, 5, 2, 6, '2025-05-23 14:58:13'),
(7, 5, 3, 8, '2025-05-23 15:00:31'),
(8, 5, 2, 7, '2025-05-23 15:08:19'),
(9, 5, 3, 7, '2025-05-23 15:08:19'),
(11, 5, 3, 4, '2025-05-23 15:10:23'),
(12, 7, 10, 3, '2025-05-26 14:47:49'),
(13, 5, 10, 1, '2025-05-26 14:49:05');

-- --------------------------------------------------------

--
-- Structure de la table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `amount` decimal(10,0) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `billing_city` varchar(255) DEFAULT NULL,
  `billing_postal_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `invoice`
--

INSERT INTO `invoice` (`id`, `user_id`, `transaction_date`, `amount`, `billing_address`, `billing_city`, `billing_postal_code`) VALUES
(5, 5, '2025-05-23 14:50:28', 225, 'zedfrghjk', 'azedrftg', '45678'),
(6, 5, '2025-05-23 14:56:13', 1110, '', '', ''),
(7, 5, '2025-05-23 14:56:37', 675, '', '', ''),
(8, 5, '2025-05-23 14:58:13', 705, '', '', ''),
(9, 5, '2025-05-23 15:00:31', 360, 'qsdfg', 'qsdfvgbn', 'qsdcfvb'),
(10, 5, '2025-05-23 15:08:19', 981, 'sdf', 'sdf', 'sd'),
(11, 5, '2025-05-23 15:10:23', 180, 'Dolorem qui cum sunt', 'Quia consequatur eni', 'Veniam dolor neque'),
(12, 7, '2025-05-26 14:47:49', 1707, 'Ipsum cillum archit', 'Nesciunt similique', 'Architecto quae sunt'),
(13, 5, '2025-05-26 14:49:05', 569, 'Labore totam nisi vi', 'Irure omnis velit ex', 'Perspiciatis illum');

-- --------------------------------------------------------

--
-- Structure de la table `review`
--

CREATE TABLE `review` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `review`
--

INSERT INTO `review` (`id`, `user_id`, `article_id`, `rating`, `comment`, `created_at`) VALUES
(1, 7, 10, 2, 'da', '2025-05-26 14:48:30'),
(2, 5, 10, 4, 'az', '2025-05-26 14:49:12');

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `article_id`, `quantity`) VALUES
(1, 1, 100),
(2, 2, 20),
(3, 3, 51),
(6, 10, 56);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `balance` decimal(10,0) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `balance`, `profile_picture`, `role`) VALUES
(2, 'bobdev', 'hashed_pwd_2', 'bob@example.com', 3010, 'bob.jpg', 'auteur'),
(4, 'feur', '$2y$10$1AqKi6LAk7iJpzYbzAtTA.FM4s6CRE9FteyVoPOkOhldN4pDzsup2', 'feur@gmail.com', 100, NULL, 'utilisateur'),
(5, 'ff', '$2y$10$BAZrEExI7ZOP.92f3HW6PugKPIx3D.JlefptGL.c3wt.tx.h5BieO', 'ff@gamil.com', 9999999430, 'https://img.le-dictionnaire.com/moi.jpg', 'user'),
(6, 'admin1', '$2y$10$XzCJaAZn.0ptl04wVpoJKuqWCxumMgg.iQRmlXF9opL18dpcLqoGi', 'admin@admin.com', 9999999999, 'https://img.le-dictionnaire.com/moi.jpg', 'administrateur'),
(7, 'fff', '$2y$10$GZwQqgmbOuTWIROhSA5VGeiyYMjT8h1wkF0nZyhozA.OtaeYgZSna', 'ff@gmail.com', 8293, NULL, 'user');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Index pour la table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `favorite`
--
ALTER TABLE `favorite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `review`
--
ALTER TABLE `review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `article_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `cart_ibfk_4` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Contraintes pour la table `favorite`
--
ALTER TABLE `favorite`
  ADD CONSTRAINT `favorite_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `favorite_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `favorite_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `favorite_ibfk_4` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Contraintes pour la table `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `history_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Contraintes pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `review_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `review_ibfk_4` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `stock_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
