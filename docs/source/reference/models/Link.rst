Link
====

ThinkUp/webapp/_lib/model/class.Link.php

Copyright (c) 2009-2011 Gina Trapani

Link object


Properties
----------

id
~~

Unique Identifier in storage.

url
~~~

Shortform URL

expanded_url
~~~~~~~~~~~~

Expanded URL

title
~~~~~

Title of target page

clicks
~~~~~~

Click count

post_id
~~~~~~~

ID of the post which this link was found

network
~~~~~~~

Network of the post this link is in

is_image
~~~~~~~~

Link to an image?

error
~~~~~

Error message

img_src
~~~~~~~

Direct image URL

container_post
~~~~~~~~~~~~~~

Container tweet

other
~~~~~

Other values,
i.e. like properties for objects contained within a property of this object



Methods
-------

__construct
~~~~~~~~~~~
* **@param** array $val


Constructor

.. code-block:: php5

    <?php
        public function __construct($val = false) {
            if($val){
                $this->constructValIncluded($val);
            }
            else {
                $this->constructNoVal();
            }
        }


constructValIncluded
~~~~~~~~~~~~~~~~~~~~
* **@param** array $val


Subroutine for construct for when arguments are passed

.. code-block:: php5

    <?php
        private function constructValIncluded($val){
            if (isset($val["url"])) {
                $this->id = $val["id"];
                $this->url = $val["url"];
                if (isset($val["expanded_url"])) {
                    $this->expanded_url = $val["expanded_url"];
                }
    
                if (isset($val["title"])) {
                    $this->title = $val["title"];
                }
    
                if (isset($val["clicks"])) {
                    $this->clicks = $val["clicks"];
                }
    
                if (isset($val["post_id"])) {
                    $this->post_id = $val["post_id"];
                }
    
                if (isset($val["network"])) {
                    $this->network = $val["network"];
                }
    
                $this->is_image = PDODAO::convertDBToBool($val["is_image"]);
    
                if (isset($val["error"])) {
                    $this->error = $val["error"];
                }
            }
        }


constructNoVal
~~~~~~~~~~~~~~

Construct for when no value is passed, i.e. during slipstreaming

.. code-block:: php5

    <?php
        private function constructNoVal(){
            if (isset($this->other['author_user_id'])){
                $this->other['id'] = $this->id;
                $this->other['post_id'] = $this->post_id;
                $this->other['network'] = $this->network;
                $this->container_post = new Post($this->other);
            }
            $this->is_image = PDODAO::convertDBToBool($this->is_image);
        }


__set
~~~~~
* **@param** str $key
* **@param** mixed $val


For overloading when attempting to set undeclared properties

.. code-block:: php5

    <?php
        public function __set($key, $val){
            switch($key){
                default:
                    $this->other[$key] = $val;
            }
        }


addMissingHttp
~~~~~~~~~~~~~~
* **@param** str $url
* **@return** str


If http:// is missing from the beginning of a string which represents a URL, add it.

.. code-block:: php5

    <?php
        public static function addMissingHttp($url) {
            return ((0===stripos($url, 'http')) ? $url : 'http://'.$url);
        }




