<?php

class DBQueries
{
    private $conn;
    private $result;
    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function succesfulLoginAttempt($uid, $group, $ip)
    {
        // Column isn't user determined but escape just incase
        $column = $this->conn->real_escape_string($this->convertGroupToTable($group));
        // Reset login attempts to give user another 5 tries without resetting the time blocked penalty
        $query = $this->conn->prepare("UPDATE BlockedIP SET Tries = FLOOR(Tries / 5) * 5 WHERE {$column} = ? AND IP = ?");
        if (!$query) return;
        $query->bind_param("ss", $uid, $ip);
        $query->execute();
        $query->close();
    }

    public function succesfulTwoFactor($uid, $group, $ip)
    {
        // Column isn't user determined but escape just incase
        $column = $this->conn->real_escape_string($this->convertGroupToTable($group));
        // Reset Failed Attempts
        $query = $this->conn->prepare("DELETE FROM BlockedIP WHERE {$column} = ? AND IP = ?");
        if (!$query) return;
        $query->bind_param("ss", $uid, $ip);
        $query->execute();
        $query->close();
    }

    public function failedLoginAttempt($uid, $group, $ip)
    {
        // Column isn't user determined but escape just incase
        $column = $this->conn->real_escape_string($this->convertGroupToTable($group));
        // Insert IP and UID into Blocked IP if a combination of the don't exist already
        $query = $this->conn->prepare("INSERT INTO BlockedIP (IP, IsBlocked, Tries, {$column})
                                        SELECT ?, FALSE, 0, ? FROM DUAL
                                        WHERE NOT EXISTS (
                                            SELECT IP FROM BlockedIP WHERE IP = ? AND {$column} = ?
                                        ) LIMIT 1;");
        if (!$query) return;
        $query->bind_param("ssss", $ip, $uid, $ip, $uid);
        $query->execute();
        $query->close();

        // Update amount of Tries by adding 1
        $query = $this->conn->prepare("UPDATE BlockedIP SET Tries = Tries + 1 WHERE {$column} = ? AND IP = ?");
        $query->bind_param("ss", $uid, $ip);
        $query->execute();
        $query->close();
        $this->blockIPIfLimitExceeded($uid, $group, $ip);
    }

    private function blockIPIfLimitExceeded($uid, $group, $ip)
    {
        // Column isn't user determined but escape just incase
        $column = $this->conn->real_escape_string($this->convertGroupToTable($group));
        $limit = 5;
        $query = $this->conn->prepare("SELECT TRIES FROM BlockedIP WHERE {$column} = ? AND IP = ?");
        if (!$query) return;
        $query->bind_param("ss", $uid, $ip);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        // Get expiration date of block
        $minutes = floor(($this->result / $limit < 30 ? $this->result / $limit : 30));
        $expiration = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes", strtotime(date("Y-m-d H:i:s"))));

        $query = $this->conn->prepare("UPDATE BlockedIP SET IsBlocked = TRUE, Expiration = ? WHERE IP = ? AND {$column} = ? AND (TRIES % ?) = 0");
        $query->bind_param("sssi", $expiration, $ip, $uid, $limit);
        $query->execute();
        $query->close();
        return;
    }

    public function blockedIPArray($uid, $group)
    {
        // Column isn't user determined but escape just incase
        $column = $this->conn->real_escape_string($this->convertGroupToTable($group));
        $query = $this->conn->prepare("SELECT IP FROM BlockedIP WHERE {$column} = ? AND IsBlocked = TRUE");
        if (!$query) return [];
        $query->bind_param("s", $uid);
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row['IP'];
        }
        $query->close();
        return isset($results) ? $results : [];
    }

    public function blockExpired($uid, $group, $ip)
    {
        // Column isn't user determined but escape just incase
        $column = $this->conn->real_escape_string($this->convertGroupToTable($group));
        // Check if current date has surpassed expiration date
        $query = $this->conn->prepare("SELECT Expiration FROM BlockedIP WHERE IP = ? AND {$column} = ?");
        if (!$query) return true;
        $query->bind_param("ss", $ip, $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        if (strtotime($this->result) > strtotime(date("Y-m-d H:i:s"))) {
            // If current date has not yet surpassed expiration date return false
            return false;
        }
        // If current date has surpassed expiration date Remove block and date
        $query = $this->conn->prepare("UPDATE BlockedIP SET IsBlocked = FALSE, Expiration = NULL WHERE IP = ? AND {$column} = ?");
        $query->bind_param("ss", $ip, $uid);
        $query->execute();
        $query->close();
        return true;
    }

    public function isUpdateLastPasswordChangeEmpty($uid)
    {
        $query = $this->conn->prepare("SELECT Last_Password_Change FROM Patiënt WHERE `UID` = ?");
        if (!$query) return;
        $query->bind_param("s", $uid);
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row['Last_Password_Change'];
        }
        $query->close();
        return $results[0];
    }

