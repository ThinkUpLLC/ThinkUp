MenuItem
========

ThinkUp/webapp/_lib/model/class.MenuItem.php

Copyright (c) 2009-2011 Gina Trapani

Menu Item
Sidebar menu item, contains datasets to render in the view.


Properties
----------

name
~~~~



description
~~~~~~~~~~~



datasets
~~~~~~~~



view_template
~~~~~~~~~~~~~



header
~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** str $name
* **@param** str $description
* **@param** str $view_template
* **@return** MenuItem


Constructor

.. code-block:: php5

    <?php
        public function __construct($name, $description='', $view_template='inline.view.tpl', $header=null) {
            $this->name = $name;
            $this->description = $description;
            $this->view_template = $view_template;
            $this->header = $header;
        }


addDataset
~~~~~~~~~~
* **@param** MenuItemDataset $dataset


Add dataset

.. code-block:: php5

    <?php
        public function addDataset($dataset) {
            if (get_class($dataset) == 'Dataset') {
                array_push($this->datasets, $dataset);
            } else {
                //throw exception here?
            }
        }


getDatasets
~~~~~~~~~~~
* **@return** array MenuItemDatasets


Get datasets

.. code-block:: php5

    <?php
        public function getDatasets() {
            return $this->datasets;
        }




