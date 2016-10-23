
--
-- Create user accounts
-- 
INSERT INTO `accounts` (`id`, `login`, `password`) VALUES
(1, 'admin',   sha1('adminadmin'));

--
-- Assign Roles to Accounts
--
INSERT INTO `link_accounts_roles` (`account_id`, `role_id`) VALUES
(1, 1); -- "admin" has role "admin"
