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
