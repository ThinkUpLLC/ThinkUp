<?php
/**
 *
 * ThinkUp/extras/wordpress/thinkup/thinkup.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 */

/**
 * This class handles all of the pages in the ThinkUp admin menu.
 *
 * @author Sam Rose
 */
class ThinkUpAdminPages {

    /**
     * Add the ThinkUp menu to the admin menus.
     */
    public static function addOptionsPage() {
        add_options_page(
                'ThinkUp Plug-in Options',
                'ThinkUp',
                ThinkUpWordPressPlugin::accessLevel(),
                ThinkUpWordPressPlugin::uniqueIdentifier(),
                array('ThinkUpAdminPages', 'index'));
    }

    /**
     * ThinkUp admin menu entry page. This page handles the passing to
     * every page in the admin section through the 'step' get variable.
     */
    public static function index() {

        // div class wrap for display purposes
        echo '<div class="wrap">';
        // header
        echo '<h2 id="top">'.__('ThinkUp Plugin Options',
            ThinkUpWordPressPlugin::uniqueIdentifier()).'</h2>';

        // menu buttons
        if (current_user_can(ThinkUpWordPressPlugin::settingsAccessLevel())) {
            echo '<a class="button-secondary" href="?page='.
            ThinkUpWordPressPlugin::uniqueIdentifier().'&step=settings">Settings</a>';
        }
        echo '<a class="button-secondary" href="?page='.
        ThinkUpWordPressPlugin::uniqueIdentifier().'&step=help">Help!</a>';
        echo '<a class="button-secondary" href="?page='.
        ThinkUpWordPressPlugin::uniqueIdentifier().'&step=faq">FAQ</a>';

        echo '<br /><br />';

        // decide which page to load based on a 'step' GET variable
        switch ($_GET['step']) {
            case 'help':
                ThinkUpAdminPages::help();
                break;
            case 'faq':
                ThinkUpAdminPages::faq();
                break;
            default:
                if (current_user_can(ThinkUpWordPressPlugin::settingsAccessLevel())) {
                    ThinkUpAdminPages::settings();
                }
                else {
                    ThinkUpAdminPages::help();
                }
                break;
        }

        // end <div class="wrap">
        echo '</div>';
    }

    /**
     * The default landing page for the plugin's admin pages.
     *
     * PHP + HTML = Messy :(
     */
    public static function settings() {
        //fetch the options array
        $options_array = ThinkUpWordPressPlugin::getOptionsArray();

        //check to see if the form was submitted
        if (isset($_POST['Submit'])) {
            //make sure the user submitting the form is an admin
            check_admin_referer('thinkup_settings_submit',
                ThinkUpWordPressPlugin::nonceName());

            foreach ($options_array as $opt) {
                // read posted values
                $opt['value'] = $_POST[$opt['key']];

                // save the posted value in the database
                if ($opt['key'] == 'thinkup_dbpw') {
                    // scramble the password
                    update_option($opt['key'],
                        ThinkUpWordPressPlugin::scramblePassword($opt['value']));
                }
                else {
                    // store non-passwords normally
                    update_option($opt['key'], $opt['value']);
                }
            }

            // print "updated!" message to screen
            ?>
            <div class="updated">
                <p><strong>
                    <?php _e('Options saved.',
                        ThinkUpWordPressPlugin::uniqueIdentifier()); ?>
                </strong></p>
            </div>
            <?php

            //force an update to the options array for display purposes
            $options_array = ThinkUpWordPressPlugin::getOptionsArray('force-update');
        }

        ?>

        <div id="poststuff" class="ui-sortable meta-box-sortable">
            <div class="postbox" id="thinkup_settings">
                <h3><?php _e('ThinkUp Plugin Settings',
                    ThinkUpWordPressPlugin::uniqueIdentifier()); ?></h3>
                <div class="inside">
                    <form name="thinkup_settings_form" method="post"
                          action="">
                        <?php
                            //Add the nonce field for added security.
                            wp_nonce_field('thinkup_settings_submit',
                                ThinkUpWordPressPlugin::nonceName());
                        ?>

                        <table>
                        <?php
                        foreach ($options_array as $opt) {
                            if ($opt['key'] == 'thinkup_dbpw') {
                                $field_value =
                                    ThinkUpWordPressPlugin::unscramblePassword(
                                            get_option($opt['key']));
                            }
                            else {
                                $field_value = get_option($opt['key']);
                            }

                            ?>
                            <tr>
                                <td align="right" valign="top"><?php _e($opt['label'], 'mt_trans_domain'); ?>
                                </td>
                                <td><input type="<?php echo $opt['type']; ?>"
                                    name="<?php echo $opt['key'] ?>" value="<?php echo $field_value ?>"
                                    size="20"> <br />
                                <small> <?php echo $opt['description']; ?> </small></td>
                            </tr>
                        <?php } ?>
                        </table>

                        <p class="submit">
                            <input type="submit" name="Submit"
                               value="<?php _e('Update Options',
                               ThinkUpWordPressPlugin::uniqueIdentifier()); ?>" />
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * This function is used to display the Help page in the ThinkUp plugin
     * menu. It uses files in /help to display its content.
     */
    public static function help() {
        $topics = self::fetchTxtFiles(
                ThinkUpWordPressPlugin::pluginDirectory().'/help');
        $title = __('Help Topics',
                ThinkUpWordPressPlugin::uniqueIdentifier());

        self::displayHelpFaqContents($title, $topics);
        self::displayTxtFiles($topics);
    }

