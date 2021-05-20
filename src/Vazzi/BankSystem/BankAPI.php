<?php

/*

Prozessor
- Formatierungen usw

*/


namespace Vazzi\BankSystem;


use pocketmine\block\Block;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\sound\ClickSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use Vazzi\BankSystem\AccountAPI;
use Vazzi\BankSystem\BankForm;
use Vazzi\BankSystem\Provider\EconomyProvider;
use Vazzi\BankSystem\Provider\MySQLProvider;
use Vazzi\BankSystem\Provider\YAMLProvider;

class BankAPI
{

    public static function mainForm(Player $player){
        BankForm::showForm($player);
    }


    public static function formation(Player $player, $state = false){
        $cfg = Main::getInstance()->getConfig();
        $input = $cfg->get("playerform-content");
        return str_replace("{bank-inhalt}", AccountAPI::readMoney($player, $state), $input);

    }

    public static function getRealNamebyString(String $playername){
        if(Main::getInstance()->getServer()->getPlayer($playername) instanceof Player){
            $player = Main::getInstance()->getServer()->getPlayer($playername)->getName();
            return $player;
        }else{
            return false;
        }
    }

    public static function createId(){
        $zahlen = mt_rand(111111, 999999);
        if(!MySQLProvider::existsID($zahlen)){
            return $zahlen;
        }else {
            self::createId();
        }
        return true;
    }

    public static function playersendlog(Player $player, $state = false){
        $player->sendMessage(Main::$prefix . "§dDir wird nun dein Banklog angezeigt!");
        $id = MySQLProvider::getIDfromAccount($player->getName(), $state);
        for($i=14; $i >= 0; $i--) {
            $ausgabe = Banklog::getLog($id, $i);
            if ($ausgabe !== "Keine Abgabe") {
                $player->sendMessage(Banklog::getLog($id, $i));
            }
        }
    }

    public static function adminsendlog(Player $player, $id){
        for($i=14; $i >= 0; $i--) {
            $ausgabe = Banklog::getLog($id, $i);
            if ($ausgabe !== "Keine Abgabe") {
                $player->sendMessage(Banklog::getLog($id, $i));
            }
        }
    }


    public static function playersendparticle(Player $player, $mode = "CREATE"){
        if($player instanceof Player){
            $x = $player->getX();
            $y = $player->y;
            $z = $player->getZ();
            switch($mode) {
                case "CREATE":
                    $player->getLevel()->addParticle(new DestroyBlockParticle(new Vector3($x, $y+2, $z), Block::get(Block::LIME_GLAZED_TERRACOTTA)));
                    $player->getLevel()->addParticle(new DestroyBlockParticle(new Vector3($x, $y+2, $z), Block::get(Block::LIGHT_BLUE_GLAZED_TERRACOTTA)));
                    break;
                case "DELETE":
                    $player->getLevel()->addParticle(new DestroyBlockParticle(new Vector3($x, $y+2, $z), Block::get(Block::RED_GLAZED_TERRACOTTA)));
                    $player->getLevel()->addParticle(new DestroyBlockParticle(new Vector3($x, $y+2, $z), Block::get(Block::REDSTONE_BLOCK)));
                    break;
            }

        }
    }


}