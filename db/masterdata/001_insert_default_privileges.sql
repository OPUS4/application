--
-- Insert Default Roles
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- System Roles
--
INSERT INTO privileges (role_id, privilege, document_server_state, file_id) VALUES
(2, 'publish', NULL, NULL),
(2, 'readMetadata', 'published', NULL),
(4, 'clearance', NULL, NULL),
(4, 'readMetadata', 'published', NULL),
(4, 'readMetadata', 'unpublished', NULL);

INSERT INTO access_modules (role_id, module_name, controller_name) VALUES
(1, 'admin', '*'),
(2, 'home', 'index'),
(2, 'default', 'auth'),
(2, 'solrsearch', '*'),
(2, 'publish', '*'),
(2, 'export', 'rss'),
(4, 'review', '*');
