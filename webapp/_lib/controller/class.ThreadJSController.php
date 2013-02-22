<?php
/**
 *
 * ThinkUp/webapp/plugins/embedthread/controller/class.ThreadJSController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
class ThreadJSController extends ThinkUpController {
    /**
     * Required query string parameters
     * @var array u = instance username, n = network
     */
    var $REQUIRED_PARAMS = array('p', 'n');
    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;
    /**
     * Is the EmbedThread plugin enabled?
     * @var boolean
     */
    var $enabled = false;

    /**
     * Constructor
     * @param bool $session_started
     * @return ThreadJSController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->is_missing_param = true;
            }
        }
        Loader::definePathConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'_lib/view/api.embed.v1.thread_js.tpl');
    }
    /**
     * Echoes thread JavaScript object
     */
    public function control() {
        //extend cache lifetime to 10 minutes in case a high-traffic web site embeds this thread
        $this->view_mgr->cache_lifetime = 600;
        $this->setContentType('text/javascript');
        if ($this->shouldRefreshCache()) {
            $js_string = $this->getJavaScript();
            $this->addToView('result', $js_string);
        }
        return $this->generateView();
    }

    /**
     * Returns JavaScript string.
     * @return str JavaScript string
     */
    private function getJavaScript() {
        $result = 'ThinkUp'.(isset($_GET['p'])?$_GET['p']:'').'.serverResponse([{' . "\n";
        $config = Config::getInstance();
        $this->enabled = !$config->getValue('is_embed_disabled');
        if ($this->enabled) {
            if (!$this->is_missing_param) {
                $post_dao = DAOFactory::getDAO('PostDAO');
                $post = $post_dao->getPost($_GET['p'], $_GET['n']);
                if (isset($post)) {
                    if (!$post->is_protected) {
                        $replies_it = $post_dao->getRepliesToPostIterator($_GET['p'], $_GET['n'], 'default', 'km',
                        true);
                        $cnt = 0;
                        $author_link = ($post->network=='twitter')?'http://twitter.com/'.$post->author_username:'null';
                        $post_link = ($post->network=='twitter')?'http://twitter.com/'.$post->author_username.
                        '/status/'.$post->post_id.'/':'null';

                        $result .='"status":"success",
"post":'.json_encode($post->post_text).', "author_avatar":'.json_encode($post->author_avatar).',
"author":'.json_encode($post->author_username).',
"author_link":'.json_encode($author_link).',
"post_link":'.json_encode($post_link).',
"replies": [
';

                        foreach($replies_it as $key => $value) {
                            $cnt++;
                            $author_link = ($value->network=='twitter')?'http://twitter.com/'.$value->author_username:
                            'null';
                            $post_link = ($value->network=='twitter')?'http://twitter.com/'.$value->author_username.
                            '/status/'.$value->post_id.'/':'null';
                            $data = array('id' => $cnt, 'text' => trim(preg_replace('/^@[a-zA-Z0-9_]+/', '',
                            $value->post_text)), 'post_id_str' => $value->post_id . '_str',
                            'author' => $value->author_username, 'author_avatar'=> $value->author_avatar,
                             'date' => $value->adj_pub_date, 'author_link' => $author_link, 'post_link' =>
                            $post_link );
                            $result .=json_encode($data) . ",\n";
                            flush();
                        }
                        $result .=']}';
                    } else {
                        $result .='"status":"failed","message":"Private post"}';
                    }
                } else {
                    $result .='"status":"failed","message":"Post does not exist"}';
                }
            } else {
                $result .='"status":"failed","message":"No ThinkUp thread specified"}';
            }
        } else {
            $result .='"status":"failed","message":"ThinkUp embedding is not enabled"}';
        }
        $result .=']);';
        return $result;
    }
}