--
-- Insert Default Roles
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- System Roles
--
INSERT INTO user_roles (id, name) VALUES
(1, 'administrator'),
(2, 'guest'),
(4, 'reviewer');
