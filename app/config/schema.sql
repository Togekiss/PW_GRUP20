CREATE TABLE IF NOT EXISTS `user` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `birthdate` DATE,
    `password` VARCHAR(255) NOT NULL,
    `img_path` VARCHAR(255),
    `active` TINYINT(1) DEFAULT 0,
    `activate_string` VARCHAR(255) NOT NULL,
    `notifications` INT DEFAULT 0,
    UNIQUE(`username`),
    UNIQUE(`email`)
);

CREATE TABLE IF NOT EXISTS `images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `img_path` VARCHAR(255) NOT NULL,
    `visits` INT DEFAULT 0,
    `private` TINYINT(1) DEFAULT 0,
    `likes` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `image_id` INT NOT NULL,
    `text` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`image_id`) REFERENCES `images`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `likes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `image_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`image_id`) REFERENCES `images`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `notification` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `image_id` INT,
    `like_id` INT,
    `is_like` TINYINT(1) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`image_id`) REFERENCES `images`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`like_id`) REFERENCES `likes`(`id`) ON DELETE CASCADE
);
