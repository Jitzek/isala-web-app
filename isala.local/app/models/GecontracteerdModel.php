<?php

require_once('../app/models/UserModel.php');

/**
 * Model containing all necessary data of a Gecontracteerd user
*/
class GecontracteerdModel extends UserModel
{
    private $adres;
    private $telefoonnummer;

    public function __construct($uid)
    {
        parent::__construct($uid);
        $this->adres = $this->db->query('getAdres', [$this->getUid(), $this->db->query('convertGroupToTable', [$this->getGroup()])]);
        $this->telefoonnummer = $this->db->query('getTelefoonnummer', [$this->getUid(), $this->db->query('convertGroupToTable', [$this->getGroup()])]);
    }

    public function getAdres()
    {
        return $this->adres;
    }
    
    public function getTelefoonnummer()
    {
        return $this->telefoonnummer;
    }
}