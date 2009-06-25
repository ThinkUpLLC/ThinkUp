<?php

require_once $root_path.'class.Config.php';		
require_once $root_path.'class.Database.php';	
require_once $root_path.'class.TwitterAPIAccessor.php';		
require_once $root_path.'class.User.php';
require_once $root_path.'class.Owner.php';
require_once $root_path.'class.Tweet.php';
require_once $root_path.'class.Instance.php';
require_once $root_path.'class.OwnerInstance.php';
require_once $root_path.'class.Crawler.php';		
require_once $root_path.'class.Utils.php';		

# crawler only
require_once $root_path.'class.Logger.php';		

# webapp only
require_once $root_path.'class.Follow.php';

require_once $root_path.'config.inc.php';
require_once($TWITALYTIC_CFG['smarty_path'].'Smarty.class.php');
require_once $root_path.'class.SmartyTwitalytic.php';

?>