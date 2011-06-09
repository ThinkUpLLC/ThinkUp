LocationDAO
===========

ThinkUp/webapp/_lib/model/interface.LocationDAO.php

Copyright (c) 2009-2011 Ekansh Preet Singh, Mark Wilkie

Location Data Access Object Interface



Methods
-------

getLocation
~~~~~~~~~~~
* **@param** str $location
* **@return** array Details of Location


Returns a given 'location' existing in storage.

.. code-block:: php5

    <?php
        public function getLocation($location);


addLocation
~~~~~~~~~~~
* **@param** array Details of Location
* **@return** int update count


Adds a location to DB

.. code-block:: php5

    <?php
        public function addLocation($vals);


getAllLocations
~~~~~~~~~~~~~~~
* **@return** array Details of Locations


Returns all locations in table

.. code-block:: php5

    <?php
        public function getAllLocations();




