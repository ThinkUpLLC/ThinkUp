Dataset
=======

ThinkUp/webapp/_lib/model/class.Dataset.php

Copyright (c) 2009-2011 Gina Trapani

Dataset
Parameters needed to retrieve a set of data to display in ThinkUp.


Properties
----------

name
~~~~



dao_name
~~~~~~~~



dao_method_name
~~~~~~~~~~~~~~~



method_params
~~~~~~~~~~~~~



iterator_method_name
~~~~~~~~~~~~~~~~~~~~



iterator_method_params
~~~~~~~~~~~~~~~~~~~~~~



FETCHING_DAOS
~~~~~~~~~~~~~



help_slug
~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** str $name
* **@param** str $dao_name
* **@param** str $dao_method_name
* **@param** array $method_params
* **@return** Dataset


Constructor

.. code-block:: php5

    <?php
        public function __construct($name, $dao_name, $dao_method_name, $method_params=array(),
        $iterator_method_name = null, $iterator_method_params = array()) {
            $this->name = $name;
            if (in_array($dao_name, $this->FETCHING_DAOS)) {
                $this->dao_name = $dao_name;
                $this->dao_method_name = $dao_method_name;
                $this->method_params = $method_params;
                if( isset($iterator_method_name) ) {
                    $this->iterator_method_name = $iterator_method_name;
                    $this->iterator_method_params = $iterator_method_params;
                }
            } else {
                throw new Exception($dao_name . ' is not one of the allowed DAOs');
            }
        }


retrieveDataset
~~~~~~~~~~~~~~~
* **@param** int $page_number Page number of the list
* **@return** array DAO method results


Retrieve dataset
Run the specified DAO method and return results

.. code-block:: php5

    <?php
        public function retrieveDataset($page_number=1) {
            $dao = DAOFactory::getDAO($this->dao_name);
            if (method_exists($dao, $this->dao_method_name)) {
                $page_pos = array_search('#page_number#', $this->method_params);
                if ($page_pos !== false) {
                    $this->method_params[$page_pos] = $page_number;
                }
                return call_user_func_array(array($dao, $this->dao_method_name), $this->method_params);
            } else {
                throw new Exception($this->dao_name . ' does not have a ' . $this->dao_method_name . ' method.');
            }
        }


isSearchable
~~~~~~~~~~~~
* **@return** boolean


Is this tab searchable
Returns true if there is an Iterator method defined for this tab

.. code-block:: php5

    <?php
        public function isSearchable() {
            return isset($this->iterator_method_name);
        }


retrieveIterator
~~~~~~~~~~~~~~~~
* **@return** PostIterator


Retrieve Iterator
Run the specified DAO Iterator method and return results

.. code-block:: php5

    <?php
        public function retrieveIterator() {
            $dao = DAOFactory::getDAO($this->dao_name);
            $iterator = null;
            if(! is_null($this->iterator_method_name) ) {
                if (method_exists($dao, $this->iterator_method_name)) {
                    $iterator = call_user_func_array(array($dao, $this->iterator_method_name),
                    $this->iterator_method_params);
                } else {
                    throw new Exception($this->dao_name . ' does not have a ' . $this->dao_method_name . ' method.');
                }
            }
            return $iterator;
        }


addHelp
~~~~~~~
* **@param** str $slug


Add a slug which points to the documentation that corresponds to this dataset.

.. code-block:: php5

    <?php
        public function addHelp($slug) {
            $this->help_slug = $slug;
        }


getHelp
~~~~~~~
* **@return** str slug


Get the slug which points to documentation that corresponds to this dataset.

.. code-block:: php5

    <?php
        public function getHelp() {
            return $this->help_slug;
        }




