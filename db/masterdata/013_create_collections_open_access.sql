SET @roleid = 8;

INSERT INTO `collections_roles` (`id`, `name`, `oai_name`, `position`, `visible`, `visible_browsing_start`, `display_browsing`, `visible_frontdoor`, `display_frontdoor`, `visible_oai`) VALUES
(@roleid, 'open_access', 'open_access', 100, 1, 0, 'Name', 0, 'Name', 1);

INSERT INTO `collections_roles` (`id`, `name`, `oai_name`, `position`, `visible`, `visible_browsing_start`, `display_browsing`, `visible_frontdoor`, `display_frontdoor`, `visible_oai`) VALUES
(23, 'OpenAIRE', 'openaire', 19, 1, 1, 'Name', 1, 'Name', 1);

INSERT INTO `collections` (`id`, `role_id`, `number`, `name`, `oai_subset`, `left_id`, `right_id`, `parent_id`, `visible`, `visible_publish`) VALUES
(16200, @roleid, NULL, NULL, NULL, 1, 4, NULL, 1, 1),
(16201, @roleid, '', 'open_access', 'open_access', 2, 3, 16200, 1, 1),
(16215, 23, NULL, '', '', 1, 4, NULL, 1, 1),
(16216, 23, NULL, 'OpenAIRE', 'openaire', 2, 3, 16215, 1, 1);
