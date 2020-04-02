<?php
/**
 * Downloads the file
 */
require_once('../app/core/Controller.php');
class Download extends Controller
{
    private $model;
    public function index()
    {
        $this->model = $this->model('DownloadModel');
        //check database connection
        if (!$this->model->getDB()->getConnection()) {
            echo "connection failed";
            return false;
        }
        $this->authorize();
        //Get the path of the document with the id of the document
        $filename = $this->model->getDB()->query('getDocPath', [$_POST['ID']]);
        //if the file exists download the file
        if (file_exists($filename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
        }
    }
    /**
     * authorize checks if the user is permitted to download the file
     */
    public function authorize(){
        //if the session is empty goto login
        if(!isset($_SESSION['role'])){
            header("Location: /public/login");
            exit();
        }
        //if role is dokter: get patient and get the dokter of the patient, if the dokter of the patient is not the same as the current dokter, goto login
        else if($_SESSION['role'] == "dokters"){
            $patiënt = $this->model->getDB()->query('getPatiëntdocument', [$_POST['ID']]);
            $dokter = $this->model->getDB()->query('getDokterPatiënt', [$patiënt]);
            if($_SESSION['uid'] != $dokter){
                header("Location: /public/login");
                exit();
            }
        }
        //check if current patient is the reciever of the document
        else if($_SESSION['role'] == "patiënten"){
            $patiënt = $this->model->getDB()->query('getPatiëntdocument', [$_POST['ID']]);
            if($_SESSION['uid'] != $patiënt){
                header("Location: /public/login");
                exit();
            }
        }
        //check if the current user is the owner of the document
        else{
            $owner = $this->model->getDB()->query('getOwnerDoc', [$_POST['ID']]);
            echo "test";
            if($owner != $_SESSION['uid']){
                header("Location: /public/home");
                exit();
            }
        }

    }
}
