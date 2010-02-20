<?PHP
class Session {
    private $data;
    private $salt = "ab194d42da0dff4a5c01ad33cb4f650a7069178b";
    public function __construct(){
        $data = $_SESSION;
    }
    public function isLogedin(){
        if (!isset($_SESSION['user'])) {
            return false;
        }
        else {
            return true;
        }
    }
    private function md5pwd($pwd) {
        return md5($pwd);
    }
    private function sha1pwd($pwd) {
        return sha1($pwd);
    }
    private function saltedsha1($pwd) {
        return sha1(sha1($pwd.$this->salt).$this->salt);
    }

    //Public Functions
    public function pwdcrypt($pwd) {
        return $this->saltedsha1($pwd);
    }
    public function pwdCheck($pwd, $result) {
        if ($this->saltedsha1($pwd) == $result 
            or $this->sha1pwd($pwd) == $result
            or $this->md5pwd($pwd) == $result
        ) {
            return true;
        } else {
            return false;
        }
    }
   public function CompleteLogin($data) {
        $_SESSION['user']= $data['mail'];
   }
   public function logout() {
        unset($_SESSION['user']);
   }
}
?>
