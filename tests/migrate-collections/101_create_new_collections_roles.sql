DROP TABLE IF EXISTS collections_roles;
CREATE TABLE IF NOT EXISTS collections_roles (
  id                          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.',
  name                        VARCHAR(255) NOT NULL COMMENT 'Name, label or type of the collection role, i.e. a specific classification or conference.',
  oai_name                    VARCHAR(255) NOT NULL COMMENT 'Shortname identifying role in oai context.',

  position                    INT(11) UNSIGNED NOT NULL COMMENT 'Position of this collection tree (role) in the sorted list of collection roles for browsing and administration.' ,
  link_docs_path_to_root      ENUM('none', 'count', 'display', 'both') default 'none' COMMENT 'Every document belonging to a collection C automatically belongs to every collection on the path from C to the root of the collection tree for document counting, document diplaying, none or both.',
  visible                     TINYINT(1) UNSIGNED NOT NULL COMMENT 'Deleted collection trees are invisible. (1=visible, 0=invisible).' ,
  visible_browsing_start      TINYINT(1) UNSIGNED NOT NULL    COMMENT 'Show tree on browsing start page. (1=yes, 0=no).' ,

  display_browsing            VARCHAR(512) NULL               COMMENT 'Comma separated list of collection_contents_x-fields to display in browsing list context.' ,
  visible_frontdoor           TINYINT(1) UNSIGNED NOT NULL    COMMENT 'Show tree on frontdoor. (1=yes, 0=no).' ,
  display_frontdoor           VARCHAR(512) NULL               COMMENT 'Comma separated list of collection_contents_x-fields to display in frontdoor context.' ,

  visible_oai                 TINYINT(1) UNSIGNED NOT NULL    COMMENT 'Show tree in oai output. (1=yes, 0=no).' ,
  display_oai                 VARCHAR(512) NULL               COMMENT 'collection_contents_x-field to display in oai context.' ,

  PRIMARY KEY (id),
  UNIQUE INDEX UNIQUE_NAME     (name ASC),
  UNIQUE INDEX UNIQUE_OAI_NAME (oai_name ASC)
) ENGINE = InnoDB
COMMENT = 'Administration table for the individual collection trees.'
CHARACTER SET = 'utf8'
COLLATE = 'utf8_general_ci';
