/**
 * Validate password
 */
var TUValidatePassword = function() {

    /**
     * @var boolean Enable for console logging
     */
    this.DEBUG = false;

    /**
     * Over ride $.validator.addMethod('password') methods and functions and messages...
     */
     var LOWER = /[a-z]/,
         UPPER = /[A-Z]/,
         DIGIT = /[0-9]/,
         DIGITS = /[0-9].*[0-9]/,
         SPECIAL = /[^a-zA-Z0-9]/,
         THINKUP_VALIDATE = /(?=.{8,})(?=.*[a-zA-Z])(?=.*[0-9])/,
         SAME = /^(.)\1+$/;

     $.validator.passwordRating = function(password, username) {
         if (!password || password.length < 8)
            return rating(0, "too-short");
        if (username && password.toLowerCase().match(username.toLowerCase()))
            return rating(0, "similar-to-username");
        if(! THINKUP_VALIDATE.test(password)) {
            return rating(2, "weak");
        }
        if (SAME.test(password))
            return rating(1, "very-weak");

            var lower = LOWER.test(password),
            upper = UPPER.test(uncapitalize(password)),
            digit = DIGIT.test(password),
            digits = DIGITS.test(password),
            special = SPECIAL.test(password);

        if (lower && upper && digit || lower && digits || upper && digits || special)
            return rating(4, "strong");
        if (lower && upper || lower && digit || upper && digit)
            return rating(3, "good");
        return rating(2, "weak");
    }
    $.validator.passwordRating.messages = {
        "similar-to-username": "Too similar to username",
        "too-short": "Too short: Must be at least 8 characters",
        "very-weak": "Weak: Must contain letters and numbers",
        "weak": "Weak: Must contain letters and numbers",
        "good": "Good",
        "strong": "Strong"
    }
    function rating(rate, message) {
        return {
            rate: rate,
            messageKey: message
        };
    }
    function uncapitalize(str) {
        return str.substring(0, 1).toLowerCase() + str.substring(1);
    }

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

    this.init_install = function() {
        // register on submit event on our install form
        $(document).ready(function() {
            var validator2 = $("#install_form").validate({
                rules: {
                    password: {
                        required: true,
                        password: true
                    },
                    confirm_password: {
                        required: true,
                        equalTo: "#password"
                    },
                },
                messages: {
                    password: { required: "" },
                    confirm_password: { required: "<br />Your passwords must match.", equalTo: "<br />Your passwords must match." }
                }
            });
            $("#password").valid();
        });
    }
}

var tu_validate_password = new TUValidatePassword();
if(document.location.href.match(/\/account/)) {
    tu_validate_password.init_reset();
} else if(document.location.href.match(/\/install/)) {
    tu_validate_password.init_install();
} else {
    tu_validate_password.init_register();
}
