<?php
/**
 * ThinkUp/webapp/plugins/insightsgenerator/tests/classes/mock.ThinkUpLLCAPIAccessor.php
 *
 * Copyright (c) 2015 Gina Trapani
 *
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
 * Mock ThinkUpLLC API Accessor
 *
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2015 Gina Trapani
 */
class ThinkUpLLCAPIAccessor {
    /**
     * Get the subscription status for a ThinkUp.com member via an API call.
     * @param  str $email
     * @return Object
     */
    public function getSubscriptionStatus($email) {
        if ($email == 'paymentfailed@example.com') {
            $resp = <<<EOD
{
    "email":"paymentfailed@example.com",
    "subscription_status":"Payment failed"
}
EOD;
            return JSONDecoder::decode($resp);
        } elseif ($email == 'paid@example.com') {
            $resp = <<<EOD
{
    "email":"paid@example.com",
    "subscription_status":"Paid"
}
EOD;
            return JSONDecoder::decode($resp);
        } elseif ($email == 'freetrial@example.com') {
            $resp = <<<EOD
{
    "email":"freetrial@example.com",
    "subscription_status":"Free trial"
}
EOD;
            return JSONDecoder::decode($resp);
        } else {
            return null;
        }
    }
}