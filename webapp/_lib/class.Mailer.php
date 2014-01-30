<?php
/**
 *
 * ThinkUp/webapp/_lib/class.Mailer.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
class Mailer {
    /**
     * For testing purposes only; this is the name of the file the latest email gets written to.
     * @var str
     */
    const EMAIL = '/latest_email';
    /**
     * Send email from ThinkUp installation. Will attempt to send via Mandrill if the key has been set.
     * If you're running tests, just write the message headers and contents to
     * the file system in the data directory.
     * @param str $to A valid email address
     * @param str $subject
     * @param str $message
     */
    public static function mail($to, $subject, $message) {
        $config = Config::getInstance();
        $mandrill_api_key = $config->getValue('mandrill_api_key');

        if (isset($mandrill_api_key) && $mandrill_api_key != '') {
            self::mailViaMandrill($to, $subject, $message);
        } else {
            self::mailViaPHP($to, $subject, $message);
        }
    }
    /**
     * Send an HTML email from ThinkUp installation. Will only be sent if a Mandrill API key is set, as it makes use of
     * Mandrill's HTML templating system and API.
     * @param str $to A valid email address
     * @param str $subject Subject of the email
     * @param str $template_name Name of a template in the mandrill system
     * @param arr $template_params Associative array of parameters
     */
    public static function mailHTMLViaMandrillTemplate($to, $subject, $template_name, $template_params) {
        $config = Config::getInstance();
        $host = Utils::getApplicationHostName();
        $app_title = $config->getValue('app_title_prefix'). "ThinkUp";
        $mandrill_api_key = $config->getValue('mandrill_api_key');
        if (Utils::isThinkUpLLC()) {
            $from_email = 'team@thinkup.com';
        } else {
            $from_email = "notifications@${host}";
        }

        try {
            require_once THINKUP_WEBAPP_PATH.'_lib/extlib/mandrill/Mandrill.php';
            $mandrill = new Mandrill($mandrill_api_key);
            $message = array('subject' => $subject, 'from_email' => $from_email,
            'from_name' => $app_title, 'to' => array( array( 'email' => $to, 'name' => $to ) ),
            'global_merge_vars' => array());

            foreach ($template_params as $key=>$val) {
                $message['global_merge_vars'][] = array('name'=>$key, 'content'=>$val);
            }

            //don't send email when running tests, just write it to the filesystem for assertions
            if (Utils::isTest()) {
                self::setLastMail(json_encode($message));
                if (preg_match('/keyerror/', $to)) {
                    throw new Mandrill_Invalid_Key('Invalid api key');
                } elseif (preg_match('/templateerror/', $to)) {
                    throw new Mandrill_Unknown_Template('Unknown template');
                }
            } else {
                $async = false;
                $ip_pool = 'Main Pool';
                $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message,
                $async, $ip_pool);
            }
        } catch (Mandrill_Unknown_Template $unknown_template_error) {
            // We want to be able to handle this specific error differently.
            throw $unknown_template_error;
        } catch (Mandrill_Error $e) {
            throw new Exception('An error occurred while sending email to '.$to.' from '.$from.' via Mandrill. '
            . get_class($e) . ': ' . $e->getMessage());
        }
    }
    /**
     * Return the contents of the last email Mailer "sent" out.
     * For testing purposes only; this will return nothing in production.
     * @return str The contents of the last email sent
     */
    public static function getLastMail() {
        $test_email_file = FileDataManager::getDataPath(Mailer::EMAIL);
        if (file_exists($test_email_file)) {
            return file_get_contents($test_email_file);
        } else {
            return '';
        }
    }
    /**
     * Return the contents of the last email Mailer "sent" out.
     * For testing purposes only; this will return nothing in production.
     * @return str The contents of the last email sent
     */
    private static function setLastMail($message) {
        $test_email = FileDataManager::getDataPath(Mailer::EMAIL);
        $fp = fopen($test_email, 'w');
        fwrite($fp, $message);
        fclose($fp);
    }
    /**
     * Send email from ThinkUp installation via PHP's built-in mail() function.
     * If you're running tests, just write the message headers and contents to the file system in the data directory.
     * @param str $to A valid email address
     * @param str $subject
     * @param str $message
     */
    public static function mailViaPHP($to, $subject, $message) {
        $config = Config::getInstance();

        $app_title = $config->getValue('app_title_prefix'). "ThinkUp";
        $host = Utils::getApplicationHostName();

        $mail_header = "From: \"{$app_title}\" <notifications@{$host}>\r\n";
        $mail_header .= "X-Mailer: PHP/".phpversion();

        //don't send email when running tests, just write it to the filesystem for assertions
        if (Utils::isTest()) {
            self::setLastMail($mail_header."\n" .
                                "to: $to\n" .
                                "subject: $subject\n" .
                                "message: $message");
        } else {
            mail($to, $subject, $message, $mail_header);
        }
    }
    /**
     * Send email from ThinkUp installation via Mandrill's API.
     * If you're running tests, just write the message headers and contents to the file system in the data directory.
     * @param str $to A valid email address
     * @param str $subject
     * @param str $message
     */
    public static function mailViaMandrill($to, $subject, $message) {
        $config = Config::getInstance();

        $app_title = $config->getValue('app_title_prefix') . "ThinkUp";
        $host = Utils::getApplicationHostName();
        $mandrill_api_key = $config->getValue('mandrill_api_key');
        if (Utils::isThinkUpLLC()) {
            $from_email = 'team@thinkup.com';
        } else {
            $from_email = "notifications@${host}";
        }

        try {
            require_once THINKUP_WEBAPP_PATH.'_lib/extlib/mandrill/Mandrill.php';
            $mandrill = new Mandrill($mandrill_api_key);
            $message = array( 'text' => $message, 'subject' => $subject, 'from_email' => $from_email,
            'from_name' => $app_title, 'to' => array( array( 'email' => $to, 'name' => $to ) ) );

            //don't send email when running tests, just write it to the filesystem for assertions
            if (Utils::isTest()) {
                self::setLastMail(json_encode($message));
            } else {
                $result = $mandrill->messages->send($message, $async, $ip_pool);
                //DEBUG
                //print_r($result);
            }
        } catch (Mandrill_Error $e) {
            throw new Exception('An error occurred while sending email via Mandrill. ' . get_class($e) .
            ': ' . $e->getMessage());
        }
    }
}
