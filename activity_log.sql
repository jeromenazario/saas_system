
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `username`    VARCHAR(150) NOT NULL,
  `action`      ENUM('CREATE','UPDATE','DELETE') NOT NULL,
  `entity_type` ENUM('client','subscription') NOT NULL,
  `entity_id`   INT UNSIGNED NOT NULL,
  `description` TEXT NOT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
