-- Create collections necessary for imports

INSERT INTO `collections_roles` (`id`, `name`, `oai_name`, `position`, `visible`, `visible_browsing_start`, `display_browsing`, `visible_frontdoor`, `display_frontdoor`, `visible_oai`) VALUES
  (24, 'Import', 'import', 24, 0, 0, 'Number', 0, 'Number', 0);

INSERT INTO `collections` (`id`, `role_id`, `number`, `name`, `oai_subset`, `left_id`, `right_id`, `parent_id`, `visible`, `visible_publish`) VALUES
  (16217, 24, NULL, NULL, NULL, 1, 4, NULL, 0, 0),
  (16218, 24, 'import', 'Import', 'import', 2, 3, 16217, 0, 0);
