# Rollen anlegen
# beachte, dass Werte des Attributs 'name' nur Ziffern und Buchstaben enthalten dürfen
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

INSERT INTO `user_roles` (`id`, `name`) VALUES
(10, 'fulladmin'),
(11, 'licenceadmin'),
(12, 'testuserrole'),
(13, 'docsadmin'),
(14, 'collectionsadmin'),
(15, 'securityadmin'),
(16, 'accesstest'),
(17, 'setupmoduleaccess'),
(18, 'helppagecontrolleraccess'),
(19, 'staticpagecontrolleraccess'),
(20, 'translationcontrolleraccess'),
(21, 'indexmaintenanceaccess'),
(22, 'jobaccess'),
(23, 'sworduser');

# Rollen mit Rechten verknüpfen
INSERT INTO `access_modules` (`role_id`, `module_name`) VALUES 
(10, 'admin'),
(11, 'admin'),
(11, 'resource_licences'),
(12, 'account'),
(13, 'admin'),
(13, 'resource_documents'),
(13, 'workflow_unpublished_published'),
(13, 'workflow_published_restricted'),
(13, 'workflow_unpublished_deleted'),
(14, 'admin'),
(14, 'resource_collections'),
(15, 'admin'),
(15, 'resource_security'),
(15, 'resource_accounts'),
(16, 'admin'),
(16, 'account'),
(16, 'resource_collections'),
(16, 'workflow_unpublished_published'),
(17, 'setup'), -- darf auf Modul setup und alle Controller zugreifen
(18, 'setup'),
(18, 'resource_helppages'),    -- darf nur auf Controller helpPage im Modul setup zugreifen
(19, 'setup'),
(19, 'resource_staticpages'),  -- darf nur auf Controller staticPage im Modul setup zugreifen
(20, 'setup'), 
(20, 'resource_translations'), -- darf nur auf Controller language im Modul setup zugreifen
(21, 'admin'),
(21, 'resource_indexmaintenance'),
(22, 'admin'),
(22, 'resource_job'),
(23, 'sword');

# Accounts anlegen
INSERT INTO `accounts` (`id`, `login`,`password`,`email`,`first_name`,`last_name`) VALUES
(10, 'security1', sha1('security1pwd'), 'security1@example.org', 'security1', 'Zugriff auf Admin Modul'),
(11, 'security2', sha1('security2pwd'), 'security2@example.org', 'security2', 'Zugriff auf Lizenzen'),
(12, 'security3', sha1('security3pwd'), 'security3@example.org', 'security3', 'Zugriff auf Review und nicht Admin Modul'),
(13, 'security4', sha1('security4pwd'), 'security4@example.org', 'security4', 'Zugriff auf Review und Admin Modul'),
(14, 'security5', sha1('security5pwd'), 'security5@example.org', 'security5', 'Zugriff auf Review und Teil von Admin Modul'),
(15, 'security6', sha1('security6pwd'), 'security6@example.org', 'security6', 'Full Admin und Licence Admin Rolle.'),
(16, 'security7', sha1('security7pwd'), 'security7@example.org', 'security7', 'Zugriff auf Account Modul.'),
(17, 'security8', sha1('security8pwd'), 'security8@example.org', 'security8', 'Dokumente editieren.'),
(18, 'security9', sha1('security9pwd'), 'security9@example.org', 'security9', 'Collections editieren.'),
(19, 'security10', sha1('security10pwd'), 'security10@example.org', 'security10', 'Accounts, Rollen, IP Ranges editieren.'),
(20, 'security11', sha1('security11pwd'), 'security11@example.org', 'security11', 'Zugriff auf Admin und Setup Modul'),
(21, 'security12', sha1('security12pwd'), 'security12@example.org', 'security12', 'Zugriff auf Setup Modul'),
(22, 'security13', sha1('security13pwd'), 'security13@example.org', 'security13', 'Zugriff auf Controller HelpPage im Setup Modul'),
(23, 'security14', sha1('security14pwd'), 'security14@example.org', 'security14', 'Zugriff auf Controller StaticPage im Setup Modul'),
(24, 'security15', sha1('security15pwd'), 'security15@example.org', 'security15', 'Zugriff auf Controller Language im Setup Modul'),
(25, 'security16', sha1('security16pwd'), 'security16@example.org', 'security16', 'Zugriff auf Controller HelpPage im Setup Modul und Admin Modul'),
(26, 'security17', sha1('security17pwd'), 'security17@example.org', 'security17', 'Zugriff auf Controller StaticPage im Setup Modul und Admin Modul'),
(27, 'security18', sha1('security18pwd'), 'security18@example.org', 'security18', 'Zugriff auf Controller Language im Setup Modul und Admin Modul'),
(28, 'security19', sha1('security19pwd'), 'security19@example.org', 'security19', 'Zugriff auf Solr-Verwaltung'),
(29, 'security20', sha1('security20pwd'), 'security20@example.org', 'security20', 'Zugriff auf Jobverwaltung'),
(30, 'sworduser', sha1('sworduserpwd'), 'sworduser@example.org', 'sworduser', 'Test user for sword access');

# Accounts und Rollen verknüpfen
INSERT INTO `link_accounts_roles` (`account_id`, `role_id`) VALUES
(10, 10),
(11, 11),
(12, 4),
(13, 4),
(13, 10),
(14, 4),
(14, 11),
(15, 10),
(15, 11),
(16, 12),
(17, 13),
(18, 14),
(19, 15),
(20, 10),
(20, 17),
(21, 17),
(22, 18),
(23, 19),
(24, 20),
(25, 18),
(25, 10),
(26, 19),
(26, 10),
(27, 20),
(27, 10),
(28, 21),
(29, 22),
(30, 23);

# Dokument fuer Workflow Test anlegen
INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(300, NULL, 2011, NULL, NULL, '2011-09-26', 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2011, '', NULL, '2011-06-04T02:36:53Z', '2011-03-05T09:47:22Z', 'unpublished', NULL, 0);

# Titel fuer Test Dokument anlegen
INSERT INTO `document_title_abstracts` (`id`, `document_id`, `type`, `value`, `language`) VALUES
(400,300,'main','Dokument fuer Workflow ACL TestKOBV','deu');


