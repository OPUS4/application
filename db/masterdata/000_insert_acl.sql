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


--
-- System Resources for Opus_Model
--
INSERT INTO resources (id, parent_id, name) VALUES
-- root resource
( 1, NULL,  'SYSTEM'),
-- resource grouping public resources
( 2, 1,     'PUBLIC'),
-- resources for business objects
(10, 2,     'Opus/Configuration'),
(11, 2,     'Opus/Document'),
(12, 2,     'Opus/Licence'),
(13, 2,     'Opus/Person');
--
-- Roles Privileges
--
INSERT INTO privileges (id, role_id, resource_id, privilege, granted) VALUES
(1, 1, null, null,  1), -- grant anything to administrator role
(2, 2, 2, 'create', 1), -- grant create and read to guest role for PUBLIC
(3, 2, 2, 'read',   1);



