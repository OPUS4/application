# Rollen anlegen
INSERT INTO `user_roles` (`id`, `name`) VALUES 
(10, 'fulladmin'),
(11, 'licenceadmin'),
(12, 'testuserrole'),
(13, 'docsadmin'),
(14, 'collectionsadmin'),
(15, 'securityadmin');

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
(15, 'resource_accounts');

# Accounts anlegen
INSERT INTO `accounts` (`id`, `login`,`password`,`email`,`first_name`,`last_name`) VALUES
(10, 'security1', sha1('security1pwd'), 'security1@example.org', 'security1', 'Zugriff auf admin Modul'),
(11, 'security2', sha1('security2pwd'), 'security2@example.org', 'security2', 'Zugriff auf Lizenzen'),
(12, 'security3', sha1('security3pwd'), 'security3@example.org', 'security3', 'Zugriff auf Review und nicht Admin Modul'),
(13, 'security4', sha1('security4pwd'), 'security4@example.org', 'security4', 'Zugriff auf Review und Admin Modul'),
(14, 'security5', sha1('security5pwd'), 'security5@example.org', 'security5', 'Zugriff auf Review und Teil von Admin Modul'),
(15, 'security6', sha1('security6pwd'), 'security6@example.org', 'security6', 'Full Admin und Licence Admin Rolle.'),
(16, 'security7', sha1('security7pwd'), 'security7@example.org', 'security7', 'Zugriff auf Account Modul.'),
(17, 'security8', sha1('security8pwd'), 'security8@example.org', 'security8', 'Dokumente editieren.'),
(18, 'security9', sha1('security9pwd'), 'security9@example.org', 'security9', 'Collections editieren.'),
(19, 'security10', sha1('security10pwd'), 'security10@example.org', 'security10', 'Accounts, Rollen, IP Ranges editieren.');

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
(19, 15);

# Dokument fuer Workflow Test anlegen
INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(300, NULL, 2011, NULL, NULL, '2011-09-26', 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2011, '', NULL, '2011-06-04T02:36:53Z', '2011-03-05T09:47:22Z', 'unpublished', NULL, 0);

# Titel fuer Test Dokument anlegen
INSERT INTO `document_title_abstracts` (`id`, `document_id`, `type`, `value`, `language`) VALUES
(400,300,'main','Dokument fuer Workflow ACL TestKOBV','deu');


