# Rollen anlegen
INSERT INTO `user_roles` (`id`, `name`) VALUES 
(10, 'fulladmin'),
(11, 'licenceadmin'),
(12, 'testuserrole');

# Rollen mit Rechten verknüpfen
INSERT INTO `access_modules` (`role_id`, `module_name`) VALUES 
(10, 'admin'),
(11, 'admin'),
(11, 'resource_licences'),
(12, 'account');

# Accounts anlegen
INSERT INTO `accounts` (`id`, `login`,`password`,`email`,`first_name`,`last_name`) VALUES
(10, 'security1', sha1('security1pwd'), 'security1@example.org', 'security1', 'Zugriff auf admin Modul'),
(11, 'security2', sha1('security2pwd'), 'security2@example.org', 'security2', 'Zugriff auf Lizenzen'),
(12, 'security3', sha1('security3pwd'), 'security3@example.org', 'security3', 'Zugriff auf Review und nicht Admin Modul'),
(13, 'security4', sha1('security4pwd'), 'security4@example.org', 'security4', 'Zugriff auf Review und Admin Modul'),
(14, 'security5', sha1('security5pwd'), 'security5@example.org', 'security5', 'Zugriff auf Review und Teil von Admin Modul'),
(15, 'security6', sha1('security6pwd'), 'security6@example.org', 'security6', 'Full Admin und Licence Admin Rolle.'),
(16, 'security7', sha1('security7pwd'), 'security7@example.org', 'security7', 'Access to Account Module.');

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
(16, 12);

