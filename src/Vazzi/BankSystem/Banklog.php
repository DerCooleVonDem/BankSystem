<?php


namespace Vazzi\BankSystem;

use pocketmine\plugin\PluginException;
use pocketmine\utils\Config;
use Vazzi\BankSystem\Main;
use Vazzi\BankSystem\Provider\EconomyProvider;
use Vazzi\BankSystem\Provider\MySQLProvider;
use Vazzi\BankSystem\AccountAPI;

//DieCooleLogAPI v2
/*
 * Ablage:
 * /players/
 *
 */
class Banklog
{

    public static function createLog(int $id){
        if(!file_exists(Main::getInstance()->getDataFolder() . "/players/{$id}.yml")) {
            $cfg = new Config(Main::getInstance()->getDataFolder() . "/players/{$id}.yml", Config::YAML);
            Main::getInstance()->getLogger()->debug("Bank Log für {$id} erstellt!");
            Main::getInstance()->saveResource("/players/{$id}.yml");
            Main::getInstance()->getLogger()->debug("Bank Log für {$id} gesichert!");

        }
    }

    public static function logEntry(string $str, int $id){
        if(file_exists(Main::getInstance()->getDataFolder()."/players/{$id}.yml")){
            $log = new Config(Main::getInstance()->getDataFolder(). "/players/{$id}.yml", 2);
            $data = date("Y-m-d-H:i:s");
            $log->set(date("Y-m-d-H:i:s"), "$data $str");
            $log->save();
            Main::getInstance()->getLogger()->debug("Bank Log Entry gesetzt {$id} am ".date("y-m-d-H-s").".");

        }else{
            self::createLog($id);
            self::logEntry($str, $id);
        }

    }

    //        for($i=14; $i >= 0; $i--){

    public static function getLog(Int $id, $zahl)
    {
        $log = new Config(Main::getInstance()->getDataFolder() . "/players/{$id}.yml", 2);
        $a = array_reverse($log->getAll());
        $namar = array_values($a);
        if (isset($namar[$zahl])) {

            return $namar[$zahl];

        }else{

            return "Keine Abgabe";

        }

    }

    public static function deleteLog($id){
        if(file_exists(Main::getInstance()->getDataFolder() . "/players/{$id}.yml")){
            $path = Main::getInstance()->getDataFolder() . "/players/{$id}.yml";
            unlink($path);
        }
    }

    public static function date($elog){

        $array = explode(" ", $elog);
        return $array[0];

    }

    public static function entry($elog){
        $array = explode(" ", $elog);
        return str_replace($array[0], "", $elog);

    }





}