    public function updateLastPasswordChange($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $newdate = date("Y-m-d");
        $query = $this->conn->prepare("UPDATE {$table} SET Last_Password_Change = ? WHERE `UID` = ?");
        if (!$query) return;
        $query->bind_param("ss", $newdate, $uid);
        $query->execute();
        $query->close();
    }

    public function set2FA($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $tfa = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 6)), 0, 6);
        $query = $this->conn->prepare("UPDATE {$table} SET 2FA = ? WHERE `UID` = ?");
        if (!$query) return;
        $query->bind_param("ss", $tfa, $uid);
        $query->execute();
        $query->close();
    }

    public function get2FA($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("SELECT 2FA FROM {$table} WHERE `UID` = ?");
        $query->bind_param("s", $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function setToken($uid, $table, $token)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("UPDATE {$table} SET Token = ? WHERE `UID` = ?");
        if (!$query) return;
        $query->bind_param("ss", $token, $uid);
        $query->execute();
        $query->close();
    }

    public function getToken($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("SELECT Token FROM {$table} WHERE `UID` = ?");
        $query->bind_param("s", $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function createUser($uid, $adres, $geboortedatum, $geslacht, $telefoonnummer, $dokter)
    {
        $query = $this->conn->prepare("INSERT INTO Patiënt (`UID`, Adres, GeboorteDatum, Geslacht, Telefoonnummer, Dokter, Accepted_Cookie) VALUES (?, ?, ?, ?, ?, ?,false)");
        $query->bind_param("ssssss", $uid, $adres, $geboortedatum, $geslacht, $telefoonnummer, $dokter);
        $query->execute();
        $query->close();
        return true;
    }

    public function patiëntExists($uid)
    {
        $query = $this->conn->prepare("SELECT COUNT(`UID`) FROM Patiënt WHERE `UID` = ?");
        $query->bind_param("s", $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return ($this->result == 0 ? false : true);
    }

    public function setCookie($uid, $table, $state)
    {
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("UPDATE {$table} SET Accepted_Cookie = ? WHERE `UID` = ?");
        $query->bind_param("ss", $state, $uid);
        $query->execute();
        $query->close();
    }

    public function getCookie($uid, $table)
    {
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("SELECT Accepted_Cookie FROM {$table} WHERE `UID` = ?");
        $query->bind_param("s", $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getAdres($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("SELECT Adres FROM {$table} WHERE `UID` = ?");
        if (!$query) return FALSE;
        $query->bind_param("s", $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getGeboorteDatum($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("SELECT GeboorteDatum FROM {$table} WHERE `UID` = ?");
        if (!$query) return FALSE;
        $query->bind_param("s", $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getGeslacht($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("SELECT Geslacht FROM {$table} WHERE `UID` = ?");
        if (!$query) return FALSE;
        $query->bind_param("s", $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getTelefoonnummer($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $query = $this->conn->prepare("SELECT Telefoonnummer FROM {$table} WHERE `UID` = ?");
        if (!$query) return FALSE;
        $query->bind_param("s", $uid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getGecontracteerd($patiëntuid, $column)
    {
        // Make sure $table can not be edited by user
        $column = $this->conn->real_escape_string($column);
        $query = $this->conn->prepare("SELECT {$column} FROM Patiënt WHERE `UID` = ?");
        $query->bind_param("s", $patiëntuid);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getMeasurements($uid, $category, $only_most_recent = FALSE)
    {
        if ($only_most_recent) {
            $query = $this->conn->prepare("SELECT * FROM Meting WHERE Patiënt = ? AND Categorie = ? AND ID NOT IN 
                                            (SELECT ID FROM 
                                                (SELECT * FROM Meting ORDER BY Datum, Tijd DESC) as t2 GROUP BY Onderwerp HAVING COUNT(*) > 1)
                                            ");
	    $query->bind_param("ssss", $uid, $category, $uid, $category);
        } else {
	    $query = $this->conn->prepare("SELECT * FROM Meting WHERE Patiënt = ? AND Categorie = ?");
	    $query->bind_param("ss", $uid, $category);
	}
        $query->execute();
        $this->results = $query->get_result();
        while ($row = $this->results->fetch_assoc()) {
            $result[] = $row;
        }
        $query->close();
        return isset($result) ? $result : [];
    }

    public function getPatientsOfGecontracteerd($uid, $role)
    {
        // Make sure $role can not be edited by user
        $role = $this->conn->real_escape_string($role);
        $query = $this->conn->prepare("SELECT `UID` FROM Patiënt WHERE {$role} = ?");
        if (!$query) return [];
        $query->bind_param("s", $uid);
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row['UID'];
        }
        $query->close();
        return isset($results) ? $results : [];
    }

    public function insertAuditlog($data)
    {
        $column = $this->conn->real_escape_string($this->convertGroupToTable($data['group']));
        $date = date('Y-m-d H:i:s');
        $query = $this->conn->prepare("INSERT INTO Auditlog ({$column}, action_time, request_url, `message`, ip) VALUES (? ,?, ?, ?, ?)");
        if (!$query) return true;
        $query->bind_param("sssss", $data['uid'], $date, $data['url'], $data['msg'], $data['ip']);
        $query->execute();
        $query->close();
        return true;
    }

    public function uploadDocument($path, $patiënt, $owner, $title, $date)
    {
        $query = $this->conn->prepare("INSERT INTO Document (`Path`, Patiënt, Eigenaar, Titel, Datum) VALUES (?, ?, ?, ?, ?)");
        $query->bind_param("sssss", $path, $patiënt, $owner, $title, $date);
        $query->execute();
        $query->close();
    }

    public function getDocs($owner, $patiënt)
    {
        if ($owner == "") {
            $query = $this->conn->prepare("SELECT Titel, Eigenaar, Datum, ID FROM Document WHERE Patiënt = ?");
            $query->bind_param("s", $patiënt);
            $query->execute();
            $result = $query->get_result();
            $query->close();
            return $result;
        } else {
            $query = $this->conn->prepare("SELECT Titel, Eigenaar, Datum, ID FROM Document WHERE Eigenaar = ? AND Patiënt = ?");
            $query->bind_param("ss", $owner, $patiënt);
            $query->execute();
            $result = $query->get_result();
            $query->close();
            return $result;
        }
    }

    public function getDocPath($id)
    {
        $query = $this->conn->prepare("SELECT `Path` FROM Document WHERE ID = ?");
        $query->bind_param("i", $id);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getOwnerDoc($id)
    {
        $query = $this->conn->prepare("SELECT Eigenaar FROM Document WHERE ID = ?");
        $query->bind_param("i", $id);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getPatiëntdocument($id)
    {
        $query = $this->conn->prepare("SELECT Patiënt FROM Document WHERE ID = ?");
        $query->bind_param("i", $id);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getDokterPatiënt($patiënt)
    {
        $query = $this->conn->prepare("SELECT Dokter FROM Patiënt WHERE `UID` = ?");
        $query->bind_param("s", $patiënt);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();
        return $this->result;
    }

    public function getGecontracteerdWithoutCurrent($uid)
    {
        if ($query = $this->conn->prepare("SELECT `UID` FROM Gecontracteerd WHERE UID != ?")) {
            $query->bind_param("s", $uid);
            $query->execute();
            $result = $query->get_result();
            while ($row = $result->fetch_assoc()) {
                $results[] = $row['UID'];
            }
            $query->close();
        } else {
            return NULL;
        }

        return $results;
    }

    public function linkGecontracteerdenToUsers($uid, $group, $guid, $dokter)
    {
        if ($query = $this->conn->prepare("UPDATE Patiënt SET {$group} = ? WHERE `UID` = ? AND Dokter = ?")) {
            $query->bind_param("sss", $guid, $uid, $dokter);
            $query->execute();
            $query->close();
            return true;
        } else {
            return false;
        }
    }

    public function getDietistFromPatient($uid)
    {
        if ($query = $this->conn->prepare("SELECT Diëtist FROM Patiënt WHERE UID = ?")) {
            $query->bind_param("s", $uid);
            $query->execute();
            $query->bind_result($this->result);
            $query->fetch();
            $query->close();
        } else {
            return NULL;
        }
        return $this->result;
    }

    public function getFysiotherapeutFromPatient($uid)
    {
        if ($query = $this->conn->prepare("SELECT Fysiotherapeut FROM Patiënt WHERE UID = ?")) {
            $query->bind_param("s", $uid);
            $query->execute();
            $query->bind_result($this->result);
            $query->fetch();
            $query->close();
        } else {
            return NULL;
        }
        return $this->result;
    }

    public function getPsycholoogFromPatient($uid)
    {
        if ($query = $this->conn->prepare("SELECT Psycholoog FROM Patiënt WHERE UID = ?")) {
            $query->bind_param("s", $uid);
            $query->execute();
            $query->bind_result($this->result);
            $query->fetch();
            $query->close();
        } else {
            return NULL;
        }
        return $this->result;
    }

    public function convertGroupToTable($group)
    {
        switch ($group) {
            case 'patienten':
                return 'Patiënt';
            case 'dokters':
                return 'Gecontracteerd';
            case 'dietisten':
                return 'Gecontracteerd';
            case 'psychologen':
                return 'Gecontracteerd';
            case 'fysiotherapeuten':
                return 'Gecontracteerd';
            case 'administrators':
                return 'Admin';
            default:
                return '';
        }
    }

    public function convertGroupToColumn($group)
    {
        switch ($group) {
            case 'patienten':
                return 'Patiënt';
            case 'dokters':
                return 'Dokter';
            case 'dietisten':
                return 'Diëtist';
            case 'psychologen':
                return 'Psycholoog';
            case 'fysiotherapeuten':
                return 'Fysiotherapeut';
            case 'administrators':
                return 'Admin';
            default:
                return '';
        }
    }
}
