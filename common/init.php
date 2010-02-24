<?php 
//Before we do anything, make sure we've got PHP 5
$version = explode('.', PHP_VERSION);
if ($version[0] < 5) {
    echo "ERROR: ThinkTank requires PHP 5. The current version of PHP is ".phpversion().".";
    die();
}

require_once 'class.Config.php';
require_once 'class.Database.php';
require_once 'class.MySQLDAO.php';
require_once 'class.User.php';
require_once 'class.Owner.php';
require_once 'class.Tweet.php';
require_once 'class.Link.php';
require_once 'class.Instance.php';
require_once 'class.OwnerInstance.php';
require_once 'class.LongUrlAPIAccessor.php';
require_once 'class.FlickrAPIAccessor.php';
require_once 'class.Crawler.php';
require_once 'class.Utils.php';
require_once 'class.Captcha.php';
require_once 'class.Session.php';

require_once 'class.TwitterAPIAccessorOAuth.php';
require_once 'OAuth.php';
require_once 'twitterOAuth.php';

require_once 'class.LoggerSlowSQL.php';		

# crawler only
require_once 'class.Logger.php';

# webapp only
require_once 'class.Follow.php';

require_once 'config.inc.php';
require_once ($THINKTANK_CFG['smarty_path'].'Smarty.class.php');
require_once 'class.SmartyThinkTank.php';

?>
