PostDetailPlugin
================

ThinkUp/webapp/_lib/model/interface.PostDetailPlugin.php

Copyright (c) 2009-2011 Gina Trapani

Post detail page plugin interface



Methods
-------

getPostDetailMenuItems
~~~~~~~~~~~~~~~~~~~~~~
* **@param** $post Post
* **@return** array of Menu objects (Tweets, Friends, Followers, etc)


Get Post Detail menu

.. code-block:: php5

    <?php
        public function getPostDetailMenuItems($post);




