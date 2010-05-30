<?php 
//Before we do anything, make sure we've got PHP 5
$version = explode('.', PHP_VERSION);
if ($version[0] < 5) {
    echo "ERROR: ThinkTank requires PHP 5. The current version of PHP is ".phpversion().".";
    die();
}

require_once 'model/class.Config.php';
require_once 'model/class.Database.php';
require_once 'model/class.MySQLDAO.php';
require_once 'model/class.PDODAO.php';
require_once 'model/class.DAOFactory.php';
require_once 'model/class.User.php';
require_once 'model/class.Owner.php';
require_once 'model/class.Post.php';
require_once 'model/class.Link.php';
require_once 'model/class.Instance.php';
require_once 'model/class.OwnerInstance.php';
require_once 'model/class.PluginHook.php';
require_once 'model/class.Crawler.php';
require_once 'model/class.Utils.php';
require_once 'model/class.Captcha.php';
require_once 'model/class.Session.php';
require_once 'model/class.RetweetDetector.php';
require_once 'model/class.Plugin.php';
require_once 'model/class.LoggerSlowSQL.php';
require_once 'model/interface.ThinkTankPlugin.php';
require_once 'model/interface.CrawlerPlugin.php';
require_once 'model/interface.WebappPlugin.php';
require_once 'model/class.WebappTab.php';
require_once 'model/class.WebappTabDataset.php';

# crawler only
require_once 'model/class.Logger.php';

# webapp only
require_once 'model/class.Follow.php';
require_once 'model/class.Webapp.php';

require_once 'config.inc.php';

$config = Config::getInstance();

require_once $config->getValue('smarty_path').'Smarty.class.php';
require_once 'model/class.SmartyThinkTank.php';
require_once $config->getValue('source_root_path').'extlib/twitteroauth/twitteroauth.php';


if ($config->getValue('time_zone')) {
    putenv($config->getValue('time_zone'));
}
if ($config->getValue('debug')) {
    ini_set("display_errors", 1);
    ini_set("error_reporting", E_ALL);
}

$webapp = new Webapp();
$crawler = new Crawler();

// Instantiate global database variable
try {
    $db = new Database($THINKTANK_CFG);
    $conn = $db->getConnection();
}
catch(Exception $e) {
    echo $e->getMessage();
}

/* Start plugin-specific configuration handling */
$pdao = new PluginDAO($db);
$active_plugins = $pdao->getActivePlugins();
foreach ($active_plugins as $ap) {
    foreach (glob($config->getValue('source_root_path').'webapp/plugins/'.$ap->folder_name."/model/*.php") as $includefile) {
        require_once $includefile;
    }
    foreach (glob($config->getValue('source_root_path').'webapp/plugins/'.$ap->folder_name."/controller/*.php") as $includefile) {
        require_once $includefile;
    }
}
?>
