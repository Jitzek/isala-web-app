<?php

require_once('../app/models/UserModel.php');

/**
 * Model containing all necessary data of a Gecontracteerd user
*/
class GecontracteerdModel extends UserModel
{
    private $adress;

    public function __construct($uid)
    {
        parent::__construct($uid);
        $this->adress = $this->db->query('getAdres', [$this->getUid(), $this->db->query('convertGroupToTable', [$this->getGroup()])]);
    }

    public function getAdress()
    {
        return $this->adress;
    }
}