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
        sql = sql_array[tu_update.position];
        tu_update.prependStatus("processing sql migration " + sql_num + " of "
                + tu_update.count + ": " + '<i>' + sql + '</i>');
        tu_update.position++;
        if (sql.match(/key/i)) {
            tu_update
                    .prependStatus("&nbsp; <b>note:</b> Adding an index, this may take some time");
        }
        controller_uri = site_root_path + 'install/upgrade.php';
        tu_update.submitting = true;
        $('#migrate_spinner').show();
        $ajax_data = {
            url : controller_uri,
            data : {
                process_sql : sql
            },
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
                            data : {
                                migration_done : true
                            },
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
                                    'Sorry, but we are unable to process your request at this time.');
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