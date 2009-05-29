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
-- parent resources for business objects
(10, 2,     'Opus/Collection'),
(11, 2,     'Opus/CollectionRole'),
(12, 2,     'Opus/Configuration'),
(13, 2,     'Opus/Document'),
(14, 2,     'Opus/Enrichment'),
(15, 2,     'Opus/File'),
(16, 2,     'Opus/GPG'),
(17, 2,     'Opus/HashValues'),
(18, 2,     'Opus/Identifier'),
(19, 2,     'Opus/Language'),
(20, 2,     'Opus/Licence'),
(21, 2,     'Opus/Note'),
(22, 2,     'Opus/OrganisationalUnit'),
(23, 2,     'Opus/OrganisationalUnits'),
(24, 2,     'Opus/Patent'),
(25, 2,     'Opus/Person'),
(26, 2,     'Opus/Reference'),
(27, 2,     'Opus/Subject'),
(28, 2,     'Opus/Title'),
(29, 2,     'Opus/Abstract'),
(30, 2,     'Opus/Model/Dependent/Link/DocumentInstitute'),
(31, 2,     'Opus/Model/Dependent/Link/DocumentLicence'),
(32, 2,     'Opus/Model/Dependent/Link/DocumentPerson');

--
-- Roles Privileges
--
INSERT INTO rules (id, role_id, resource_id, privilege, granted) VALUES
(1, 1, null, null,  1), -- grant anything to administrator role
(2, 2, 2, 'create', 1), -- grant create and read to guest role for PUBLIC
(3, 2, 2, 'read',   1);



