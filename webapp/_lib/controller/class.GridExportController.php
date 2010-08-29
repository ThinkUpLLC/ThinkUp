<?php
/**
 * Grid Export Controller
 * Exports Grid posts from an instance user on ThinkUp.
 *
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class GridExportController extends ThinkUpAuthController {

    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        if (!isset($_POST['grid_export_data']) ) {
            $this->is_missing_param = true;
        }
    }

    public function authControl() {
        if(  $this->is_missing_param ) {
            echo('No search data to export.');
        } else {
            if(get_magic_quotes_gpc()) {
                $_POST['grid_export_data'] = stripslashes($_POST['grid_export_data']);
            }
            $data = json_decode( $_POST['grid_export_data'] );
            if(! $data ) {
                echo('No search data to export.' . json_last_error() . "<br />");
                echo( $_POST['grid_export_data']);
            } else {
                if( ! headers_sent() ) { // this is so our test don't barf on us
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="export.csv"');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                }
                $fp = fopen('php://output', 'w');
                foreach($data as $post) {
                    // output post csv line
                    fputcsv($fp, (array)$post);
                    // flush output buffer
                    flush();
                }
                // close output handle
                fclose($fp);
            }
        }
    }

}