INSERT INTO `collections_roles` (`id`, `name`, `oai_name`, `position`, `visible`, `visible_browsing_start`, `display_browsing`, `visible_frontdoor`, `display_frontdoor`, `visible_oai`) VALUES
(10, 'series', 'series', 10, 1, 1, 'Name', 1, 'Name', 1),
(9, 'collections', 'collections', 9, 1, 1, 'Name', 1, 'Name', 1),
(11, 'reports', 'reports', 11, 1, 1, 'Number, Name', 1, 'Number, Name', 1),
(15, 'projects', 'projects', 12, 1, 1, 'Number, Name', 1, 'Number, Name', 1),
(17, 'no-root-test', 'no-root-test', 13, 0, 1, 'Name', 1, 'Name', 1),
(18, 'frontdoor-test-1', 'frontdoor-test-1', 14, 0, 1, 'Name', 1, 'Name', 1),
(19, 'frontdoor-test-2', 'frontdoor-test-2', 15, 0, 1, 'Name', 1, 'Name', 1);

INSERT INTO `collections` (`id`, `role_id`, `number`, `name`, `oai_subset`, `left_id`, `right_id`, `parent_id`, `visible`) VALUES
(1, 1, NULL, NULL, NULL, 1, 2, NULL, 1),
(15982, 9, NULL, NULL, NULL, 1, 2, NULL, 1),
(15983, 10, NULL, NULL, NULL, 1, 2, NULL, 1),
(15984, 11, NULL, NULL, NULL, 1, 2, NULL, 1),
(16202, 18, NULL, NULL, NULL, 1, 2, NULL, 1),
(16203, 19, NULL, NULL, NULL, 1, 2, NULL, 1);