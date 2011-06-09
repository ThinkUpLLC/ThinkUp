LocationMySQLDAO
================
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.LocationMySQLDAO.php

Copyright (c) 2009-2011 Ekansh Preet Singh, Mark Wilkie

Location Data Access Object
The data access object for retrieving and saving locations in the ThinkUp database



Methods
-------

getLocation
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getLocation($location) {
            $q = "SELECT  l.* FROM #prefix#encoded_locations l ";
            $q .= " WHERE l.short_name=:name";
            $vars = array(
                ':name'=>$location,
            );
            $ps = $this->execute($q, $vars);
            $row = $this->getDataRowAsArray($ps);
            return $row;
        }


addLocation
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function addLocation($vals) {
            $q = "INSERT INTO #prefix#encoded_locations SET short_name = :short_name, full_name = :full_name, ";
            $q .= " latlng = :latlng";
            $vars = array(
                ':short_name'=>$vals['short_name'],
                ':full_name'=>$vals['full_name'],
                ':latlng'=>$vals['latlng']
            );
            $ps = $this->execute($q, $vars);
            $logstatus = "Location (".$vals['short_name'].") added to DB";
            $this->logger->logInfo($logstatus, __METHOD__.','.__LINE__);
            return $this->getUpdateCount($ps);
        }


getAllLocations
~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getAllLocations() {
            $q = "SELECT * FROM #prefix#encoded_locations";
            $ps = $this->execute($q);
            return $this->getDataRowsAsArrays($ps);
        }




