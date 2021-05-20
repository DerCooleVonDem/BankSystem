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

    public static function addPlayer(int $id, Player $player, bool $deposit, bool $withdraw, bool $transfer){
        $name = $player->getName();
        $data = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        if(!$data->exists($name)){
            $data->set($name."-deposit", $deposit);
            $data->set($name."-withdraw", $withdraw);
            $data->set($name."-transfer", $transfer);
            $data->set($name, true);
            $data->save();
            $playerdata = new Config(Main::getInstance()->getDataFolder()."/user/{$name}.yml", 2);
            $playerdata->set($id, true);
            $playerdata->save();

        }
    }

    public static function deletePlayer(int $id, String $player){
        $name = $player;
        $data = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        if($data->exists($name)) {
            $data->remove($name);
            $data->remove($name . "-deposit");
            $data->remove($name . "-withdraw");
            $data->remove($name . "-transfer");
            $data->save();
        }

    }

    public static function editPerms(int $id, Player $player, bool $deposit, bool $withdraw, bool $transfer){
        $name = $player->getName();
        $data = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        $data->set($name."-deposit", $deposit);
        $data->set($name."-withdraw", $withdraw);
        $data->set($name."-transfer", $transfer);
        $data->set($name, true);
        $data->save();

    }

    public static function createUserData(Player $player){
        $name = $player->getName();
        $data = new Config(Main::getInstance()->getDataFolder()."/user/{$name}.yml", 2);
        $invites = new Config(Main::getInstance()->getDataFolder()."/invites/{$name}.yml", 2);
        Main::getInstance()->saveResource("/user/{$name}");
        Main::getInstance()->saveResource("/invites/{$name}");


    }
    //id means basically the bank where you were invited to
    public static function addInvite($player, int $id, string $by, bool $deposit, bool $withdraw, bool $transfer){
        $name = $player;
        $data = new Config(Main::getInstance()->getDataFolder()."/invites/{$name}.yml", 2);
        $oinv = $data->get("Invites");
        $oinv[] = $id;
        $data->set($id, [$by, $deposit, $withdraw, $transfer]);
        $data->set("Invites", $oinv);
        $data->save();
    }

    public static function deleteInvite(Player $player, int $id){
        $name = $player->getName();
        $data = new Config(Main::getInstance()->getDataFolder()."/invites/{$name}.yml", 2);
        $invites = $data->get("Invites", []);
        $data->set("Invites", array_splice($invites, $id));
        $data->remove($id);
        $data->save();

    }

    public static function existsPlayerData(string $name){
        if(file_exists(Main::getInstance()->getDataFolder()."/user/{$name}.yml")){
            return true;
        }else{
            return false;
        }

    }

    public static function getPlayerInvites(string $name){
        $data = new Config(Main::getInstance()->getDataFolder()."/invites/{$name}.yml", 2);
        return $data->getAll();

    }

    public static function getInvitesonly(string $name){
        $data = new Config(Main::getInstance()->getDataFolder()."/invites/{$name}.yml", 2);
        return $data;

    }

    public static function deleteZugang(int $id, string $playername){
        $perms = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        $perms->remove($playername."-deposit");
        $perms->remove($playername."-withdraw");
        $perms->remove($playername."-transfer");
        $perms->remove($playername);
        $perms->save();
        $playerdata =  new Config(Main::getInstance()->getDataFolder()."/user/{$playername}.yml", 2);
        $playerdata->remove($id);
        $playerdata->save();
    }
    public static function getInviteId(array $cfg){
        return $cfg[0];
    }
    public static function getInviteDeposit(array $cfg){
        return $cfg[2];
    }
    public static function getInviteWithdraw(array $cfg){
        return $cfg[3];
    }
    public static function getInviteTransfer(array $cfg){
        return $cfg[4];
    }





















































}