<?php 
class Link {
    var $id;
    var $url;
    var $expanded_url;
    var $title;
    var $clicks;
    var $post_id;
    var $is_image;
    var $error;
    var $img_src; //optional
    
    var $container_tweet; //optional
    
    function Link($val) {
        if (isset($val["id"])) {
            $this->id = $val["id"];
        }
        
        $this->url = $val["url"];
        if (isset($val["expanded_url"])) {
            $this->expanded_url = $val["expanded_url"];
        }
        
        if (isset($val["title"])) {
            $this->title = $val["title"];
        }
        
        if (isset($val["clicks"])) {
            $this->clicks = $val["clicks"];
        }
        
        if (isset($val["post_id"])) {
            $this->post_id = $val["post_id"];
        }
        
        if (isset($val["is_image"]) && $val["is_image"] == 1) {
            $this->is_image = true;
        } else {
            $this->is_image = false;
        }
        if (isset($val["error"])) {
            $this->error = $val["error"];
        }
    }
    
}

class LinkDAO extends MySQLDAO {
    //Construct is located in parent
    
    function insert($url, $expanded, $title, $post_id, $is_image = 0) {
        $expanded = mysql_real_escape_string($expanded);
        $title = mysql_real_escape_string($title);
        
        $q = "
			INSERT INTO
				#prefix#links (url, expanded_url, title, post_id, is_image)
				VALUES (
					'{$url}', '{$expanded}', '{$title}', ".$post_id.", ".$is_image.");";
					
        $foo = $this->executeSQL($q);
        if (mysql_affected_rows() > 0)
            return true;
        else
            return false;
    }
    
    function saveExpandedURL($short_link, $expanded_url, $title = '', $is_image = 0) {
        $expanded_url = mysql_real_escape_string($expanded_url);
        $short_link = mysql_real_escape_string($short_link);
        $title = mysql_real_escape_string($title);
        
        $q = "
			UPDATE #prefix#links 
			SET expanded_url = '{$expanded_url}', title = '{$title}', is_image = $is_image
			WHERE url = '{$short_link}'";
			
        $this->executeSQL($q);
        if (mysql_affected_rows() > 0) {
            $this->logger->logStatus("Expanded URL $expanded_url for $short_link saved", get_class($this));
            return true;
        } else {
            $this->logger->logStatus("Expanded URL NOT saved", get_class($this));
            return false;
        }
    }
    
    function saveExpansionError($short_link, $error_text) {
        $error_text = mysql_real_escape_string($error_text);
        $short_link = mysql_real_escape_string($short_link);
        
        $q = "
			UPDATE #prefix#links 
			SET error = '{$error_text}'
			WHERE url = '{$short_link}'";
			
        $this->executeSQL($q);
        if (mysql_affected_rows() > 0) {
            $this->logger->logStatus("Error '$error_text' saved for link ID $short_link saved", get_class($this));
            return true;
        } else {
            $this->logger->logStatus("Error '$error_text' for URL NOT saved", get_class($this));
            return false;
        }
    }
    
    function update($url, $expanded, $title, $post_id, $is_image = 0) {
        $expanded = mysql_real_escape_string($expanded);
        $title = mysql_real_escape_string($title);
        
        $q = "
			UPDATE #prefix#links 
			SET expanded_url = '{$expanded}', title = '{$title}', post_id=".$post_id.", is_image=".$is_image."
			WHERE url = '{$url}';";
			
        $foo = $this->executeSQL($q);
        if (mysql_affected_rows() > 0)
            return true;
        else
            return false;
    }

    
    function getLinksByFriends($user_id) {
        $q = "
			SELECT l.*, p.*, pub_date - interval 8 hour as adj_pub_date  
			FROM #prefix#links l
			INNER JOIN #prefix#posts p
			ON p.post_id = l.post_id
			WHERE p.author_user_id in (SELECT user_id FROM #prefix#follows f WHERE f.follower_id = ".$user_id.")
			ORDER BY l.post_id DESC
			LIMIT 15";
			
        $sql_result = $this->executeSQL($q);
        $links = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $l = new Link($row);
            $l->container_tweet = new Post($row);
            $links[] = $l;
        }
        mysql_free_result($sql_result);
        return $links;
    }
    
    function getPhotosByFriends($user_id) {
        $q = "
			SELECT l.*, p.*, pub_date - interval 8 hour as adj_pub_date  
			FROM #prefix#links l
			INNER JOIN #prefix#posts p
			ON p.post_id = l.post_id
			WHERE is_image = 1 and p.author_user_id in (SELECT user_id FROM #prefix#follows f WHERE f.follower_id = ".$user_id.")
			ORDER BY l.post_id DESC
			LIMIT 15";
			
        $sql_result = $this->executeSQL($q);
        $links = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $l = new Link($row);
            $l->container_tweet = new Post($row);
            $links[] = $l;
        }
        mysql_free_result($sql_result);
        return $links;
    }
    
    function getLinksToExpand($limit = 1500) {
        $q = "SELECT l1.url
FROM (
SELECT l.url, l.post_id 
FROM #prefix#links l
WHERE l.expanded_url = '' and l.error = ''
ORDER BY post_id DESC LIMIT $limit) as l1
GROUP BY l1.url";
			
        $sql_result = $this->executeSQL($q);
        $links = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $links[] = $row["url"];
        }
        mysql_free_result($sql_result);
        return $links;
    }
    
    function getLinksToExpandByURL($url) {
        $q = "
			SELECT l.url
			FROM #prefix#links l
			WHERE l.expanded_url = '' AND l.url LIKE '$url%' AND l.error = '' 
			GROUP BY l.url";
			
        $sql_result = $this->executeSQL($q);
        $links = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $links[] = $row["url"];
        }
        mysql_free_result($sql_result);
        return $links;
    }
    
    function getLinkById($id) {
        $q = "
			SELECT l.*
			FROM #prefix#links l
			WHERE l.id = $id";
			
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        $link = new Link($row);
        mysql_free_result($sql_result);
        return $link;
    }
    
    function getLinkByUrl($url) {
        $url = mysql_real_escape_string($url);
        
        $q = "
			SELECT l.*
			FROM #prefix#links l
			WHERE l.url = '$url'";
			
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        $link = new Link($row);
        mysql_free_result($sql_result);
        return $link;
    }
    
}

?>
