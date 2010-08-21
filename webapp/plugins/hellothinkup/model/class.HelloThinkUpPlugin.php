<?php
class HelloThinkUpPlugin implements CrawlerPlugin {

    public function renderConfiguration($owner) {
        $controller = new HelloThinkUpPluginConfigurationController($owner, 'hellothinkup');
        return $controller->go();
    }

    public function crawl() {
        //echo "HelloThinkUp crawler plugin is running now.";
        /**
          * When crawling, make sure you only work on objects the current Owner has access to.
          *
          * Example:
          *
          *	$od = DAOFactory::getDAO('OwnerDAO');
          *	$oid = DAOFactory::getDAO('OwnerInstanceDAO');
          *
          * $current_owner = $od->getByEmail($_SESSION['user']);
          *
          * $instances = [...]
          * foreach ($instances as $instance) {
          *	    if (!$oid->doesOwnerHaveAccess($current_owner, $instance->network_username)) {
          *	        // Owner doesn't have access to this instance; let's not crawl it.
          *	        continue;
          *	    }
          *	    [...]
          * }
          *
          */
    }
}