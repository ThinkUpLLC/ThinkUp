<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/tests/classes/mock.URLExpander.php
 *
 * Copyright (c) 2012 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * Mock URL Expander
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class URLExpander {
    public static function expandURL($tinyurl, $original_link, $current_number, $total_number, $link_dao, $logger) {
        switch ($tinyurl) {
            case "http://bit.ly/a5VmbO":
                $exp_url = "http://www.thewashingtonnote.com/archives/2010/04/communications/";
                break;
            case "http://www.thewashingtonnote.com/archives/2010/04/communications/":
                $exp_url = "http://www.thewashingtonnote.com/archives/2010/04/communications/";
                break;
            case "http://bit.ly":
                $exp_url = "http://bit.ly";
                break;
            case "http://t.co/xRRz4lk":
                $exp_url = "http://www.macworld.com/article/161927/2011/08/steve_jobs_resigns_as_apple_ceo.html#lsrc.".
                "twt_danfrakes";
                break;
            case "http://www.macworld.com/article/161927/2011/08/steve_jobs_resigns_as_apple_ceo.html#lsrc.twt_".
            "danfrakes":
            $exp_url = "http://www.macworld.com/article/161927/2011/08/steve_jobs_resigns_as_apple_ceo.html#lsrc.".
                "twt_danfrakes";
            break;
            case "http://bit.ly/dPOYo3":
                $exp_url =  "http://static.ak.fbcdn.net/rsrc.php/zw/r/ZEKh4ZZQY74.png";
                break;
            case "http://static.ak.fbcdn.net/rsrc.php/zw/r/ZEKh4ZZQY74.png":
                $exp_url =  "http://static.ak.fbcdn.net/rsrc.php/zw/r/ZEKh4ZZQY74.png";
                break;
            case "http://bit.ly/41":
                $exp_url =  "http://bitly.com/a/warning?url=http%3a%2f%2fwww%2ealideas%2ecom%2f&hash=41";
                break;
            case "http://bitly.com/a/warning?url=http%3a%2f%2fwww%2ealideas%2ecom%2f&hash=41":
                $exp_url =  "http://bitly.com/a/warning?url=http%3a%2f%2fwww%2ealideas%2ecom%2f&hash=41";
                break;
            case "http://t.co/LfN0PXm":
                $exp_url =  "http://vimeo.com/27427184";
                break;
            case "http://vimeo.com/27427184":
                $exp_url =  "http://vimeo.com/27427184";
                break;
            case "http://bit.ly/qpBNce":
                $exp_url =  "http://twitpic.com/6bheho";
                break;
            case "http://twitpic.com/6bheho":
                $exp_url =  "http://twitpic.com/6bheho";
                break;
            case "http://bit.ly/qpBNce":
                $exp_url =  "http://twitpic.com/6bheho";
                break;
            case "http://twitpic.com/6bheho":
                $exp_url =  "http://twitpic.com/6bheho";
                break;
            case "http://t.co/oDI8D34":
                $exp_url =  "http://yfrog.com/gz2inwrj";
                break;
            case "http://yfrog.com/gz2inwrj":
                $exp_url =  "http://yfrog.com/gz2inwrj";
                break;
            case "http://bit.ly/40":
                $exp_url =  "http://static.ak.fbcdn.net/rsrc.php/zw/r/ZEKh4ZZQY74.png";
                break;
            case "http://static.ak.fbcdn.net/rsrc.php/zw/r/ZEKh4ZZQY74.png":
                $exp_url =  "http://static.ak.fbcdn.net/rsrc.php/zw/r/ZEKh4ZZQY74.png";
                break;
            case "http://t.co/MZrNmBc":
                $exp_url =  "https://secure.aclu.org/site/Advocacy?cmd=display&page=UserAction&id=3561&s_subsrc=110819".
                "_CAyouth_tw";
                break;
            case "https://secure.aclu.org/site/Advocacy?cmd=display&page=UserAction&id=3561&s_subsrc=110819_CAyouth_tw":
                $exp_url =  "https://secure.aclu.org/site/Advocacy?cmd=display&page=UserAction&id=3561&s_subsrc=11081".
                "9_CAyouth_tw";
                break;
            case "http://thinkupapp.com/":
                $exp_url =  "http://thinkupapp.com/";
                break;
            case "http://wp.me/p1fxNB-2F":
                $exp_url =  "";
                break;
            case "http://instagr.am/p/oyQ6/":
                $exp_url = "http://instagr.am/p/oyQ6/";
                break;
            default:
                $exp_url = '';
        }
        return $exp_url;
    }
}
