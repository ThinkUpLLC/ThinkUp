/**
 *
 * ThinkUp/webapp/assets/js/upgrade.js
 *
 * Copyright (c) 2009-2010 Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * upgrade.js
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */

/**
 * Upgrade db Migration object for migrating db updates...
 */
var TU_Update = function() {

    /**
     * current position for sql_array
     */
    this.position = 0;

    /**
     * our json sql array count
     */
    this.count = 0;
    /**
     * @var boolean Enable for console logging
     */
    this.DEBUG = false;

    /**
     * Init our update form
     */
    this.init = function() {
        // gen count for our json array (since the normal array.lenght does not
        // work on json structures
        if (typeof (sql_array) != 'undefined') {
            for (_obj in sql_array)
                this.count++;
        }

        // register on submit event on our form
        $(document).ready(function() {
            $("#upgrade-form").submit(function(event) {
                if (tu_update.DEBUG) {
                    console.debug("upgrade form submitted");
                }
                if (!tu_update.submitting) {
                    $('#migration-submit').hide();
                    tu_update.prependStatus("Starting database migration...");
                    tu_update.submitForm();
                }
            });
        });
    };

    /**
     * Process next upgrade sql in list
     */
    this.submitForm = function() {
        if (tu_update.submitting)
            return;
        sql_num = tu_update.position + 1;
        migration = sql_array[tu_update.position];
        tu_update.prependStatus("processing sql migration " + sql_num + " of "
                + tu_update.count + ": version " + '<i>' + migration.version + '</i><pre style="font-size: 10px; margin: 20px;">' + migration.sql + '</pre>');
        tu_update.position++;
//        if (sql.match(/key/i)) {
//            tu_update
//                    .prependStatus("&nbsp; <b>note:</b> Adding an index, this may take some time");
//        }
        

        controller_uri = site_root_path + 'install/upgrade.php';
        tu_update.submitting = true;
        $('#migrate_spinner').show();
        var params = {migration_index: tu_update.position};
        var done_params = { migration_done : true };
        if(typeof(upgrade_token) != 'undefined') {
            params['upgrade_token'] = upgrade_token;
            done_params['upgrade_token'] = upgrade_token;
        }

        $ajax_data = {
            url : controller_uri,
            data : params,
            error : function(data) {
                tu_update.submitting = false;
                $('#migrate_spinner').hide();
                $('#upgrade-error')
                        .html(
                                'Sorry, but we are unable to process your request at this time.');
                $('#upgrade-error').show();
            },
            success : function(data) {
                tu_update.submitting = false;
                $('#migrate_spinner').hide();
                if (data && data.processed) {
                    tu_update.prependStatus("Complete");
                    if (tu_update.position <= tu_update.count - 1) {
                        tu_update.prependStatus("<br />");
                        tu_update.submitForm();
                    } else {
                        tu_update.prependStatus("<br />");
                        tu_update.prependStatus("<b>Migration Complete!</b>");
                        $('#migration-info').html('<b>Migration Complete!</b>');
                        $('#migrate_spinner').hide();
                        $done_ajax_data = {
                            url : controller_uri,
                            data : done_params,
                            error : function() {
                                $('#migrate_spinner').hide();
                                $('#upgrade-error')
                                        .html(
                                                'Sorry, but we are unable to process your request.');
                                $('#upgrade-error').show();
                            }
                        }
                        $.ajax($done_ajax_data);
                    }
                } else {
                    $('#upgrade-error')
                            .html(
                                    'Sorry, but we are unable to process your request at this time.<br />' 
                                    + data.message);
                    $('#upgrade-error').show();
                }
            }
        }
        $.ajax($ajax_data);
    }

    /**
     * prepend text to migration status div
     */
    this.prependStatus = function(status) {
        status = '<div>' + status + '</div>';
        $('#migration-status').prepend(status);
    }
}

var tu_update = new TU_Update();
tu_update.init();