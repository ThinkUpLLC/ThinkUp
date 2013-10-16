<?php
/**
 *
 * ThinkUp/webapp/_lib/class.MailerPHP.php
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
 * @author KJ <xiankai[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
class MailerPHP {
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
        $host = Mailer::getHost();

        $mail_header = "From: \"{$app_title}\" <notifications@{$host}>\r\n";
        $mail_header .= "X-Mailer: PHP/".phpversion();

        //don't send email when running tests, just write it to the filesystem for assertions
        if (Mailer::isTest()) {
            Mailer::setLastMail($mail_header."\n" .
                                "to: $to\n" .
                                "subject: $subject\n" .
                                "message: $message");
        } else {
            mail($to, $subject, $message, $mail_header);
        }
    }
}