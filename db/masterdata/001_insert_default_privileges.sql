--
-- Insert Default Roles
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- System Roles
--
INSERT INTO privileges (id, role_id, privilege, document_server_state, file_id) VALUES
(1, 2, 'publish', NULL, NULL),
(2, 2, 'readMetadata', 'published', NULL),
(5, 4, 'clearance', NULL, NULL);
