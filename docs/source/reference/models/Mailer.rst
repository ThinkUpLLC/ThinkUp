Mailer
======

ThinkUp/webapp/_lib/model/class.Mailer.php

Copyright (c) 2009-2011 Gina Trapani





Methods
-------

mail
~~~~
* **@param** str $to A valid email address
* **@param** str $subject
* **@param** str $message


Send email from ThinkUp instalation. If you're running tests, just write the message headers and contents to
the file system in the compiled_view folder.

.. code-block:: php5

    <?php
        public static function mail($to, $subject, $message) {
            $config = Config::getInstance();
    
            $app_title = $config->getValue('app_title');
            $host = self::getHost();
    
            $mail_header = "From: \"{$app_title}\" <notifications@{$host}>\r\n";
            $mail_header .= "X-Mailer: PHP/".phpversion();
    
            //don't send email when running tests, just write it to the filesystem for assertions
            if ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS") {
                $test_email = THINKUP_WEBAPP_PATH . '_lib/view/compiled_view' . Mailer::EMAIL;
                $fp = fopen($test_email, 'w');
                fwrite($fp, $mail_header."\n");
                fwrite($fp, "to: $to\n");
                fwrite($fp, "subject: $subject\n");
                fwrite($fp, "message: $message");
                fclose($fp);
                return $message;
            } else {
                mail($to, $subject, $message, $mail_header);
            }
        }


getHost
~~~~~~~
* **@return** str Host name


Return the current host's name, ie, $_SERVER['HTTP_HOST'] if it is set.

.. code-block:: php5

    <?php
        private static function getHost() {
            if (isset($_SERVER['HTTP_HOST'])) {
                return $_SERVER['HTTP_HOST'];
            } else {
                return "";
            }
        }


getLastMail
~~~~~~~~~~~
* **@return** str The contents of the last email sent


Return the contents of the last email Mailer "sent" out.
For testing purposes only; this will return nothing in production.

.. code-block:: php5

    <?php
        public static function getLastMail() {
            $test_email_file = THINKUP_WEBAPP_PATH . '_lib/view/compiled_view' . Mailer::EMAIL;
            if(file_exists($test_email_file)) {
                return file_get_contents($test_email_file);
            } else {
                return '';
            }
        }




