/**
* Plugin Option object for processing the plugin option form
*/
var PluginOptions = function() {

    /**
* @var boolean Enable for console logging
*/
    this.DEBUG = false;

    /**
* Init our plugin options form
*/
    this.init = function() {
        // register on submit event on our form
        $(document).ready( function() {
            $("#plugin_option_form").submit(function(event){
                if(plugin_options.DEBUG) { console.debug("option form submitted"); }
                plugin_options.submitForm();
            });
        });
    };

    /**
* form submit event handler
*/
    this.submitForm = function() {
        if( plugin_options.submitting ) { return; }
        if(plugin_options.DEBUG) { console.debug("submit_form function called..."); }
        submit_status = true;
        // note plugin_id , option_elements, option_required_message, option_etc,
        // defined in _plugin_options.tpl, and populated in PluginConfigurationController
        form_data = {action: 'set_options', plugin_id: plugin_id};
        $('#plugin_option_error').hide();
        $('#plugin_option_server_error').hide();
        for (var option_name in option_elements) {
            option = option_elements[option_name];
            status_check = true;
            if(plugin_options.DEBUG) {
                console.debug('%s type %s id %s', option_name, option.type, option.id);
            }
            var err_div_selector = '#plugin_options_error_' + option_name;
            $(err_div_selector).hide();
            if(option.type == 'text_element') {
                delete option.regex_fail;
                value = this.processTextElement(option);
                if(value && option.validation_regex) {
                    var val_regex = new RegExp(option.validation_regex);
                    if(! value.match(val_regex)) {
                        value = '';
                        option.regex_fail = true;
                    }
                }
                if(value) {
                    form_data['option_' + option_name] = value;
                    if(option.id) {
                        form_data['id_option_' + option_name] = option.id;
                    }
                } else {
                    status_check = this.setRequiredMessage(option);
                }
            } else if(option.type == 'radio_element') {
                value = this.processRadioElement(option);
                if(value) {
                    form_data['option_' + option_name] = value;
                    if(option.id) {
                        form_data['id_option_' + option_name] = option.id;
                    }
                } else {
                    status_check = this.setRequiredMessage(option);
                }
            } else if(option.type == 'select_element') {
                value = this.processSelectElement(option);
                if(value) {
                    form_data['option_' + option_name] = value;
                    if(option.id) {
                        form_data['id_option_' + option_name] = option.id;
                    }
                } else {
                    status_check = this.setRequiredMessage(option);
                }
            }
            if(option.id && ! value && option_not_required[option_name]) {
                if(option.id) {
                    form_data['id_option_' + option_name] = option.id;
                    form_data['option_' + option_name] = '';
                }
            }
            submit_status = (submit_status == false) ? false : status_check;
            if(plugin_options.DEBUG) { console.debug('Submitted stats = %s - %s', submit_status, option_name); }
        }
        if(! submit_status) {
            if(plugin_options.DEBUG) { console.debug('not submitted'); }
            $('#plugin_option_error').show();
        } else {
            plugin_options.submitting = true;
            controller_uri = site_root_path + 'account/plugin-options.php';
            form_data['csrf_token'] = window.csrf_token;
            $.ajax({
                url: controller_uri,
                data: form_data,
                dataType: 'json',
                error: function(data) {
                    plugin_options.submitting = false;
                    $('#plugin_option_server_error_message').html(
                        'Sorry, but we are unable to process your request at this time.'
                    );
                    $('#plugin_option_server_error').show();
                },
                success: function(data) {
                    if(plugin_options.DEBUG) { console.debug(data); }
                    if(data.status == 'failed' || data.error) {
                        plugin_options.submitting = false;
                        var message = (data.error) ? 'Exception: ' + data.error.message : data.message;
                        $('#plugin_option_server_error_message').html(message);
                        $('#plugin_option_server_error').show();
                    } else {
                        $('#plugin_options_success').show();
                        
                        // set our ids for new items, so we can update again in thesame form
                        for(key in data.results.inserted) {
                            option_elements[key]['id'] = data.results.inserted[key];
                        }
                        setTimeout(function() {
                            window.location.reload();
                            $('#plugin_options_success').fadeOut(500,
                                function() {
                                    plugin_options.submitting = false;
                            });
                        }, 1000);
                    }
                }
            });
        }
    }

    /**
* set missing element message
*/
    this.setRequiredMessage = function(option) {
        // we don't require this option, so return true
        if(option_not_required[option['name']] && ! option.regex_fail) {
            if(plugin_options.DEBUG) { console.debug('Value not required for %s', option.name ); }
            return true;
        }

        message = '';
        if( option_required_message[option['name']] ) {
            message = option_required_message[option['name']];
        } else {
            var name = option['label'] ? option['label']: option['name'];
            message = 'Please enter an option for <i>' + name + '<i>';
        }
        if(plugin_options.DEBUG) { console.debug("set missing element message %", message); }
        var mess_select = '#plugin_options_error_message_' + option['name'];
        $(mess_select).html(message);
        var err_div_selector = '#plugin_options_error_' + option['name'];
        $(err_div_selector).show();
        return false;
    }

    /**
* process select element input
*
* @return str option value, false if not defined
*/
    this.processSelectElement = function(option) {
        selector = '#plugin_options_' + option.name;
        var select_element = $(selector);
        value = select_element.val();
        if(plugin_options.DEBUG) { console.debug("%s form value = %s", option.name, value); }
        value = (! value) ? false : value;
        return value;
    }


    /**
* process radio element input
*
* @return str option value, false if not defined
*/
    this.processRadioElement = function(option) {
        selector = '#plugin_options_' + option.name;
        var radio_element = $(selector + ' input:radio:checked');
        value = radio_element.val()
        if(plugin_options.DEBUG) { console.debug("%s form value = %s", option.name, value); }
        value = (! value) ? false : value;
        return value;
    }

    /**
* process text element input
*
* @return str option value, false if not defined
*/
    this.processTextElement = function(option) {
        selector = '#plugin_options_' + option.name;
        var text_element = $(selector);
        value = text_element.val()
        if(plugin_options.DEBUG) { console.debug("%s form value = %s", option.name, value); }
        if(value && value != '') {
            return value;
        } else {
            if(plugin_options.DEBUG) { console.debug('No value for %s', option.name ); }
            return false;
        }
    }
    
}

var plugin_options = new PluginOptions();
plugin_options.init();