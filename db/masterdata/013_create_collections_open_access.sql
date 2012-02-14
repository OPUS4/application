SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

INSERT INTO `collections` (`id`, `role_id`, `number`, `name`, `oai_subset`, `sort_order`, `left_id`, `right_id`, `parent_id`, `visible`) VALUES
(16200, 8, NULL, NULL, NULL, 0, 1, 4, NULL, 1),
(16201, 8, '', 'open_access', 'open_access', 0, 2, 3, 16200, 1);

INSERT INTO `collections_roles` (`id`, `name`, `oai_name`, `position`, `visible`, `visible_browsing_start`, `display_browsing`, `visible_frontdoor`, `display_frontdoor`, `visible_oai`) VALUES
(8, 'open_access', 'open_access', 8, 1, 0, 'Name', 0, 'Name', 1);

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
