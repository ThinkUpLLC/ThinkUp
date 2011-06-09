ThinkUpAuthAPIController
========================
Inherits from `ThinkUpAuthController <./ThinkUpAuthController.html>`_.

ThinkUp/webapp/_lib/controller/class.ThinkUpAuthAPIController.php

Copyright (c) 2009-2011 Guillaume Boudreau

ThinkUp Authorized Controller for API calls
API calls can be made while a valid session is open, or by specifying a username and an API secret in parameters.

Parent controller for all API calls



Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            parent::__construct($session_started);
        }


control
~~~~~~~

Checks if the authorization tokens (username & API secret) are valid or not, and allow the request if they are.
If there are no authorization tokens, the request could be allowed if a valid session is found.

.. code-block:: php5

    <?php
        public function control() {
            if ($this->isAPICallValid()) {
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                Session::completeLogin($owner);
                return $this->authControl();
            }
            $as = $this->getAPISecretFromRequest();
            if (empty($as) && $this->isLoggedIn()) {
                return $this->authControl();
            }
            // Assume if no API key is set, that it's a regular HTML page request
            if (empty($as)) {
                parent::control();
            } else {
                $this->setContentType("text/plain; charset=UTF-8");
                throw new UnauthorizedUserException("Unauthorized API call");
            }
        }


getLoggedInUser
~~~~~~~~~~~~~~~
* **@return** string Username


Return the username specified in the request, or from the session.

.. code-block:: php5

    <?php
        protected function getLoggedInUser() {
            if (isset($_POST['un'])) {
                return $_POST['un'];
            }
            if (isset($_GET['un'])) {
                return $_GET['un'];
            }
            return parent::getLoggedInUser();
        }


getAPISecretFromRequest
~~~~~~~~~~~~~~~~~~~~~~~
* **@return** string $api_secret


Return the API secret specified in the request.

.. code-block:: php5

    <?php
        protected static function getAPISecretFromRequest() {
            return isset($_POST['as']) ? $_POST['as'] : @$_GET['as'];
        }


isAPICallValid
~~~~~~~~~~~~~~
* **@return** boolean Are the provided username and API secret parameters valid?


Checks the username and API secret from the request, and returns true if they match, and are both valid.

.. code-block:: php5

    <?php
        private function isAPICallValid() {
            $logged_in_username = $this->getLoggedInUser();
            $api_secret = self::getAPISecretFromRequest();
            return Session::isAPICallAuthorized($logged_in_username, $api_secret);
        }


getAuthParameters
~~~~~~~~~~~~~~~~~
* **@param** str $username
* **@return** str Parameters to use in a URL to make an API call


Returns URL-encoded parameters needed to make an API call.

.. code-block:: php5

    <?php
        public static function getAuthParameters($username) {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $pwd_from_db = $owner_dao->getPass($username);
            $api_secret = Session::getAPISecretFromPassword($pwd_from_db);
            return 'un='.urlencode($username).'&as='.urlencode($api_secret);
        }


isAPICall
~~~~~~~~~
* **@return** boolean


Checks if the request is an API call, where the username and API secret were specified in the request.

.. code-block:: php5

    <?php
        protected function isAPICall() {
            $as = $this->getAPISecretFromRequest();
            return !empty($as);
        }




