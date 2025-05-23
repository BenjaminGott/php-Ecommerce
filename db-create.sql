CREATE TABLE `User` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `username` varchar(255),
  `password` varchar(255),
  `email` varchar(255),
  `balance` decimal,
  `profile_picture` varchar(255),
  `role` varchar(255)
);

CREATE TABLE `Article` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `description` text,
  `price` decimal,
  `published_at` datetime,
  `author_id` int,
  `image_url` varchar(255),
  `categorie` varchar(255)
);

CREATE TABLE `Cart` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int,
  `article_id` int,
  `quantity` int
);

CREATE TABLE `Stock` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `article_id` int,
  `quantity` int
);

CREATE TABLE `Invoice` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int,
  `transaction_date` datetime,
  `amount` decimal,
  `billing_address` text,
  `billing_city` varchar(255),
  `billing_postal_code` varchar(255)
);

CREATE TABLE `Favorite` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int,
  `article_id` int,
  `created_at` datetime
);

CREATE TABLE `Review` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int,
  `article_id` int,
  `rating` int,
  `comment` text,
  `created_at` datetime
);

CREATE TABLE `History` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int,
  `article_id` int,
  `quantity` int,
  `order_date` datetime
);

ALTER TABLE `Article` ADD FOREIGN KEY (`author_id`) REFERENCES `User` (`id`);

ALTER TABLE `Cart` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `Cart` ADD FOREIGN KEY (`article_id`) REFERENCES `Article` (`id`);

ALTER TABLE `Stock` ADD FOREIGN KEY (`article_id`) REFERENCES `Article` (`id`);

ALTER TABLE `Invoice` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `Favorite` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `Favorite` ADD FOREIGN KEY (`article_id`) REFERENCES `Article` (`id`);

ALTER TABLE `Review` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `Review` ADD FOREIGN KEY (`article_id`) REFERENCES `Article` (`id`);

ALTER TABLE `History` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`id`);

ALTER TABLE `History` ADD FOREIGN KEY (`article_id`) REFERENCES `Article` (`id`);
