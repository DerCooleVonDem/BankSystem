<?php

namespace Vazzi\BankSystem;

use pocketmine\Player;
use pocketmine\Server;
use Vazzi\BankSystem\Main;
use Vazzi\BankSystem\Provider\EconomyProvider;
use Vazzi\BankSystem\Provider\MySQLProvider;
use Vazzi\BankSystem\Provider\YAMLProvider;

class AccountAPI
{

    public static function pushMoney(int $id, Player $player, $money){
        $getmoney = MySQLProvider::getMoneyfromID($id);
        $whole = $getmoney + $money;
        MySQLProvider::setMoneybyID($id, $whole);
        EconomyProvider::removeMoneyfromPlayer($player->getName(), $money);
        $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich §e{$money} Coins §aauf dein Bankacccount eingezahlt!");
        Banklog::logEntry("Einzahlung: {$player->getName()}, $money", $id);
    }

    public static function pullMoney(int $id, Player $player, $money){
        $getmoney = MySQLProvider::getMoneyfromID($id);
        $whole = $getmoney - $money;
        MySQLProvider::setMoneybyID($id, $whole);
        EconomyProvider::addMoneytoPlayer($player->getName(), $money);
        $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich §e{$money} Coins §avon deinem Bankacccount ausgezahlt!");
        Banklog::logEntry("Auszahlung: {$player->getName()}, $money", $id);

    }


    public static function transferMoney(int $senderid, int $recid, Player $player, String $recname, $money){
        $getmoney = MySQLProvider::getMoneyfromID($senderid);
        $recmoney = MySQLProvider::getMoneyfromID($recid);
        $senderwhole = $getmoney - $money;
        MySQLProvider::setMoneybyID($senderid, $senderwhole);
        $recwhole = $recmoney + $money;
        MySQLProvider::setMoneybyID($recid, $recwhole);
        $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich §e{$money} Coins §aan §b{$recname} §aüberwiesen!");
        if(Main::getInstance()->getServer()->getPlayerExact($recname) instanceof Player){
            $recer = Main::getInstance()->getServer()->getPlayerExact($recname);
            $recer->sendMessage(Main::$prefix . "§aDu hast soeben §e{$money} Coins §avon §b{$player->getName()} §aüberwiesen bekommen!");
        }
        Banklog::logEntry("Ausgehende Transferierung: {$player->getName()} -> $recname:  $money", $senderid);
        Banklog::logEntry("Eingehende Transferierung: {$player->getName()} -> $recname: $money", $recid);
    }

    public static function readMoney(Player $player){
        $id = MySQLProvider::getIDfromAccount($player->getName());
        return MySQLProvider::getMoneyfromID($id);

    }

}