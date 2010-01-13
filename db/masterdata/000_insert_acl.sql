--
-- Insert Access Control List data
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- System Roles
--
INSERT INTO roles (id, parent, name) VALUES
(1, NULL, 'administrator'),
(2, NULL, 'guest');
