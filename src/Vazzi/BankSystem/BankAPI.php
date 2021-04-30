<?php


namespace Vazzi\BankSystem;


use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use Vazzi\BankSystem\AccountAPI;
use Vazzi\BankSystem\Provider\EconomyProvider;
use Vazzi\BankSystem\Provider\MySQLProvider;
use Vazzi\BankSystem\Provider\YAMLProvider;

class BankAPI
{

    public static function mainForm(Player $player){
        if(MySQLProvider::existsAccount($player->getName())) {
            self::showFormExists($player);
        }else{
            self::showForm($player);
        }
    }

    public static function showForm(Player $player)
    {
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            $cfg = Main::getInstance()->getConfig();
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if(MySQLProvider::existsAccount($player->getName())) {
                        self::errorform($player, "§cDu hast bereits einen Account");

                    }else{

                        self::confirmForm($player);

                    }

                    break;
                case 1:
                    if(MySQLProvider::existsAccount($player->getName())) {
                        self::errorform($player, "§cDu hast bereits einen Account");

                    }else{

                        self::confirmGlobalForm($player);

                    }

                    break;
                case 2:
                    if($player->hasPermission($cfg->get("admin-perm"))){
                        self::adminForm($player);
                    }

                    break;

            }


            return true;
        });
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("newplayerform-title"));
        $form->setContent($config->get("newplayerform-content"));
        $form->addButton("§aEröffne ein Privates Konto", 0, "textures/items/gold_ingot.png");
        $form->addButton("§aEröffne ein Gemeinsames Konto", 0, "textures/gui/newgui/Friends");
        if($player->hasPermission($config->get("admin-perm"))){
            $form->addButton("§l§4Admin Verwaltung");
        }
        $form->addButton("§7✘EXIT✘");
        $form->sendToPlayer($player);

    }

    public static function showFormExists(Player $player)
    {
        $id = MySQLProvider::getIDfromAccount($player->getName());
        if(MySQLProvider::isGlobalAccount($id)){
            self::GlobalForm($player);
        }else{
            self::PrivateForm($player);
        }
    }

    public static function PrivateForm(Player $player)
    {
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            $cfg = Main::getInstance()->getConfig();
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    BankAPI::pushMoneyForm($player);
                    break;
                case 1:
                    BankAPI::pullMoneyForm($player);
                    break;
                case 2:
                    BankAPI::transferMoneyForm($player);
                    break;
                case 3:
                    BankAPI::playersendlog($player);
                    break;
                case 4:
                    BankAPI::confirmDeleteForm($player);
                    break;
                case 5:
                    if($player->hasPermission($cfg->get("admin-perm"))){
                        self::adminForm($player);
                    }

                    break;
            }


            return true;
        });
        $desc = self::formation($player);
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("playerform-title"));
        $form->setContent($desc);
        $form->addButton("§7» §fGeld einzahlen");
        $form->addButton("§7» §fGeld auszahlen");
        $form->addButton("§7» §fGeld überweisen");
        $form->addButton("§7» §fLetzte Aktivitäten");
        $form->addButton("§7» §cKonto kündigen");
        if($player->hasPermission($config->get("admin-perm"))){
            $form->addButton("§l§4Admin Verwaltung");
        }
        $form->addButton("§7EXIT");
        $form->sendToPlayer($player);
    }

    public static function GlobalForm(Player $player)
    {
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            $cfg = Main::getInstance()->getConfig();
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    BankAPI::pushMoneyForm($player);
                    break;
                case 1:
                    BankAPI::pullMoneyForm($player);
                    break;
                case 2:
                    BankAPI::transferMoneyForm($player);
                    break;
                case 3:
                    BankAPI::playersendlog($player);
                    break;
                case 4:
                    //MiltgliederEditor
                    self::memberManager($player);
                    break;
                case 5:
                    BankAPI::confirmDeleteForm($player);
                    break;
                case 6:
                    if($player->hasPermission($cfg->get("admin-perm"))){
                        self::adminForm($player);
                    }

                    break;
            }


            return true;
        });
        $desc = self::formation($player);
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("playerform-title"));
        $form->setContent($desc);
        $form->addButton("§7» §fGeld einzahlen");
        $form->addButton("§7» §fGeld auszahlen");
        $form->addButton("§7» §fGeld überweisen");
        $form->addButton("§7» §fLetzte Aktivitäten");
        $form->addButton("§7» §cMitglieder Verwalten");
        $form->addButton("§7» §cKonto kündigen");
        if($player->hasPermission($config->get("admin-perm"))){
            $form->addButton("§l§4Admin Verwaltung");
        }
        $form->addButton("§7EXIT");
        $form->sendToPlayer($player);
    }

    public static function memberManager(Player $player){
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch($result){
                case 0:
                    //Zugriff hinzufügen
                    break;
                case 1:
                    //zugriff entfernen
                    break;
                case 2:
                    //Member verwalten
                    break;

            }
            return true;
        });
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("manager-title"));
        $form->setContent($config->get("manager-content"));
        $form->addButton("§7» §fZugriff hinzufügen");
        $form->addButton("§7» §fZugriff entfernen");
        $form->addButton("§7» §fMitgliedder verwalten");
        $form->sendToPlayer($player);
    }

    public static function manageMember(Player $player){
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            $cfg = Main::getInstance()->getConfig();
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    BankAPI::pushMoneyForm($player);
                    break;
                case 1:
                    BankAPI::pullMoneyForm($player);
                    break;
                case 2:
                    BankAPI::transferMoneyForm($player);
                    break;
                case 3:
                    break;
            }


            return true;
        });
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("managemember-title"));
        $form->setContent($config->get("managemember-content"));
        $form->addButton("§7» §fGeld einzahlen");
        $form->addButton("§7» §fGeld auszahlen");
        $form->addButton("§7» §fGeld überweisen");
        $form->addButton("§7EXIT");
        $form->sendToPlayer($player);
    }

    public static function formation(Player $player){
        $cfg = Main::getInstance()->getConfig();
        $input = $cfg->get("playerform-content");
        return str_replace("{bank-inhalt}", AccountAPI::readMoney($player), $input);

    }

    public static function errorform(Player $player, $error){
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if($result === null){
                return true;
            }
            switch ($result) {
                case 0:
                    break;
            }

            return true;
        });
        $form->setTitle("§cFehler! Versuche erneut.");
        $form->setContent($error);
        $form->addButton("§7EXIT");
        $form->sendToPlayer($player);

    }

    public static function confirmDeleteForm(Player $player){
        $cfg = Main::getInstance()->getConfig();
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $player, bool $data = null) {
            $result = $data;
            $cfg = Main::getInstance()->getConfig();

            if($result === null){
                return true;
            }else {
                if($result === true){

                    $id = MySQLProvider::getIDfromAccount($player->getName());
                    if(MySQLProvider::getOwnerfromID($id) == $player->getName()){

                        $money = MySQLProvider::getMoneyfromID($id);
                        EconomyProvider::addMoneytoPlayer($player->getName(), $money);
                        MySQLProvider::deleteAccount($id);
                        $delmsg = str_replace("{bank-money}", $money, $cfg->get("delete-message"));
                        $player->sendMessage(Main::$prefix . $delmsg);

                    }

                }else{

                    self::showForm($player);


                }
            }
            return true;
        });
        $id = MySQLProvider::getIDfromAccount($player->getName());
        $money = MySQLProvider::getMoneyfromID($id);
        $form->setTitle($cfg->get("delete-confirm-title"));
        $form->setContent($cfg->get("delete-confirm-content") . $money);
        $form->setButton1("§7» §aKündigen");
        $form->setButton2("§7» §cAbbruch");
        $form->sendToPlayer($player);
    }

    public static function confirmForm(Player $player){
        $cfg = Main::getInstance()->getConfig();
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $player, bool $data = null) {
            $result = $data;
            $cfg = Main::getInstance()->getConfig();

            if($result === null){
                return true;
            }else {
                if($result === true){

                    if(Main::$economy->mymoney($player) >= $cfg->get("bank-cost")) {
                        $id = self::createId();
                        MySQLProvider::makeAccount($id, $player->getName());
                        Banklog::createLog($id);
                        $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich ein Bank Konto erstellt.");
                        Main::$economy->reduceMoney($player, $cfg->get("bank-cost"));
                        self::mainForm($player);
                    }else{
                        self::errorform($player, "§cDu hast nicht genügend Geld.");
                    }
                }else{

                    self::showForm($player);


                }
            }
            return true;
        });
        $form->setTitle($cfg->get("confirm-title"));
        $form->setContent($cfg->get("confirm-content") . $cfg->get("bank-cost"));
        $form->setButton1("§7» §aBestätigen");
        $form->setButton2("§7» §cAbbruch");
        $form->sendToPlayer($player);
    }

    public static function confirmGlobalForm(Player $player){
        $cfg = Main::getInstance()->getConfig();
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $player, bool $data = null) {
            $result = $data;
            $cfg = Main::getInstance()->getConfig();

            if($result === null){
                return true;
            }else {
                if($result === true){

                    if(Main::$economy->mymoney($player) >= $cfg->get("global-bank-cost")) {
                        $id = self::createId();
                        MySQLProvider::makeGlobalAccount($id, $player->getName());
                        Banklog::createLog($id);
                        $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich ein Bank Konto erstellt.");
                        Main::$economy->reduceMoney($player, $cfg->get("global-bank-cost"));
                        self::mainForm($player);
                    }else{
                        self::errorform($player, "§cDu hast nicht genügend Geld.");
                    }
                }else{

                    self::showForm($player);


                }
            }
            return true;
        });
        $form->setTitle($cfg->get("global-confirm-title"));
        $form->setContent($cfg->get("global-confirm-content") . $cfg->get("global-bank-cost"));
        $form->setButton1("§7» §aBestätigen");
        $form->setButton2("§7» §cAbbruch");
        $form->sendToPlayer($player);
    }

    public static function pushMoneyForm(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            if($data !== null){
                if (is_numeric($data[0])) {
                    if (EconomyProvider::getPlayerMoney($player->getName()) >= $data[0]) {
                        $id = MySQLProvider::getIDfromAccount($player->getName());
                        $money = (int)$data[0];
                        AccountAPI::pushMoney($id, $player, $money);
                    } else {
                        self::errorform($player, "§cDu hast nicht genug Geld um dies Einzuzahlen");
                    }

                } else {
                    $player->sendMessage(Main::$prefix . "Du kannst nur Zahlen eingeben!");
                }
            }

        });
        $form->setTitle("§l§aGeld Einzahlen");
        $form->addInput("§fWähle die Anzahl aus die du Einzahlen willst", "1000");
        $form->sendToPlayer($player);

    }

    public static function pullMoneyForm(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            if($data !== null){
                if(is_numeric($data[0])) {
                    if (AccountAPI::readMoney($player) >= $data[0]) {
                        $id = MySQLProvider::getIDfromAccount($player->getName());
                        EconomyProvider::addMoneytoPlayer($player->getName(), $data[0]);
                        AccountAPI::pullMoney($id, $player, $data[0]);
                    } else {
                        self::errorform($player, "§cIn der Bank ist nicht genug Geld um dies auszuzahlen");
                    }
                }else{
                    self::errorform($player, "§cDu hast keine Zahl angegeben!");
                }
            }
        });
        $form->setTitle("§aGeld auszahlen");
        $form->addInput("Wähle die Anzahl aus die du Auszahlen willst", "1000");
        $form->sendToPlayer($player);

    }

    public static function transferMoneyForm(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            if($data !== null){
                if(is_numeric($data[0])) {
                    $playerrealname = $data[1];
                    $pname = self::getRealNamebyString($data[1]);
                    if($pname !== false){
                        $playerrealname = self::getRealNamebyString($data[1]);
                    }
                    if (MySQLProvider::existsAccount($playerrealname)) {
                        if(strtolower($playerrealname) != strtolower($player->getName())) {
                            if (AccountAPI::readMoney($player) >= $data[0]) {
                                $senderid = MySQLProvider::getIDfromAccount($player->getName());
                                $recid = MySQLProvider::getIDfromAccount($playerrealname);
                                AccountAPI::transferMoney($senderid, $recid, $player, $playerrealname, $data[0]);
                            } else {
                                self::errorform($player, "§cDer Spieler wurde nicht gefunden!");
                            }

                        }else{
                            self::errorform($player, "§cDu kannst dir nicht selber Geld überweisen!");
                        }

                    }else{
                        self::errorform($player, "§cDer angegebene Spieler besitzt kein Konto!");
                    }
                }else{
                    self::errorform($player, "§cDu hast keine Zahl angegeben!");

                }

            }
        });
        $form->setTitle("§aGeld überweisen");
        $form->addInput("Wie viel soll überwiesen werden? (Coins Anzahl)", "1000");
        $form->addInput("Zu wem soll es überwiesen werden? (Spieler Name)", "Giulizo");
        $form->sendToPlayer($player);

    }



    public static function adminForm(Player $player){
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    self::seeMoneyFromPlayer($player);
                    break;
                case 1:
                    self::changeMoneyFromPlayer($player);
                    break;
                case 2:
                    self::getIdByPlayerName($player);
                    break;
                case 3:
                    self::deleteAccount($player);
                    break;
                case 4:
                    self::seePlayerLog($player);
                    break;
                case 5:
                    break;
            }


            return true;
        });
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("adminform-title"));
        $form->setContent($config->get("adminform-content"));
        $form->addButton("§fSpieler Kontostand einsehen");
        $form->addButton("§fSpieler Kontostand ändern");
        $form->addButton("§fSpieler Konto ID Finden");
        $form->addButton("§fSpieler Konto löschen");
        $form->addButton("§fSpieler Banklog einsehen");
        $form->addButton("§7EXIT");
        $form->sendToPlayer($player);

    }

    public static function getIdByPlayerName($player){
        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data){
            if($data !== null){
                if(MySQLProvider::existsAccount($data[0])) {
                    $id = MySQLProvider::getIDfromAccount($data[0]);
                    $player->sendMessage("[" . $id . "] ist die Account ID von {$data[0]}");
                }else{
                    self::errorform($player, "§cDieser Spieler hat kein Bank Konto!");
                }
            }


        });
        $form->setTitle("§0KontoID finden");
        $form->addInput("Spielernamen eintragen", "Giulizo");
        $form->sendToPlayer($player);

    }

    public static function seeMoneyFromPlayer(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            if($data !== null){
                $playerrealname = $data[0];
                $pname = self::getRealNamebyString($data[0]);
                if($pname !== false){
                    $playerrealname = self::getRealNamebyString($data[0]);
                }
                if (MySQLProvider::existsAccount($playerrealname)) {
                    $id = MySQLProvider::getIDfromAccount($playerrealname);
                    $money = MySQLProvider::getMoneyfromID($id);
                    $player->sendMessage(Main::$prefix . "§fDer Spieler §6{$playerrealname} §fhat §b{$money} Coins §fauf seinem Konto.");
                } else {
                    self::errorform($player, "§cDieser Spieler hat kein Bank Konto!");
                }
            }
        });
        $form->setTitle("§aGeld einsehen");
        $form->addInput("Von wem willst du das Geld einsehen? (Spieler Name)", "Giulizo");
        $form->sendToPlayer($player);

    }

    public static function changeMoneyFromPlayer(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            if($data !== null){
                $playerrealname = $data[0];
                $pname = self::getRealNamebyString($data[0]);
                if($pname !== false){
                    $playerrealname = self::getRealNamebyString($data[0]);
                }
                if (MySQLProvider::existsAccount($playerrealname)) {
                    if (is_numeric($data[1])) {
                        $id = MySQLProvider::getIDfromAccount($playerrealname);
                        MySQLProvider::setMoneybyID($id, $data[1]);
                        $player->sendMessage(Main::$prefix . "§fDer Spieler §6{$playerrealname} §fhat nun §b{$data[1]} Coins!");
                    }else{
                        self::errorform($player, "§cDu musst eine gültige Zahl eingeben!");
                    }
                }else {
                    self::errorform($player, "§cDieser Spieler hat kein Bank Konto!");
                }
            }
        });
        $form->setTitle("§aGeldstand ändern");
        $form->addInput("Von wem willst du den Kontostand ändern? (Spieler Name)", "Giulizo");
        $form->addInput("Wieviele Coins soll das Konto haben? (Coins Anzahl)", "1000");
        $form->sendToPlayer($player);

    }

    public static function getRealNamebyString(String $playername){
        if(Main::getInstance()->getServer()->getPlayer($playername) instanceof Player){
            $player = Main::getInstance()->getServer()->getPlayer($playername)->getName();
            return $player;
        }else{
            return false;
        }
    }

    public static function deleteAccount(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            if($data !== null){
                $playerrealname = $data[0];
                $pname = self::getRealNamebyString($data[0]);
                if($pname !== false){
                    $playerrealname = self::getRealNamebyString($data[0]);
                }
                if (MySQLProvider::existsAccount($playerrealname)) {

                    $id = MySQLProvider::getIDfromAccount($playerrealname);
                    Banklog::deleteLog($id);
                    MySQLProvider::deleteAccount($id);
                    $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich das Konto von §c{$playerrealname} §agelöscht!");

                }else {
                    self::errorform($player, "§cDieser Spieler hat kein Bank Konto!");
                }
            }
        });
        $form->setTitle("§l§cKonto löschen");
        $form->addInput("Von wem willst du das Konto löschen? (Spieler Name)", "Giulizo");
        $form->sendToPlayer($player);

    }

    public static function seePlayerLog(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            if($data !== null){
                $playerrealname = $data[0];
                $pname = self::getRealNamebyString($data[0]);
                if($pname !== false){
                    $playerrealname = self::getRealNamebyString($data[0]);
                }
                if (MySQLProvider::existsAccount($playerrealname)) {
                    $id = MySQLProvider::getIDfromAccount($playerrealname);
                    $player->sendMessage(Main::$prefix . "§aDir wird nun der Banklog von §6{$playerrealname} §aangezeigt!");
                    self::adminsendlog($player, $id);
                }else {
                    self::errorform($player, "§cDieser Spieler hat kein Bank Konto!");
                }
            }
        });
        $form->setTitle("§l§cBanklog einsehen");
        $form->addInput("Von wem willst du den Banklog einsehen? (Spieler Name)", "Giulizo");
        $form->sendToPlayer($player);

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

    public static function playersendlog(Player $player){
        $player->sendMessage(Main::$prefix . "§dDir wird nun dein Banklog angezeigt!");
        $id = MySQLProvider::getIDfromAccount($player->getName());
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
}