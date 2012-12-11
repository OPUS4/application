# Accounts anlegen
INSERT INTO `accounts` (`id`, `login`,`password`,`email`,`first_name`,`last_name`) VALUES
(10, 'security1', sha1('security1pwd'), 'security1@example.org', 'security1', 'Zugriff auf admin Modul'),
(11, 'security2', sha1('security2pwd'), 'security1@example.org', 'security2', 'Zugriff auf Lizenzen'),
(12, 'security3', sha1('security3pwd'), 'security1@example.org', 'security3', 'Zugriff auf Review und nicht Admin Modul'),
(13, 'security4', sha1('security4pwd'), 'security1@example.org', 'security4', 'Zugriff auf Review und Admin Modul');

# Rollen anlegen
INSERT INTO `user_roles` (`id`, `name`) VALUES 
(10, 'fulladmin'),
(11, 'licenceadmin');

# Accounts und Rollen verknüpfen
INSERT INTO `link_accounts_roles` (`account_id`, `role_id`) VALUES
(10, 10),
(11, 11);

# Rollen mit Rechten verknüpfen
INSERT INTO `access_modules` (`role_id`, `module_name`) VALUES 
(10, 'admin'),
(11, 'admin'),
(11, 'resource_licences');