
--
-- Create user accounts
-- 
INSERT INTO `accounts` (`id`, `login`, `password`) VALUES
(1, 'user',    sha1('useruser')),
(2, 'admin',   sha1('adminadmin')),
(4, 'referee', sha1('refereereferee'));

--
-- Assign Roles to Accounts
--
INSERT INTO `link_accounts_roles` (`account_id`, `role_id`) VALUES
(1, 2), -- "user" has role "guest"
(2, 1); -- "admin" has role "admin"
(4, 4); -- "referee" has role "reviewer"
