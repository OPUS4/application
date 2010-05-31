CREATE TABLE collections (
   -- Referenz auf die Collection, die in diesem Knoten eingeblendet
   -- werden soll.  Die Referenz auf die role_id ist eigentlich eine
   -- tree_id.  Da wir aber zu jeder CollectionRole nur einen Baum
   -- erlauben, können wir tree_id und role_id identifizieren.
   id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
   role_id        INT UNSIGNED NOT NULL,


   -- Generische Attribute einer Collection.  Ob die wirklich benötigt
   -- werden, wenn die Attribute-Tabelle existiert, lässt sich noch
   -- streiten.  Visibility des Knotens im Baum muss noch diskutiert
   -- werden, da nicht klar ist was mit seinen Kindern passieren soll.
   number         VARCHAR(255),
   name           VARCHAR(255),
   subset_key     VARCHAR(255),

   address        VARCHAR(255),
   city           VARCHAR(255),
   phone          VARCHAR(255),
   dnb_contact_id VARCHAR(255),
   is_grantor     VARCHAR(255),

   -- Fremdschlüssel auf CollectionsRoles.
   INDEX(role_id, id),
   FOREIGN KEY(role_id)           REFERENCES collections_roles(id),
   PRIMARY KEY(id)

) ENGINE = InnoDB
CHARACTER SET = 'utf8'
COLLATE = 'utf8_general_ci';
