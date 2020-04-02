<?php

require_once('../app/core/Controller.php');
class Upload extends Controller
{
    private $model;
    private $uploadOk;
    public function index()
    {
        $this->model = $this->model('UploadModel');
        $this->view('fileupload/upload', ['title' => $this->model->getTitle()]);

        if ($_SESSION['role'] != "dokters" && $_SESSION['role'] != "dietisten" && $_SESSION['role'] != "fysiotherapeuten" && $_SESSION['role'] != "psychologen") {
            header("Location: /public/login");
            $this->uploadOk = 0;
            exit();
        }
        $patiënt = $_POST['patiënt'];
        //check if patiënt exists
        if (!$this->model->getDB()->query('checkpatiënt', [$patiënt])) {
            echo "Patiënt bestaat niet";
            $this->uploadOk = 0;
            exit();
        }
        if($_SESSION['role'] == "dokters"){
            $dokter = $this->model->getDB()->query('getDokterPatiënt', [$patiënt]);
            if ($dokter != $_SESSION['uid']) {
                echo "U heeft geen recht tot het uploaden van files voor deze patiënt";
                $this->uploadOk = 0;
                exit();
            }
        }
         else if($_SESSION['role'] == "dietisten" || $_SESSION['role'] == "fysiotherapeuten" || $_SESSION['role'] == "psychologen"){
            $column = $this->model->getDB()->query('convertGroupToColumn', [$_SESSION['role']]);
            $gecontracteerde = $this->model->getDB()->query('getGecontracteerd', [$patiënt, $column]);
            if($gecontracteerde != $_SESSION['uid']){
                echo "U heeft geen recht tot het uploaden van files voor deze patiënt";
                $this->uploadOk = 0;
                exit();
            }
        } 
        $target_dir = "{$_SERVER["DOCUMENT_ROOT"]}/app/uploads/{$patiënt}/";
        //check if directory exists, if not make new directory with correct permissions
        if (!file_exists($target_dir)) {
            $old = umask(0);
            mkdir($target_dir, 0760, true);
            umask($old);
        }
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $this->uploadOk = 1;
        $FileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        // Check if file is doc(x), pdf, etc
        if (isset($_POST["submit"])) {
            $this->checkmtype($FileType);
        }
        // Check if file already exists
        $this->checkexist($target_file);
        // Check file size
        $this->checksize();
        // Allow certain file formats
        $this->checkfiletype($FileType);
        //check check file name
        $this->checkfilename();
        //check file name length
        $this->checkfilenamelength($FileType);
        // Check if $uploadOk is set to 0 by an error
        $this->uploadfile($target_file);
    }
    //check mimetype of uploaded document
    public function checkmtype($FileType)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        //check if magic database can be used
        if (!$finfo) {
            echo "opening fileinfodb failed";
            $this->uploadOk = 0;
            exit();
        }
        $mtype = finfo_file($finfo, $_FILES["fileToUpload"]["tmp_name"]);
        finfo_close($finfo);
        if (
            $mtype == ("application/msword") ||
            $mtype == ("application/pdf") ||
            $mtype == ("application/vnd.openxmlformats-officedocument.wordprocessingml.document") ||
            $mtype == ("application/x-iwork-pages-sffpages ") ||
            $mtype == ("application/vnd.ms-excel") ||
            $mtype == ("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
        ) {
            $this->uploadOk = 1;
        } else {
            echo "Bestand is niet een pdf, word, pages of excel bestand";
            $this->uploadOk = 0;
        }
    }
    //check if the file exists
    public function checkexist($target_file)
    {
        if (file_exists($target_file)) {
            echo "Bestand bestaat al.";
            $this->uploadOk = 0;
        }
    }
    //check if the size of the file is too big
    public function checksize()
    {
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            echo "Bestand is te groot";
            $this->uploadOk = 0;
        }
    }
    //check if the extension is permitted
    public function checkfiletype($FileType)
    {
        if ($FileType != "pdf" && $FileType != "doc" && $FileType != "docx" && $FileType != "pages") {
            echo "Alleen bestanden met een pdf, doc, docx en pages extentie zijn toegestaan";
            $this->uploadOk = 0;
        }
    }
    //check if the filename contains non-permitted characters
    public function checkfilename()
    {
        if (!preg_match("`^[-0-9A-Z_\. ]+$`i", $_FILES["fileToUpload"]["name"])) {
            echo "Bestandsnaam is ongeldig";
            $this->uploadOk = 0;
        }
    }
    //check if the filename is too long
    public function checkfilenamelength($FileType)
    {
        if (strlen(basename($_FILES["fileToUpload"]["name"], '.' . $FileType)) > 40) {
            echo "Bestandsnaam is te lang";
            $this->uploadOk = 0;
        }
    }
    //upload the file to the database and server
    public function uploadfile($target_file)
    {
        if ($this->uploadOk == 0) {
            echo "Bestand is niet geupload";
            // if everything is ok, try to upload file
        } else {
            //check database connection
            if (!$this->model->getDB()->getConnection()) {
                echo "connection failed";
                return false;
            }
            $datetime = date_create()->format('Y-m-d H:i:s');
            //move temporary file to server with correct permissions
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                chmod($target_file, 0760);
                //upload filepath to database
                $this->model->getDB()->query('uploadDocument', [$target_file, $_POST['patiënt'], $_SESSION['uid'], $_POST['title'], $datetime]);
                header("Location: /public/fileupload?state=uploaded");
                exit();
            } else {
                echo "Er is iets misgegaan bij het uploaden van het bestand.";
            }
        }
    }
}
