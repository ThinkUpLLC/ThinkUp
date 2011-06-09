PostIterator
============

ThinkUp/webapp/_lib/model/class.PostIterator.php

Copyright (c) 2009-2011 Mark Wilkie

Post Iterator.

Used to iterate through the cursor of SQL results for Posts.


Properties
----------

stmt
~~~~



row
~~~



valid
~~~~~



closed_cursor
~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~

Contructor

.. code-block:: php5

    <?php
        public function __construct($stmt) {
            $this->stmt = $stmt;
        }


rewind
~~~~~~

Empty method, for this case nothing

.. code-block:: php5

    <?php
        public function rewind() {
            // we can't rewind this stmt, so this won't do anything
        }


current
~~~~~~~
* **@return** Post Current Post


Returns the current row/Post

.. code-block:: php5

    <?php
        public function current() {
            return $this->row;
        }


key
~~~
* **@return** int The current Post id


Returns the current Post key/id

.. code-block:: php5

    <?php
        public function key() {
            return $this->row->id;
        }


valid
~~~~~



.. code-block:: php5

    <?php
        public function valid() {
            $this->valid = false;
            if(! is_null($this->stmt)) {
                $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
                if($row) {
                    $post = new Post($row);
                    $this->row = $post;
                    $this->valid = true;
                } else {
                    // close our cursor...
                    $this->closed_cursor = true;
                    $this->stmt->closeCursor();
                }
            }
            return $this->valid;
        }


next
~~~~

Empty method, for this case does nothing

.. code-block:: php5

    <?php
        public function next() {
            // we handle the row call invalid, so...
        }


__destruct
~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __destruct() {
            // make sure our cursor is closed...
            if(! $this->closed_cursor && isset($this->stmt)) {
                $this->stmt->closeCursor();
            }
        }




