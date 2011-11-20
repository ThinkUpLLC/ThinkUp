/**
 * Validate password
 */
var TUValidatePassword = function() {

    /**
     * @var boolean Enable for console logging
     */
    this.DEBUG = false;

    /**
     * Over ride defuslt password messages...
     */
    $.validator.passwordRating.messages['too-short'] = "Too-Short: must be at least 8 characters";

    /**
     * Init our plugin options form
     */
    this.init_register = function() {
        // register on submit event on our register form
        $(document).ready(function() {
            var validator = $("#registerform").validate({
                rules: {
                    full_name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    pass1: {
                        required: true,
                        password: true
                    },
                    pass2: {
                        required: true,
                        equalTo: "#pass1"
                    }
                },
                messages: {
                    full_name: { required: "Please enter your name." },
                    email: { required: "Please enter a valid email address." },
                    pass1: { required: "" },
                    pass2: { required: "<br />Your passwords must match.", equalTo: "<br />Your passwords must match." }
                }
            });
            $("#pass1").valid();
        });
    }

    this.init_reset = function() {
        // register on submit event on our register form
        $(document).ready(function() {
            var validator2 = $("#changepass").validate({
                rules: {
                    oldpass: {
                        required: true,
                        password: false
                    },
                    pass1: {
                        required: true,
                        password: true
                    },
                    pass2: {
                        required: true,
                        equalTo: "#pass1"
                    }
                },
                messages: {
                    oldpass: { required: "<br />Please enter your current password." },
                    pass1: { required: "" },
                    pass2: { required: "<br />Your passwords must match.", equalTo: "<br />Your passwords must match." }
                }
            });
            $("#pass1").valid();
        });
    }
}

var tu_validate_password = new TUValidatePassword();
if(document.location.href.match(/\/account/)) {
    tu_validate_password.init_reset();
} else {
    tu_validate_password.init_register();
}
