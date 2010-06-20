<?php
/**
 * Controller interface
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

interface Controller {
    /**
     * Handle request parameters for a particular resource and return HTML markup view
     *
     * @return str HTML markup for web page
     */
    public function control();
}