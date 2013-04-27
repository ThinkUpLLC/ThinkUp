/**
 * 
 * ThinkUp/webapp/assets/js/notify-insights.js
 * 
 * Copyright (c) 2009-2010 Nilaksh Das
 * 
 * LICENSE:
 * 
 * This file is part of ThinkUp (http://thinkup.com).
 * 
 * ThinkUp is free software: you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 2 of the License, or (at your option) any later
 * version.
 * 
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * ThinkUp. If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
 * @author Nilaksh Das <nilakshdas[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Nilaksh Das
 */

(function($) {
    $(document).ready(function() {
        function checkNotificationPermission() {
            switch(window.webkitNotifications.checkPermission()) {
                case 0:
                // Permission has been granted.
                $('#notify-insights').hide();
                var timecheck = Math.round(+new Date()/1000);
                (function poll() {
                    $.ajax({
                        url: site_root_path + "api/v1/insight.php"
                        + "?from=" + timecheck
                        + "&api_key=" + thinkup_api_key,
                        dataType: "json",
                        success: function(data) {
                            if (typeof data.error === 'undefined') {
                                for (var i = 0 ; i < data.length; i++) {
                                    var insight = data[i];
                                    var icon = site_root_path + "assets/img/favicon.png",
                                    title = insight.prefix,
                                    message = $(document.createElement('div')).hide().append(insight.text).text().replace(':', '...');
                                    notification = window.webkitNotifications.createNotification(icon, title, message);
                                    notification.onclick = function(x) { 
                                        window.open(
                                            document.URL 
                                            + "?u=" + insight.instance.network_username
                                            + "&n=" + insight.instance.network
                                            + "&d=" + insight.date.substr(0,10)
                                            + "&s=" + insight.slug
                                        );
                                        this.cancel(); 
                                    };
                                    notification.show();
                                }
                                timecheck = Math.round(+new Date()/1000);
                            }
                        },
                        timeout: 120000 // Kill request after 2 minutes
                    });
                    setTimeout(poll,(300000)); // Wait 5 mins before polling again
                })();
                break;
                
                case 1:
                // Permission has not been granted or refused.
                $('#notify-insights').show();
                $('#notify-insights').click(function() {
                    window.webkitNotifications.requestPermission(checkNotificationPermission);
                });
                break;
                
                case 2:
                // Permission has been refused.
                $('#notify-insights').hide();
                console.log("Desktop notifications of new insights has been disabled by the user.");
                break;
            }
        }

        if (window.webkitNotifications) {
            checkNotificationPermission();
        }
    });
})(jQuery);