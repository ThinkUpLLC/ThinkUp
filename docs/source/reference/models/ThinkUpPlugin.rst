ThinkUpPlugin
=============

ThinkUp/webapp/_lib/model/interface.ThinkUpPlugin.php

Copyright (c) 2009-2011 Gina Trapani

ThinkUp Plugin interface



Methods
-------

renderConfiguration
~~~~~~~~~~~~~~~~~~~
* **@param** Owner $owner
* **@return** str HTML markup of configuration panel


Render the configuration screen in the webapp

.. code-block:: php5

    <?php
        public function renderConfiguration($owner);


activate
~~~~~~~~

Activation callback, triggered when user deactivates plugin.

.. code-block:: php5

    <?php
        public function activate();


deactivate
~~~~~~~~~~

Deactivation callback, triggered when user deactivates plugin.

.. code-block:: php5

    <?php
        public function deactivate();




