<?php

require_once('../app/models/UserModel.php');

/**
 * Model containing all necessary data of a Patiënt user
*/
class PatiëntModel extends UserModel
{
    private $adress;
    private $dokter;
    private $diëtist;
    private $fysiotherapeut;
    private $psycholoog;
    private $medical_data;

    public function __construct($uid)
    {
        parent::__construct($uid);
        $this->adress = $this->db->query('getAdres', [$this->getUid(), $this->db->query('convertGroupToTable', [$this->getGroup()])]);
        $this->dokter = $this->db->query('getGecontracteerd', [$this->getUid(), 'Dokter']);
        $this->diëtist = $this->db->query('getGecontracteerd', [$this->getUid(), 'Diëtist']);
        $this->fysiotherapeut = $this->db->query('getGecontracteerd', [$this->getUid(), 'Fysiotherapeut']);
        $this->psycholoog = $this->db->query('getGecontracteerd', [$this->getUid(), 'Psycholoog']);
        $this->medical_data = json_decode($this->db->query('getMedicalData', [$this->getUid()]));
    }

    public function getMedicalData()
    {
        return $this->medical_data;
    }

    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * Gets Gecontracteerd by it's variable name
     * 'dokter', 'diëtist', 'fysiotherapeut', 'psycholoog'
    */
    public function getGecontracteerd($type)
    {
        if (!in_array($type, ['dokter', 'diëtist', 'fysiotherapeut', 'psycholoog'])) return '';
        return $this->$type;
    }
}