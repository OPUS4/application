#!/bin/sh
# dieses Script muss als www-data aufgerufen werden, da sonst einige der Zend-Cachedateien
# unter /tmp unterschiedliche Besitzer haben (entweder vom ausführender Benutzer oder von www-data)
# wenn das passiert, erhält man die folgende Warning vom Zend-Framework: Failed saving metadata to metadataCache
sudo -u www-data php5 SolrIndexBuilder.php "$@"