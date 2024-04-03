SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,NO_AUTO_VALUE_ON_ZERO';
SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, AUTOCOMMIT=0;
SET @OLD_TIME_ZONE=@@TIME_ZONE, TIME_ZONE = "+00:00";

-- Schema changes

START TRANSACTION;

-- Daten für Tabelle `document_licences`

INSERT INTO `document_licences` (`active`, `name`, `comment_internal`, `desc_markup`, `desc_text`, `language`, `link_licence`, `link_logo`, `link_sign`, `mime_type`, `name_long`, `pod_allowed`, `sort_order`) VALUES
(1, 'In Copyright', NULL, '<a rel="license" href="https://rightsstatements.org/page/InC/1.0/" target="_blank"><img alt="In Copyright" border="0" src="https://rightsstatements.org/files/buttons/InC.dark-white-interior.png" width="100px" height="23px" /></a><br /><br />Dieses Werk ist urheberrechtlich geschützt.</p><p>Dieses Objekt ist durch das Urheberrecht und/oder verwandte Schutzrechte geschützt. Sie sind berechtigt, das Objekt in jeder Form zu nutzen, die das Urheberrechtsgesetz und/oder einschlägige verwandte Schutzrechte gestatten. Für weitere Nutzungsarten benötigen Sie die Zustimmung der/des Rechteinhaber/s.</p><p>Weitere Informationen finden Sie auf diesen Seiten:<br />- <a href="https://rightsstatements.org/page/InC/1.0/" target="_blank">RightsStatements.org / Urheberrechtsschutz</a><br />- <a href="https://www.gesetze-im-internet.de/urhg/" target="_blank">Gesetz über Urheberrecht und verwandte Schutzrechte</a>', NULL, 'deu', 'https://rightsstatements.org/page/InC/1.0/', 'https://rightsstatements.org/files/buttons/InC.dark-white-interior.png', NULL, 'text/html', 'Urheberrechtlich geschützt', 0, 0);

COMMIT;

-- Reset settings

SET TIME_ZONE=@OLD_TIME_ZONE;
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
