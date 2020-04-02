<?php

require_once('../app/models/UserModel.php');

/**
 * Model containing all necessary data of a Patiënt user
*/
class PatiëntModel extends UserModel
{
    private $adres;
    private $geboortedatum;
    private $geslacht;
    private $telefoonnummer;
    private $dokter;
    private $diëtist;
    private $fysiotherapeut;
    private $psycholoog;

    public function __construct($uid)
    {
        parent::__construct($uid);
        $this->adres = $this->db->query('getAdres', [$this->getUid(), $this->db->query('convertGroupToTable', [$this->getGroup()])]);
        $this->geboortedatum = date('d-m-Y', strtotime($this->db->query('getGeboorteDatum', [$this->getUid(), $this->db->query('convertGroupToTable', [$this->getGroup()])])));
        $this->geslacht = $this->db->query('getGeslacht', [$this->getUid(), $this->db->query('convertGroupToTable', [$this->getGroup()])]);
        $this->telefoonnummer = $this->db->query('getTelefoonnummer', [$this->getUid(), $this->db->query('convertGroupToTable', [$this->getGroup()])]);
        $this->dokter = $this->db->query('getGecontracteerd', [$this->getUid(), 'Dokter']);
        $this->diëtist = $this->db->query('getGecontracteerd', [$this->getUid(), 'Diëtist']);
        $this->fysiotherapeut = $this->db->query('getGecontracteerd', [$this->getUid(), 'Fysiotherapeut']);
        $this->psycholoog = $this->db->query('getGecontracteerd', [$this->getUid(), 'Psycholoog']);
    }

    public function getMeasurements($category, $only_most_recent = FALSE)
    {
        return $this->db->query('getMeasurements', [$this->getUid(), $category, $only_most_recent]);
    }

    public function getAdres()
    {
        return $this->adres;
    }
    
    public function getGeboorteDatum()
    {
        return $this->geboortedatum;
    }

    public function getLeeftijd()
    {
        return date('Y') - date('Y', strtotime($this->geboortedatum));
    }
    
    public function getGeslacht()
    {
        return $this->geslacht;
    }
    
    public function getTelefoonnummer()
    {
        return $this->telefoonnummer;
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