Instance
========

ThinkUp/webapp/_lib/model/class.Instance.php

Copyright (c) 2009-2011 Gina Trapani

Instance - Authorized network user for which ThinkUp archives data.

An instance is a service and user account, i.e., @thinkupapp on Twitter is an instance. The ThinkUp Facebook Page
is also an instance.


Properties
----------

id
~~



network_user_id
~~~~~~~~~~~~~~~



network_viewer_id
~~~~~~~~~~~~~~~~~



network_username
~~~~~~~~~~~~~~~~



last_post_id
~~~~~~~~~~~~



crawler_last_run
~~~~~~~~~~~~~~~~



total_posts_by_owner
~~~~~~~~~~~~~~~~~~~~



total_posts_in_system
~~~~~~~~~~~~~~~~~~~~~



total_replies_in_system
~~~~~~~~~~~~~~~~~~~~~~~



total_follows_in_system
~~~~~~~~~~~~~~~~~~~~~~~



posts_per_day
~~~~~~~~~~~~~



posts_per_week
~~~~~~~~~~~~~~



percentage_replies
~~~~~~~~~~~~~~~~~~



percentage_links
~~~~~~~~~~~~~~~~



earliest_post_in_system
~~~~~~~~~~~~~~~~~~~~~~~



earliest_reply_in_system
~~~~~~~~~~~~~~~~~~~~~~~~



is_archive_loaded_replies
~~~~~~~~~~~~~~~~~~~~~~~~~



is_archive_loaded_follows
~~~~~~~~~~~~~~~~~~~~~~~~~



is_public
~~~~~~~~~



is_active
~~~~~~~~~



network
~~~~~~~



favorites_profile
~~~~~~~~~~~~~~~~~



owner_favs_in_system
~~~~~~~~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($row = false) {
            if ($row) {
                $this->id = $row['id'];
                $this->network_user_id = $row['network_user_id'];
                $this->network_viewer_id = $row['network_viewer_id'];
                $this->network_username = $row['network_username'];
                $this->last_post_id = $row['last_post_id'];
                $this->crawler_last_run = $row['crawler_last_run'];
                $this->total_posts_by_owner = $row['total_posts_by_owner'];
                $this->total_posts_in_system = $row['total_posts_in_system'];
                $this->total_replies_in_system = $row['total_replies_in_system'];
                $this->total_follows_in_system = $row['total_follows_in_system'];
                $this->posts_per_day = $row['posts_per_day'];
                $this->posts_per_week = $row['posts_per_week'];
                $this->percentage_replies = $row['percentage_replies'];
                $this->percentage_links = $row['percentage_links'];
                $this->earliest_post_in_system = $row['earliest_post_in_system'];
                $this->earliest_reply_in_system = $row['earliest_reply_in_system'];
                $this->is_archive_loaded_replies = PDODAO::convertDBToBool($row['is_archive_loaded_replies']);
                $this->is_archive_loaded_follows = PDODAO::convertDBToBool($row['is_archive_loaded_follows']);
                $this->is_public = PDODAO::convertDBToBool($row['is_public']);
                $this->is_active = PDODAO::convertDBToBool($row['is_active']);
                $this->network = $row['network'];
                $this->favorites_profile = $row['favorites_profile'];
                $this->owner_favs_in_system = $row['owner_favs_in_system'];
            }
        }




