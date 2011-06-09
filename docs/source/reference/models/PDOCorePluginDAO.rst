PDOCorePluginDAO
================
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.PDOCorePluginDAO.php

Copyright (c) 2011 Gina Trapani

PDO Core/Plugin DAO

Provides support methods for selecting plugin-specific fields in addition to core data fiels without rewritng SQL.


Properties
----------

object_name
~~~~~~~~~~~



table_name
~~~~~~~~~~



meta_table_name
~~~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** str $object_name
* **@param** str $table_name
* **@param** str $meta_table_name
* **@return** PDOCorePluginDAO


Define the object name to return, the core table name, and the meta/plugin table name.

.. code-block:: php5

    <?php
        public function __construct($object_name, $table_name, $meta_table_name=null) {
            $this->object_name = $object_name;
            $this->table_name = $table_name;
            $this->meta_table_name = $meta_table_name;
        }


setObjectName
~~~~~~~~~~~~~
* **@param** str $object_name


Set the object name.

.. code-block:: php5

    <?php
        protected function setObjectName($object_name) {
            $this->object_name = $object_name;
        }


setMetaTableName
~~~~~~~~~~~~~~~~
* **@param** str $meta_table_name


Set the meta table name.

.. code-block:: php5

    <?php
        protected function setMetaTableName($meta_table_name) {
            $this->meta_table_name = $meta_table_name;
        }


getFieldList
~~~~~~~~~~~~
* **@return** str


Get string listing all the fields to select from both core and plugin table.

.. code-block:: php5

    <?php
        protected function getFieldList() {
            $field_list = "";
            $obj = new $this->object_name;
            $fields = get_object_vars($obj);
            foreach ($fields as $field=>$value) {
                //preface id field with table name to avoid ambiguity
                $field_list .= ($field == 'id')? $this->getTableName().".id": ", ".$field;
            }
            return $field_list;
        }


getTableName
~~~~~~~~~~~~
* **@return** str


Get table name with dynamic prefix.

.. code-block:: php5

    <?php
        protected function getTableName() {
            return "#prefix#".$this->table_name;
        }


getMetaTableName
~~~~~~~~~~~~~~~~
* **@return** str


Get meta table name with dynamic prefix.

.. code-block:: php5

    <?php
        protected function getMetaTableName() {
            if (isset($this->meta_table_name)) {
                return "#prefix#".$this->meta_table_name;
            } else {
                return "";
            }
        }


getMetaTableJoin
~~~~~~~~~~~~~~~~
* **@return** str


Get the join definition on the meta plugin table.

.. code-block:: php5

    <?php
        protected function getMetaTableJoin() {
            $join = "";
            if (isset($this->meta_table_name)) {
                $join .= "LEFT JOIN ".$this->getMetaTableName()." on ".$this->getTableName(). ".id=".
                $this->getMetaTableName().".id ";
            }
            return $join;
        }


doesMetaDataExist
~~~~~~~~~~~~~~~~~
* **@param** int $id
* **@return** bool


Check whether or not a row in the metadata table exists.

.. code-block:: php5

    <?php
        public function doesMetaDataExist($id) {
            $q  = "SELECT id ";
            $q .= "FROM ".$this->getMetaTableName()." ";
            $q .= "WHERE id=:id";
            $vars = array(
                ':id'=>$id
            );
            $ps = $this->execute($q, $vars);
            return $this->getDataIsReturned($ps);
        }




