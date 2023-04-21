-- -------------------------------------------------------------
-- TablePlus 3.12.8(368)
--
-- https://tableplus.com/
--
-- Database: lotr
-- Generation Time: 2021-06-10 16:54:30.2900
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP DATABASE IF EXISTS `lotr`;
CREATE DATABASE `lotr` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `lotr`;

DROP TABLE IF EXISTS `factions`;
CREATE TABLE `factions` (
                            `id` int NOT NULL AUTO_INCREMENT,
                            `faction_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `leader` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `equipments`;
CREATE TABLE `equipments` (
                             `id` int NOT NULL AUTO_INCREMENT,
                             `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `type` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `made_by` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `character_id` int NOT NULL,
                             PRIMARY KEY (`id`),
                             KEY `character_id` (`character_id`),
                             CONSTRAINT `equipments_ibfk_1` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `characters`;
CREATE TABLE `characters` (
                             `id` int NOT NULL AUTO_INCREMENT,
                             `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `birth_date` date NOT NULL,
                             `kingdom` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                             `equipment_id` int NOT NULL,
                             `faction_id` int NOT NULL,
                             PRIMARY KEY (`id`),
                             KEY `equipment_id` (`equipment_id`),
                             KEY `faction_id` (`faction_id`),
                             CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipments` (`id`),
                             CONSTRAINT `characters_ibfk_2` FOREIGN KEY (`faction_id`) REFERENCES `factions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





INSERT INTO `factions` (`id`, `faction_name`, `description`, `leader`) VALUES
                                                                        (1, 'MORDOR', 'Mordor es un país situado al sureste de la Tierra Media, que tuvo gran importancia durante la Guerra del Anillo por ser el lugar donde Sauron, el Señor Oscuro, decidió edificar su fortaleza de Barad-dûr para intentar atacar y dominar a todos los pueblos de la Tierra Media.', 'SAURON');

INSERT INTO `equipments` (`id`, `name`, `type`, `made_by`, `character_id`) VALUES
                                                                        (1, 'Maza de Sauron', 'arma', 'desconocido', 1);

INSERT INTO `characters` (`id`, `name`, `birth_date`, `kingdom`, `equipment_id`, `faction_id`) VALUES
                                                                        (1, 'SAURON', '3019-03-25', 'AINUR', 1, 1);







/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;