    /**
     * This function is used to display the FAQ page in the ThinkUp plugin
     * menu. It uses files in /faq to display its content.
     */
    public static function faq() {
        $questions = self::fetchTxtFiles(
                ThinkUpWordPressPlugin::pluginDirectory().'/faq');

        $title = __('Frequently Asked Questions',
                ThinkUpWordPressPlugin::uniqueIdentifier());

        self::displayHelpFaqContents($title, $questions);
        self::displayTxtFiles($questions);
    }

    /**
     * This function searches a directory for files and returns an associative
     * array of key = filename and value = file contents. 
     *
     * @param string $directory The directory to search, DO NOT ADD A TRAILING
     * FORWARDSLASH.
     * @return array Associative array of key = filename, value = file
     * contents.
     */
    public static function fetchTxtFiles($directory) {
        // get an array of the contends of $directory
        $files = scandir($directory);

        // initialise the return array
        $return = array();

        foreach ($files as $file) {
            // check that the current $file is a file, not a directory
            if (is_file($directory.'/'.$file)) {
                /*
                 * Create an associative entry in the array, key = filename,
                 * value = file contents.
                 */
                $return[basename($directory.'/'.$file, '.txt')] =
                    file_get_contents($directory.'/'.$file);
            }
        }

        // return the $return array
        return $return;
    }

    /**
     * This function prints out an array generated by the fetchTxtFiles()
     * function in this class.
     *
     * @param array $files An array generated by fetchTxtFiles().
     * @return boolean Returns false if the parameter passed is not an array.
     */
    public static function displayTxtFiles($files) {
        if (!is_array($files)) {
            return false;
        }

        foreach ($files as $title => $content) {
            $title = __($title, ThinkUpWordPressPlugin::uniqueIdentifier());
            $content = __($content, ThinkUpWordPressPlugin::uniqueIdentifier());
            ?>
            <div id="poststuff" class="ui-sortable meta-box-sortable">
                <div class="postbox" id="<?php echo $title ?>">
                    <h3>
                        <?php echo $title; ?>
                    </h3>
                    <div class="inside" style="line-height: 1.5;">
                        <?php echo nl2br($content); ?>
                        <br />
                        <a class="button-secondary" href="#top">Back to top</a>
                    </div>
                </div>
            </div>
            <?php
        }

        return true;
    }

    public static function displayHelpFaqContents($title, $files) {
        $title = __($title, ThinkUpWordPressPlugin::uniqueIdentifier());

        ?>
        <div id="poststuff" class="ui-sortable meta-box-sortable">
            <div class="postbox" id="contents">
                <h3>
                    <?php echo $title; ?>
                </h3>
                <div class="inside" style="line-height: 1.5">
        <?php
            foreach ($files as $name => $contents) {
                $name = __($name, ThinkUpWordPressPlugin::uniqueIdentifier());
                echo '<a href="#'.$name.'">'.$name.'</a><br />';
            }
        ?>
                </div>
            </div>
        </div>
        <?php

    }
}
?>
