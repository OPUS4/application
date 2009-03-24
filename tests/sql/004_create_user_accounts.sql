
--
-- Create user accounts
-- 
INSERT INTO `accounts` (`id`, `login`, `password`) VALUES
(1, 'user',  md5('useruser')),
(2, 'admin', md5('adminadmin'));

--
-- Assign Roles to Accounts
--
INSERT INTO `link_accounts_roles` (`account_id`, `role_id`) VALUES
(1, 2), -- "user" has role "guest"
(2, 1); -- "admin" has role "adminadmin"
