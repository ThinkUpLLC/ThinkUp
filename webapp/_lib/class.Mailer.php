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
     * Send email from ThinkUp instalation. If you're running tests, just write the message headers and contents to
     * the file system in the data directory.
     * @param str $to A valid email address
     * @param str $subject
     * @param str $message
     */
    public static function mail($to, $subject, $message) {
        $config = Config::getInstance();

        $app_title = $config->getValue('app_title_prefix'). "ThinkUp";
        $host = self::getHost();

        $mail_header = "From: \"{$app_title}\" <notifications@{$host}>\r\n";
        $mail_header .= "X-Mailer: PHP/".phpversion();

        //don't send email when running tests, just write it to the filesystem for assertions
        if ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS") {
          $test_email = FileDataManager::getDataPath(Mailer::EMAIL);
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
    /**
     * Return the current host's name, ie, $_SERVER['HTTP_HOST'] if it is set.
     * @return str Host name
     */
    private static function getHost() {
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        } else {
            return "";
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
}