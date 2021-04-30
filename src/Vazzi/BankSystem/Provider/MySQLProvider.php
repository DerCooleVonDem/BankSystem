<?php

namespace Vazzi\BankSystem\Provider;

use pocketmine\plugin\PluginException;
use Vazzi\BankSystem\Main;
use MySQLi;

class MySQLProvider
{
    private $plugin;

    /** @var \ MySQLi */
    private static $database;

    /**
     * MySQLProvider constructor.
     *
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $config = $plugin->getConfig()->get("provider-settings", []);
        MySQLProvider::$database = new \mysqli($config["host"], $config["user"], $config["password"], $config["db"], $config["port"]);
        $query = "CREATE TABLE IF NOT EXISTS BankAccounts(AccountID INT, moneycount INT, owner VARCHAR(20), perms BOOLEAN);";
        MySQLProvider::$database->query($query);
    }

    /**
     * @return \MySQLi
     */
    public function getDatabase(): \MySQLi
    {
        return MySQLProvider::$database;
    }

    public static function existsID($id){
        $result = self::$database->query("SELECT * FROM BankAccounts WHERE AccountID = '$id'");
        return $result->num_rows > 0 ? true:false;
    }


    public static function existsAccount($playername){
        $result = self::$database->query("SELECT * FROM BankAccounts WHERE owner = '$playername'");
        return $result->num_rows > 0 ? true:false;
    }

    public static function isGlobalAccount($id)
    {
        $res = MySQLProvider::$database->query("SELECT perms FROM BankAccounts WHERE AccountID = '$id'");
        $ret = $res->fetch_array()[0] ?? false;
        $res->free();
        return $ret;
    }

    public static function getBankdata($data, $id)
    {
        $res = MySQLProvider::$database->query("SELECT '$data' FROM BankAccounts WHERE AccountID = '$id'");
        $ret = $res->fetch_array()[0] ?? false;
        $res->free();
        return $ret;
    }

    public static function getIDfromAccount($playername)
    {
        $res = MySQLProvider::$database->query("SELECT AccountID FROM BankAccounts WHERE owner = '$playername'");
        $ret = $res->fetch_array()[0] ?? false;
        $res->free();
        return $ret;
    }

    public static function getMoneyfromID($id)
    {
        $res = MySQLProvider::$database->query("SELECT moneycount FROM BankAccounts WHERE AccountID = '$id'");
        $ret = $res->fetch_array()[0] ?? false;
        $res->free();
        return $ret;
    }

    public static function getOwnerfromID($id)
    {
        $res = MySQLProvider::$database->query("SELECT owner FROM BankAccounts WHERE AccountID = '$id'");
        $ret = $res->fetch_array()[0] ?? false;
        $res->free();
        return $ret;
    }

    public static function setMoneybyID($id, $money)
    {
        MySQLProvider::$database->query("UPDATE BankAccounts SET moneycount = '$money' WHERE AccountID = '$id';");
    }

    public static function setBankdata($data, $id, $ndata)
    {
        MySQLProvider::$database->query("UPDATE BankAccounts SET '$data' = '$ndata' WHERE AccountID = '$id';");
    }

    public static function makeAccount($id, $playername)
    {
        MySQLProvider::$database->query("INSERT INTO BankAccounts (AccountID, moneycount, owner, perms) VALUES ('$id', 0, '$playername', false);");
        return;
    }

    public static function makeGlobalAccount($id, $playername)
    {
        MySQLProvider::$database->query("INSERT INTO BankAccounts (AccountID, moneycount, owner, perms) VALUES ('$id', 0, '$playername', true);");
        return;
    }

    public static function deleteAccount($id)
    {
        MySQLProvider::$database->query("DELETE FROM BankAccounts WHERE AccountID = '$id';");
        return;
    }

}