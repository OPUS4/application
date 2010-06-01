CREATE TABLE collections_attributes (
   -- Eindeutige ID fuer die Collection und Referenz auf die role_id,
   -- zu der die Collection gehoert.
   id         INT UNSIGNED NOT NULL,
   name       VARCHAR(255),         
   value      VARCHAR(255),  

   --
   -- Constraints.
   --
   FOREIGN KEY(id) REFERENCES collections(id),
   INDEX(id, name)
) ENGINE = InnoDB
CHARACTER SET = 'utf8'
COLLATE = 'utf8_general_ci';
