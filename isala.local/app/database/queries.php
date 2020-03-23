<?php

class DBQueries
{
    private $conn;
    private $result;
    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function succesfulLoginAttempt($uid, $ip)
    {
        // Reset login attempts to give user another 5 tries without resetting the time blocked penalty
        $query = $this->conn->prepare("UPDATE BlockedIP SET Tries = FLOOR(Tries / 5) * 5 WHERE `UID` = ? AND IP = ?");
        $query->bind_param("ss", $uid, $ip);
        $query->execute();
        $query->close();
    }

    public function succesfulTwoFactor($uid, $ip)
    {
        // Reset Failed Attempts
        $query = $this->conn->prepare("DELETE FROM BlockedIP WHERE `UID` = ? AND IP = ?");
        $query->bind_param("ss", $uid, $ip);
        $query->execute();
        $query->close();
    }

    public function failedLoginAttempt($uid, $ip)
    {
        // Insert IP and UID into Blocked IP if a combination of the don't exist already
        $query = $this->conn->prepare(  "INSERT INTO BlockedIP (IP, IsBlocked, Tries, `UID`)
                                        SELECT ?, FALSE, 0, ? FROM DUAL
                                        WHERE NOT EXISTS (
                                            SELECT IP FROM BlockedIP WHERE IP = ? AND `UID` = ?
                                        ) LIMIT 1;");
        $query->bind_param("ssss", $ip, $uid, $ip, $uid);
        $query->execute();
        $query->close();

        // Update amount of Tries by adding 1
        $query = $this->conn->prepare("UPDATE BlockedIP SET Tries = Tries + 1 WHERE `UID` = ? AND IP = ?");
        $query->bind_param("ss", $uid, $ip);
        $query->execute();
        $query->close();
        $this->blockIPIfLimitExceeded($uid, $ip);
    }

    private function blockIPIfLimitExceeded($uid, $ip)
    {
        $limit = 5;
        $query = $this->conn->prepare("SELECT TRIES FROM BlockedIP WHERE `UID` = ? AND IP = ?");
        $query->bind_param("ss", $uid, $ip);
        $query->execute();
        $query->bind_result($this->result);
        $query->fetch();
        $query->close();

        // Get expiration date of block
        $minutes = floor(($this->result / $limit < 30 ? $this->result / $limit : 30));
        $expiration = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes", strtotime(date("Y-m-d H:i:s"))));

        $query = $this->conn->prepare("UPDATE BlockedIP SET IsBlocked = TRUE, Expiration = ? WHERE IP = ? AND `UID` = ? AND (TRIES % ?) = 0");
        $query->bind_param("sssi", $expiration, $ip, $uid, $limit);
        $query->execute();
        $query->close();
        return;
    }

    public function blockedIPArray($uid)
    {
        $query = $this->conn->prepare("SELECT IP FROM BlockedIP WHERE `UID` = ? AND IsBlocked = TRUE");
        $query->bind_param("s", $uid);
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row['IP'];
        }
        $query->close();
        return $results;
    }

    public function blockExpired($uid, $ip)
    {
        // Check if current date has surpassed expiration date
        $query = $this->conn->prepare("SELECT Expiration FROM BlockedIP WHERE IP = ? AND `UID` = ?");
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
        $query = $this->conn->prepare("UPDATE BlockedIP SET IsBlocked = FALSE, Expiration = NULL WHERE IP = ? AND `UID` = ?");
        $query->bind_param("ss", $ip, $uid);
        $query->execute();
        $query->close();
        return true;
    }

    public function updateLastPasswordChange($uid, $table)
    {
        // Make sure $table can not be edited by user
        $table = $this->conn->real_escape_string($table);
        $newdate = date("d/m/Y");
        $query = $this->conn->prepare("UPDATE {$table} SET Last_Password_Change = ? WHERE `UID` = ?");
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

    public function convertGroupToTable($group)
    {
        switch ($group) {
            case 'patienten':
                return 'Patiënt';
            case 'dokters':
                return 'Gecontracteerd';
            case 'dietisten':
                return 'Gecontracteerd';
            case 'dokters':
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
}
 