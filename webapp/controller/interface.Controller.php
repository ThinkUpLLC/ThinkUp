<?php
/**
 * Controller interface
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

interface Controller {
    /**
     * Handle request parameters for a particular resource and display HTML markup results
     *
     * @return string HTML markup
     */
    public function control();
}