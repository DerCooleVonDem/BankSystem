<?php


namespace Vazzi\BankSystem\Provider;


use pocketmine\Player;
use pocketmine\utils\Config;
use Vazzi\BankSystem\Main;

class YAMLProvider
{

    public static function createPermissionConfig(int $id){
        if(!file_exists(Main::getInstance()->getDataFolder())){
            $data = new Config(Main::getInstance()->getDataFolder(). "/permissions/{$id}.yml", 2);
            Main::getInstance()->saveResource("/permissions/{$id}.yml");
            Main::getInstance()->getLogger()->debug("Permissions Created [{$id}]");
            $data->save();
        }




    }

    public static function addPlayer(int $id, Player $player, bool $deposit, bool $withdraw, bool $transfer, bool $admin){
        $name = strtolower($player->getName());
        $data = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        if(!$data->exists($name)){
            $data->set($name."-deposit", $deposit);
            $data->set($name."-withdraw", $withdraw);
            $data->set($name."-transfer", $transfer);
            $data->set($name."-admin", $admin);
            $data->set($name, true);
            $data->save();
        }
    }

    public static function deletePlayer(int $id, Player $player){
        $name = strtolower($player->getName());
        $data = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        if($data->exists($name)){
         $data->remove($name);
         $data->remove($name."-deposit");
         $data->remove($name."-withdraw");
         $data->remove($name."-transfer");
         $data->remove($name."-admin");
         $data->save();
        }

    }

    public static function editPerms(int $id, Player $player, bool $deposit, bool $withdraw, bool $transfer, bool $admin){
        $name = strtolower($player->getName());
        $data = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        $data->set($name."-deposit", $deposit);
        $data->set($name."-withdraw", $withdraw);
        $data->set($name."-transfer", $transfer);
        $data->set($name."-admin", $admin);
        $data->set($name, true);
        $data->save();

    }

    public static function createUserData(Player $player){
        $name = $player->getName();
        $data = new Config(Main::getInstance()->getDataFolder()."/user/{$name}.yml", 2);
        Main::getInstance()->saveResource("/user/{$name}.yml");


    }
    //id means basically the bank where you were invited to
    public static function addInvite(Player $player, int $id, string $by, bool $deposit, bool $withdraw, bool $transfer, bool $admin){
        $name = $player->getName();
        $data = new Config(Main::getInstance()->getDataFolder()."/user/{$name}.yml", 2);
        $data->set($id, array($id, $by, $deposit, $withdraw, $transfer, $admin));
        $data->save();
    }

    public static function deleteInvite(Player $player, int $id){
        $name = $player->getName();
        $data = new Config(Main::getInstance()->getDataFolder()."/user/{$name}.yml", 2);

    }

    public static function existsPlayerData(string $name){
        if(file_exists(Main::getInstance()->getDataFolder()."/user/{$name}.yml")){
            return true;
        }else{
            return false;
        }

    }





















































}