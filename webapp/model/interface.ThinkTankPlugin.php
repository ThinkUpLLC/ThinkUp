<?php
/**
 * ThinkTank Plugin interface
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface ThinkTankPlugin {
    /**
     * Render the configuration screen in the webapp
     * @param Owner $owner
     * @return str HTML markup of configuration panel
     */
    public function renderConfiguration($owner);
}
