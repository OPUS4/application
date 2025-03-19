SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,NO_AUTO_VALUE_ON_ZERO';
SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, AUTOCOMMIT=0;
SET @OLD_TIME_ZONE=@@TIME_ZONE, TIME_ZONE = "+00:00";

-- Schema changes

START TRANSACTION;

-- Set internal OPUS version (for controlling updates)

TRUNCATE TABLE `opus_version`;
INSERT INTO `opus_version` (`version`) VALUES (18);

COMMIT;

-- Reset settings

SET TIME_ZONE=@OLD_TIME_ZONE;
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
