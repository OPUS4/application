-- Es muss sichergestellt werden, dass diese Änderungen auch beim einem Update auf
-- die Version 4.5 auf der Instanz eingespielt werden

INSERT INTO `enrichmentkeys` (`name`) VALUES
('opus.import.checksum'),
('opus.import.date'),
('opus.import.file'),
('opus.import.user');

INSERT INTO `collections_roles` (`id`, `name`, `oai_name`, `position`, `visible`, `visible_browsing_start`, `display_browsing`, `visible_frontdoor`, `display_frontdoor`, `visible_oai`) VALUES
(24, 'Import', '', 24, 0, 0, 'Number', 0, 'Number', 0);

INSERT INTO `collections` (`id`, `role_id`, `number`, `name`, `oai_subset`, `left_id`, `right_id`, `parent_id`, `visible`, `visible_publish`) VALUES
(16217, 24, NULL, NULL, NULL, 1, 2, NULL, 1, 1);