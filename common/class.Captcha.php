<?PHP 
class Captcha {
    var $type;
    var $msg = FALSE;
    private $pubkey;
    private $prikey;
    public function __construct($config) {
        if ($config['recaptcha_enable']) {
            $this->type = 1;
            require_once ($config['recaptcha_path']."/recaptchalib.php");
            $this->pubkey = $config['recaptcha_public_key'];
            $this->prikey = $config['recaptcha_private_key'];
        } else {
            $this->type = 0;
        }
    }
    public function generate() {
        switch ($this->type) {
            case 1:
                $code = recaptcha_get_html($this->pubkey, $this->msg);
                return $code;
                break;
            default:
                if (isset($this->msg)) {
                    return "<input name=\"user_code\" type=\"text\" size=\"10\"><img src=\"/captcha/pngimg.php\" align=\"middle\"><span style=\"color: #FF0000\">".$this->msg."</span>";
                } else {
                    return "<input name=\"user_code\" type=\"text\" size=\"10\"><img src=\"/captcha/pngimg.php\" align=\"middle\">&nbsp;";
                }
                break;
        }
    }
    public function check() {
        switch ($this->type) {
            case 1:
                $resp = recaptcha_check_answer($this->prikey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
                if (!$resp->is_valid) {
                    $this->msg = $resp->error;
                    return FALSE;
                } else {
                    return TRUE;
                }
                break;
            default:
                if (strcmp(md5($_POST['user_code']), $_SESSION['ckey'])) {
                    $this->msg = "Wrong text, try again";
                    return FALSE;
                } else {
                    return TRUE;
                }
                break;
        }
    }
}

?>
