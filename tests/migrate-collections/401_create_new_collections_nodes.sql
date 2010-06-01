CREATE TABLE collections_nodes (
   -- Referenz auf die Collection, die in diesem Knoten eingeblendet
   -- werden soll.  Die Referenz auf die role_id ist eigentlich eine
   -- tree_id.  Da wir aber zu jeder CollectionRole nur einen Baum
   -- erlauben, können wir tree_id und role_id identifizieren.
   id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
   role_id        INT UNSIGNED NOT NULL,
   collection_id  INT UNSIGNED, -- NOT NULL,


   -- Werden benoetigt fuer die Nested-Sets-Datenstruktur.  Die Frage,
   -- welche Kinder ein bestimmter Knoten hat, wird bei uns sehr häufig
   -- vorkommen und ist per NestedSet teuer.  Deshalb führen wir ein
   -- zusätzliches Attribut "parent_id" ein, mit dem sich diese Abfrage
   -- sehr einfach beantworten lässt.
   left_id        INT UNSIGNED NOT NULL,
   right_id       INT UNSIGNED NOT NULL,
   parent_id      INT UNSIGNED,


   -- Visibility des Knotens im Baum muss noch diskutiert werden, da
   -- nicht klar ist was mit seinen Kindern passieren soll.
   visible      TINYINT(1) UNSIGNED NOT NULL,


   -- Constraints und Indexe.  Eigentlich zu viele, aber darüber kann
   -- man sich später immernoch Gedanken machen.  Muss man mal genau
   -- Benchmarken.  Zu prüfen ist weiter, ob MySQL Fremdschlüssel der
   -- Form (x,y) REFERENCES table(x,y) beherscht.
   --
   -- MySQL beherrscht leider keine Constraints der Form left < right.
   UNIQUE (role_id, left_id),
   UNIQUE (role_id, right_id),


   -- Fremdschlüssel auf CollectionsRoles.
   FOREIGN KEY(role_id)           REFERENCES collections_roles(id),
   FOREIGN KEY(collection_id)     REFERENCES collections(id),
   INDEX(id, role_id),

   -- Fremdschlüssel auf sich selbst, damit immer ein gültiger
   -- Vaterknoten sichergestellt ist.
   FOREIGN KEY(parent_id)         REFERENCES collections_nodes(id),
   PRIMARY KEY(id)

) ENGINE = InnoDB
CHARACTER SET = 'utf8'
COLLATE = 'utf8_general_ci';

