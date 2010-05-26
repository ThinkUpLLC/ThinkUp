<?php
interface InstanceDAO {
    public function insert($network_user_id, $network_username, $network = "twitter", $viewer_id = false);

    public function getFreshestByOwnerId($owner_id);

    public function getInstanceOneByLastRun($order);

    public function getByUsername($username);

    public function getByUserId($network_user_id);

    public function getAllInstances($order = "DESC", $only_active = false, $network = "twitter");

    public function getByOwner($owner, $force_not_admin = false);

    public function getByOwnerAndNetwork($o, $network, $force_not_admin = false);

    public function setPublic($username, $public);

    public function setActive($username, $active);

    public function save($instance_object, $user_xml_total_posts_by_owner, $logger = false, $api = false);

    public function updateLastRun($id);

    public function isUserConfigured($username);
}
?>