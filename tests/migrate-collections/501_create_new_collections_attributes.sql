CREATE TABLE collections_enrichments (
   -- Eindeutige ID fuer die Collection und Referenz auf die role_id,
   -- zu der die Collection gehoert.
   id            INT UNSIGNED NOT NULL,
   collection_id INT(10) unsigned NOT NULL,                                                                                                                                                                         
   `key`         VARCHAR(255),
   `value`       VARCHAR(255),

   --
   -- Constraints.
   --
   FOREIGN KEY(collection_id)     REFERENCES collections(id),
   PRIMARY KEY(id),
   INDEX(id, name)
) ENGINE = InnoDB
CHARACTER SET = 'utf8'
COLLATE = 'utf8_general_ci';
