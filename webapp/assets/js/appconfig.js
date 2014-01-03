/**
 * 
 * ThinkUp/webapp/assets/js/application_settings.js
 * 
 * Copyright (c) 2009-2010 Mark Wilkie
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
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 */

var TUApplicationSettings = function() {

    /**
     * @var boolean Enable for console logging
     */
    this.DEBUG = false;

    /**
     * @var boolean tab view for setting has been loaded
     */
    this.TAB_LOADED = false;

    /**
     * Init our plugin options form
     */
    this.init = function() {
        // pre-load loading image...
        var loading_image = new Image();
        loading_image.src = site_root_path + 'assets/img/loading.gif';

        // register on submit event on our form
        $(document).ready(function() {
            $("#app-settings-tab").click(function(event) {
                if (tu_app_settings.DEBUG) {
                    console.debug("app settings tab selected");
                }
                tu_app_settings.load_settings();
            });
        });

        $('#recaptcha_enable')
                .click(
                        function(event) {
                            var checked = $('#recaptcha_enable:checked').val() ? true
                                    : false;
                            if (tu_app_settings.DEBUG) {
                                console
                                        .log(
                                                "recaptcha setting checkbox selected, value = %s",
                                                checked);
                            }
                            if (checked) {
                                $('#default_instance').css('margin-top','20px');
                                $('#recaptcha_enable_deps').show();
                            } else {
                                $('#default_instance').css('margin-top','0px');
                                $('#recaptcha_enable_deps').hide();
                            }
                        });
        if (document.location.href.match(/#app_settings/)) {
            if (tu_app_settings.DEBUG) {
                console
                    .debug("app settings tab loaded with hash #app_settings");
            }
            setTimeout(function() {
                tu_app_settings.load_settings();
            }, 1000);
        }

    };

    this.save_settings = function() {
        $('#settings_error_message_error').hide();
        if (this.DEBUG) {
            console.log("saving settings...");
            console.dir( {
                data : this.settings_data
            });
        }
        var params = {
            save : true
        };
        for (key in this.settings_data.app_config_settings) {
            var setting = this.settings_data.app_config_settings[key];
            var id = '#' + key;
            if (setting.type == 'checkbox') {
                id += ':checked';
                if ($(id).val()) {
                    id = '#' + key;
                    params[key] = $(id).val();
                    if (this.DEBUG) {
                        console.debug("saving checkbox value %s for %s",
                                params[key], key);
                    }
                }
            } else {
                params[key] = $(id).val();
            }
        }
        // add CSRF token
        params['csrf_token'] = window.csrf_token;
        if (this.DEBUG) {
            console.debug("saving app settings");
            console.dir( {
                save_settings : params
            });
        }
        $('#app-settings-save').hide();
        $('#save_setting_image').show();
        controller_uri = site_root_path + 'account/appconfig.php';
        $
                .ajax( {
                    url : controller_uri,
                    data : params,
                    type : 'post',
                    dataType : 'json',
                    error : function(data) {
                        $('#app-settings-save').show();
                        $('#save_setting_image').hide();
                        $('#settings_error_message')
                                .html(
                                        'Sorry, but we are unable to process your request at this time.');
                        $('#settings_error_message_error').show();
                    },
                    success : function(data) {
                        tu_app_settings._save_settings(data);
                    }
                });

    };

    this._save_settings = function(data) {
        $('#app-settings-save').show();
        $('#save_setting_image').hide();
        if (data.status == 'failed') {
            if (data.required) {
                var error_mess = 'Please enter values for the required fields<ul style="padding-left: 20px;">';
                for (key in data.required) {
                    error_mess += '<li>' + data.required[key] + '</li>';
                }
                error_mess += '</ul>';
                $('#settings_error_message').html(error_mess);
                $('#settings_error_message_error').show();
            }
        } else {
            $('#settings_success').show();
            setTimeout(function() {
                $('#settings_success').fadeOut(1500, function() {
                });
            }, 500);
        }
    };
    this.load_settings = function() {
        if (!tu_app_settings.TAB_LOADED) {
            if (this.DEBUG) {
                console.debug("app settings tab not yet loaded, loading...");
            }
            controller_uri = site_root_path + 'account/appconfig.php';
            $
                    .ajax( {
                        url : controller_uri,
                        dataType : 'json',
                        error : function(data) {
                            $('#app_setting_loading_div').hide();
                            $('#app_settings_div').show();
                            $('#settings_error_message')
                                    .html(
                                            'Sorry, but we are unable to process your request at this time.');
                            $('#settings_error_message_error').show();
                        },
                        success : function(data) {
                            tu_app_settings._load_settings(data);
                        }
                    });
        } else {
            if (this.DEBUG) {
                console.debug("app settings already loaded, not loading...");
            }
        }
    };

    this._load_settings = function(data) {
        this.settings_data = data;
        $('#app_setting_loading_div').hide();
        $('#app_settings_div').show();
        this.TAB_LOADED = true;
        $('#app-settings-form').submit(function() {
            tu_app_settings.save_settings();
        });
        for (key in data.app_config_settings) {
            var setting = data.app_config_settings[key];
            var id = '#' + key;
            if (data.values[key]) {
                if (this.DEBUG) {
                    console.debug("loading %s with value %s", key,
                            typeof (data.values[key]['option_value']));
                }
                if (setting.type == 'checkbox'
                        && data.values[key]['option_value'] != 'false') {
                    if (this.DEBUG) {
                        console.debug("%s is a checkbox with value %s", key,
                                data.values[key]['option_value']);
                    }
                    $(id).prop('checked', true);
                } else {
                    $(id).val(data.values[key]['option_value']);
                }
            } else {
                if (this.DEBUG) {
                    console.debug("loading default %s with value %s", key,
                            setting['default']);
                }
                if (setting.type == 'checkbox' && setting['default'] == 'true') {
                    if (this.DEBUG) {
                        console.debug("Checking checkbox for key %s", key,
                                setting['default']);
                    }
                    $(id).prop('checked', true);
                } else if (setting.type != 'checkbox') {
                    $(id).val(setting['default']);
                }
            }
            if (setting.dependencies && data.values[key]
                    && data.values[key] !== '') {
                var id = '#' + key + '_deps';
                if ($(id)) {
                    $(id).show();
                }
            }
        }
    };
};

var tu_app_settings = new TUApplicationSettings();
tu_app_settings.init();
