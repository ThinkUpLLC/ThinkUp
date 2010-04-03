<?php
require_once('config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once 'common/class.Config.php';		
require_once 'common/class.Database.php';	
require_once 'common/class.MySQLDAO.php';	
require_once 'common/class.User.php';
require_once 'common/class.Owner.php';
require_once 'common/class.Post.php';
require_once 'common/class.Link.php';
require_once 'common/class.Instance.php';
require_once 'common/class.OwnerInstance.php';
require_once 'common/class.PluginHook.php';
require_once 'common/class.Crawler.php';		
require_once 'common/class.Utils.php';	

# crawler only
require_once 'common/class.Logger.php';		

# webapp only
require_once 'common/class.Follow.php';

require_once 'config.inc.php';
require_once($THINKTANK_CFG['smarty_path'].'Smarty.class.php');
require_once 'common/class.SmartyThinkTank.php';



?>