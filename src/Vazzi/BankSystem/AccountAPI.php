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

    public static function pushMoney(int $id, Player $player, $money, $global = false){
        $memmsg = "auf dein privates Konto";
        if($global === true){
            $memmsg = "auf dein gemeinsames Konto";
        }
        $getmoney = MySQLProvider::getMoneyfromID($id);
        $whole = $getmoney + $money;
        MySQLProvider::setMoneybyID($id, $whole);
        EconomyProvider::removeMoneyfromPlayer($player->getName(), $money);
        $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich §e{$money} Coins §a{$memmsg} eingezahlt!");
        Banklog::logEntry("Einzahlung: {$player->getName()}, $money", $id);
    }

    public static function pullMoney(int $id, Player $player, $money, $global = false){
        $memmsg = "von deinem privaten Konto";
        if($global === true){
            $memmsg = "von deinem gemeinsamen Konto";
        }
        $getmoney = MySQLProvider::getMoneyfromID($id);
        $whole = $getmoney - $money;
        MySQLProvider::setMoneybyID($id, $whole);
        EconomyProvider::addMoneytoPlayer($player->getName(), $money);
        $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich §e{$money} Coins §a{$memmsg} ausgezahlt!");
        Banklog::logEntry("Auszahlung: {$player->getName()}, $money", $id);

    }


    public static function transferMoney(int $senderid, int $recid, Player $player, String $recname, $money, $global){
        $youmsg = "auf sein privates Konto";
        $memmsg = "auf dein privates Konto";
        if($global === true){
            $youmsg = "auf sein gemeinsames Konto";
            $memmsg = "auf dein gemeinsames Konto";
        }
        $getmoney = MySQLProvider::getMoneyfromID($senderid);
        $recmoney = MySQLProvider::getMoneyfromID($recid);
        $senderwhole = $getmoney - $money;
        MySQLProvider::setMoneybyID($senderid, $senderwhole);
        $recwhole = $recmoney + $money;
        MySQLProvider::setMoneybyID($recid, $recwhole);
        $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich §e{$money} Coins §aan §b{$recname} §aüberwiesen {$youmsg}!");
        if(Main::getInstance()->getServer()->getPlayerExact($recname) instanceof Player){
            $recer = Main::getInstance()->getServer()->getPlayerExact($recname);
            $recer->sendMessage(Main::$prefix . "§aDu hast soeben §e{$money} Coins §avon §b{$player->getName()} §aüberwiesen bekommen {$memmsg}!");
        }
        Banklog::logEntry("Ausgehende Transferierung: {$player->getName()} -> $recname:  $money", $senderid);
        Banklog::logEntry("Eingehende Transferierung: {$player->getName()} -> $recname: $money", $recid);
    }

    public static function readMoney(Player $player, $state = false){
        $id = MySQLProvider::getIDfromAccount($player->getName(), $state);
        return MySQLProvider::getMoneyfromID($id);

    }

}