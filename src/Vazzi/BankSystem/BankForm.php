<?php

/*
 * Forms
 *
 */


namespace Vazzi\BankSystem;


use pocketmine\block\Block;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\ClickSound;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use Vazzi\BankSystem\Main;
use Vazzi\BankSystem\Provider\EconomyProvider;
use Vazzi\BankSystem\Provider\MySQLProvider;
use Vazzi\BankSystem\Provider\YAMLProvider;
use pocketmine\utils\Config;

class BankForm
{

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
                    if (MySQLProvider::existsAccount($player->getName())) {

                        BankForm::PrivateForm($player);

                    } else {

                        self::confirmForm($player);

                    }

                    break;
                case 1:
                    if(MySQLProvider::existsGlobalAccount($player->getName())) {

                        self::GlobalForm($player);

                    }else {

                        self::confirmGlobalForm($player);

                    }

                    break;

                case 2:

                    BankForm::otherKonto($player);

                    break;

                case 3:

                    BankForm::inviteManager($player);

                    break;
                case 4:
                    if ($player->hasPermission($cfg->get("admin-perm"))) {
                        self::adminForm($player);
                    }

                    break;

            }


            return true;
        });
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("newplayerform-title"));
        $form->setContent($config->get("newplayerform-content"));
        if (MySQLProvider::existsAccount($player->getName())) {

            $form->addButton("§2Mein Konto", 0, "textures/items/gold_ingot.png");

        }else{
            $form->addButton("§2Eröffne ein Privates Konto", 0, "textures/items/gold_ingot.png");
        }
        if (MySQLProvider::existsGlobalAccount($player->getName())) {

            $form->addButton("§2Mein Gemeinsames Konto", 0, "textures/gui/newgui/Friends");

        }else{
            $form->addButton("§2Eröffne ein Gemeinsames Konto", 0, "textures/gui/newgui/Friends");
        }
        $form->addButton("§6Andere Konten", 0, "textures/items/ghast_tear.png");
        $form->addButton("§eEinladungen Verwalten", 0, "textures/items/paper.png");
        if($player->hasPermission($config->get("admin-perm"))){
            $form->addButton("§l§4Admin Verwaltung");
        }
        $form->addButton("§7Schließen");
        $form->sendToPlayer($player);

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
                    BankForm::pushMoneyForm($player);
                    break;
                case 1:
                    BankForm::pullMoneyForm($player);
                    break;
                case 2:
                    BankForm::transferMoneyForm($player);
                    break;
                case 3:
                    BankAPI::playersendlog($player);
                    break;
                case 4:
                    BankForm::confirmPrivateDeleteForm($player);
                    break;
                case 5:
                    if($player->hasPermission($cfg->get("admin-perm"))){
                        self::adminForm($player);
                    }

                    break;
            }


            return true;
        });
        $desc = BankAPI::formation($player);
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
        $form->addButton("§7Schließen");
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
                    BankForm::pushGlobalMoneyForm($player);
                    break;
                case 1:
                    BankForm::pullGlobalMoneyForm($player);
                    break;
                case 2:
                    BankForm::transferGlobalMoneyForm($player);
                    break;
                case 3:
                    BankAPI::playersendlog($player, true);
                    break;
                case 4:
                    BankForm::memberManager($player);
                    break;
                case 5:
                    BankForm::confirmGlobalDeleteForm($player);
                    break;
                case 6:
                    if($player->hasPermission($cfg->get("admin-perm"))){
                        self::adminForm($player);
                    }

                    break;
            }


            return true;
        });
        $desc = BankAPI::formation($player, true);
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("playerform-title"));
        $form->setContent($desc);
        $form->addButton("§7» §fGeld einzahlen");
        $form->addButton("§7» §fGeld auszahlen");
        $form->addButton("§7» §fGeld überweisen");
        $form->addButton("§7» §fLetzte Aktivitäten");
        $form->addButton("§7» §aMitglieder Verwalten");
        $form->addButton("§7» §cKonto kündigen");
        if($player->hasPermission($config->get("admin-perm"))){
            $form->addButton("§l§4Admin Verwaltung");
        }
        $form->addButton("§7Schließen");
        $form->sendToPlayer($player);
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
        $form->addButton("§7Schließen");
        $form->sendToPlayer($player);

    }

    public static function confirmPrivateDeleteForm(Player $player){
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
                        Banklog::deleteLog($id);
                        MySQLProvider::deleteAccount($id);
                        $delmsg = str_replace("{bank-money}", $money, $cfg->get("delete-message"));
                        $player->sendMessage(Main::$prefix . $delmsg);
                        BankAPI::playersendparticle($player, "DELETE");

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

    public static function confirmGlobalDeleteForm(Player $player){
        $cfg = Main::getInstance()->getConfig();
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $player, bool $data = null) {
            $result = $data;
            $cfg = Main::getInstance()->getConfig();

            if($result === null){
                return true;
            }else {
                if($result === true){

                    $id = MySQLProvider::getIDfromAccount($player->getName(), true);
                    if(MySQLProvider::getOwnerfromID($id) == $player->getName()){

                        $money = MySQLProvider::getMoneyfromID($id);
                        EconomyProvider::addMoneytoPlayer($player->getName(), $money);
                        Banklog::deleteLog($id);
                        MySQLProvider::deleteAccount($id);
                        $delmsg = str_replace("{bank-money}", $money, $cfg->get("delete-global-message"));
                        $player->sendMessage(Main::$prefix . $delmsg);
                        BankAPI::playersendparticle($player, "DELETE");

                    }

                }else{

                    self::showForm($player);


                }
            }
            return true;
        });
        $id = MySQLProvider::getIDfromAccount($player->getName(), true);
        $money = MySQLProvider::getMoneyfromID($id);
        $form->setTitle($cfg->get("delete-confirm-title"));
        $form->setContent($cfg->get("delete-global-confirm-content") . $money);
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
                        $id = BankAPI::createId();
                        MySQLProvider::makeAccount($id, $player->getName());
                        Banklog::createLog($id);
                        $player->sendMessage(Main::$prefix . $cfg->get("private-create"));
                        Main::$economy->reduceMoney($player, $cfg->get("private-cost"));
                        BankForm::PrivateForm($player);
                        BankAPI::playersendparticle($player, "CREATE");
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
                        $id = BankAPI::createId();
                        MySQLProvider::makeGlobalAccount($id, $player->getName());
                        Banklog::createLog($id);
                        $player->sendMessage(Main::$prefix . $cfg->get("global-create"));
                        Main::$economy->reduceMoney($player, $cfg->get("global-bank-cost"));
                        BankForm::GlobalForm($player);
                        BankAPI::playersendparticle($player, "CREATE");
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
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if (is_numeric($data[0])) {
                    if (EconomyProvider::getPlayerMoney($player->getName()) >= $data[0]) {
                        $id = MySQLProvider::getIDfromAccount($player->getName());
                        $money = (int)$data[0];
                        AccountAPI::pushMoney($id, $player, $money);
                    } else {
                        self::errorform($player, $cfg->get("not-enough-pay"));
                    }

                } else {
                    $player->sendMessage(Main::$prefix . $cfg->get("only-money"));
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
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if(is_numeric($data[0])) {
                    if (AccountAPI::readMoney($player) >= $data[0]) {
                        $id = MySQLProvider::getIDfromAccount($player->getName());
                        EconomyProvider::addMoneytoPlayer($player->getName(), $data[0]);
                        AccountAPI::pullMoney($id, $player, $data[0]);
                    } else {
                        self::errorform($player, $cfg->get("not-enough-get"));
                    }
                }else{
                    self::errorform($player, $cfg->get("no-input"));
                }
            }
        });
        $form->setTitle("§aGeld auszahlen");
        $form->addInput("Wähle die Anzahl aus die du Auszahlen willst", "1000");
        $form->sendToPlayer($player);

    }

    public static function pushGlobalMoneyForm(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if (is_numeric($data[0])) {
                    if (EconomyProvider::getPlayerMoney($player->getName()) >= $data[0]) {
                        $id = MySQLProvider::getIDfromAccount($player->getName(), true);
                        $money = (int)$data[0];
                        AccountAPI::pushMoney($id, $player, $money, true);
                    } else {
                        self::errorform($player, $cfg->get("not-enough-pay"));
                    }

                } else {
                    $player->sendMessage(Main::$prefix . $cfg->get("only-money"));
                }
            }

        });
        $form->setTitle("§l§aGeld Einzahlen");
        $form->addInput("§fWähle die Anzahl aus die du Einzahlen willst", "1000");
        $form->sendToPlayer($player);

    }

    public static function pullGlobalMoneyForm(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if(is_numeric($data[0])) {
                    if (AccountAPI::readMoney($player, true) >= $data[0]) {
                        $id = MySQLProvider::getIDfromAccount($player->getName(), true);
                        EconomyProvider::addMoneytoPlayer($player->getName(), $data[0]);
                        AccountAPI::pullMoney($id, $player, $data[0], true);
                    } else {
                        self::errorform($player, $cfg->get("not-enough-get"));
                    }
                }else{
                    self::errorform($player, $cfg->get("no-input"));
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
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if(is_numeric($data[0])) {
                    $playerrealname = $data[1];
                    $pname = BankAPI::getRealNamebyString($data[1]);
                    if($pname !== false){
                        $playerrealname = BankAPI::getRealNamebyString($data[1]);
                    }
                    if (MySQLProvider::existsAccount($playerrealname) or MySQLProvider::existsGlobalAccount($playerrealname)) {
                        if(strtolower($playerrealname) != strtolower($player->getName())) {
                            if (AccountAPI::readMoney($player) >= $data[0]) {
                                $senderid = MySQLProvider::getIDfromAccount($player->getName());
                                $recid = MySQLProvider::getIDfromAccount($playerrealname);
                                $whobank = false;
                                if($data[2] === true) {
                                    if (MySQLProvider::existsGlobalAccount($playerrealname)) {
                                        $recid = MySQLProvider::getIDfromAccount($playerrealname, true);
                                        $whobank = true;
                                    }
                                }
                                AccountAPI::transferMoney($senderid, $recid, $player, $playerrealname, $data[0], $whobank);

                            } else {
                                self::errorform($player, $cfg->get("not-enough-transfer"));
                            }

                        }else{
                            self::errorform($player, $cfg->get("no-own-transfer"));
                        }

                    }else{
                        self::errorform($player, $cfg->get("reciver-no-account"));
                    }
                }else{
                    self::errorform($player, $cfg->get("no-input"));

                }

            }
        });
        $form->setTitle("§aGeld überweisen");
        $form->addInput("Wie viel soll überwiesen werden? (Coins Anzahl)", "1000");
        $form->addInput("Zu wem soll es überwiesen werden? (Spieler Name)", "Giulizo");
        $form->addToggle("§8[§aLinks§8] §cPrivates Konto\n§8[§bRechts§8] §6Globales Konto", false);
        $form->sendToPlayer($player);

    }

    public static function transferGlobalMoneyForm(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if(is_numeric($data[0])) {
                    $playerrealname = $data[1];
                    $pname = BankAPI::getRealNamebyString($data[1]);
                    if($pname !== false){
                        $playerrealname = BankAPI::getRealNamebyString($data[1]);
                    }
                    if (MySQLProvider::existsAccount($playerrealname) or MySQLProvider::existsGlobalAccount($playerrealname)) {
                        if(strtolower($playerrealname) != strtolower($player->getName())) {
                            if (AccountAPI::readMoney($player) >= $data[0]) {
                                $senderid = MySQLProvider::getIDfromAccount($player->getName());
                                $recid = MySQLProvider::getIDfromAccount($playerrealname);
                                $whobank = false;
                                if($data[2] === true) {
                                    if (MySQLProvider::existsGlobalAccount($playerrealname)) {
                                        $recid = MySQLProvider::getIDfromAccount($playerrealname, true);
                                        $whobank = true;
                                    }
                                }
                                AccountAPI::transferMoney($senderid, $recid, $player, $playerrealname, $data[0], $whobank);

                            } else {
                                self::errorform($player, $cfg->get("not-enough-transfer"));
                            }

                        }else{
                            self::errorform($player, $cfg->get("no-own-transfer"));
                        }

                    }else{
                        self::errorform($player, $cfg->get("reciver-no-account"));
                    }
                }else{
                    self::errorform($player, $cfg->get("no-input"));

                }

            }
        });
        $form->setTitle("§aGeld überweisen");
        $form->addInput("Wie viel soll überwiesen werden? (Coins Anzahl)", "1000");
        $form->addInput("Zu wem soll es überwiesen werden? (Spieler Name)", "Giulizo");
        $form->addToggle("§8[§aLinks§8] §cPrivates Konto\n§8[§bRechts§8] §6Globales Konto", false);
        $form->sendToPlayer($player);

    }

    /*

        ---------------- Admin Manage Forms ----------------

    */

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
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if(MySQLProvider::existsAccount($data[0])) {
                    $whobank = false;
                    if($data[1] === true) {
                        if (MySQLProvider::existsGlobalAccount($data[0])) {
                            $whobank = true;
                        }
                    }
                    $id = MySQLProvider::getIDfromAccount($data[0], $whobank);
                    $player->sendMessage("[" . $id . "] ist die Account ID von {$data[0]}");
                }else{
                    self::errorform($player, $cfg->get("reciver-no-account"));
                }
            }


        });
        $form->setTitle("§0KontoID finden");
        $form->addInput("Spielernamen eintragen", "Giulizo");
        $form->addToggle("§8[§aLinks§8] §cPrivates Konto\n§8[§bRechts§8] §6Globales Konto", false);
        $form->sendToPlayer($player);

    }

    public static function seeMoneyFromPlayer(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                $playerrealname = $data[0];
                $pname = BankAPI::getRealNamebyString($data[0]);
                if($pname !== false){
                    $playerrealname = BankAPI::getRealNamebyString($data[0]);
                }
                if (MySQLProvider::existsAccount($playerrealname)) {
                    $whobank = false;
                    if($data[1] === true) {
                        if (MySQLProvider::existsGlobalAccount($playerrealname)) {
                            $whobank = true;
                        }
                    }
                    $id = MySQLProvider::getIDfromAccount($playerrealname, $whobank);
                    $money = MySQLProvider::getMoneyfromID($id);
                    $player->sendMessage(Main::$prefix . "§fDer Spieler §6{$playerrealname} §fhat §b{$money} Coins §fauf seinem Konto.");
                } else {
                    self::errorform($player,  $cfg->get("reciver-no-account"));
                }
            }
        });
        $form->setTitle("§aGeld einsehen");
        $form->addInput("Von wem willst du das Geld einsehen? (Spieler Name)", "Giulizo");
        $form->addToggle("§8[§aLinks§8] §cPrivates Konto\n§8[§bRechts§8] §6Globales Konto", false);
        $form->sendToPlayer($player);

    }

    public static function changeMoneyFromPlayer(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                $playerrealname = $data[0];
                $pname = BankAPI::getRealNamebyString($data[0]);
                if($pname !== false){
                    $playerrealname = BankAPI::getRealNamebyString($data[0]);
                }
                if (MySQLProvider::existsAccount($playerrealname)) {
                    if (is_numeric($data[1])) {
                        $whobank = false;
                        if($data[2] === true) {
                            if (MySQLProvider::existsGlobalAccount($playerrealname)) {
                                $whobank = true;
                            }
                        }
                        $id = MySQLProvider::getIDfromAccount($playerrealname, $whobank);
                        MySQLProvider::setMoneybyID($id, $data[1]);
                        $player->sendMessage(Main::$prefix . "§fDer Spieler §6{$playerrealname} §fhat nun §b{$data[1]} Coins!");
                    }else{
                        self::errorform($player, "§cDu musst eine gültige Zahl eingeben!");
                    }
                }else {
                    self::errorform($player,  $cfg->get("reciver-no-account"));
                }
            }
        });
        $form->setTitle("§aGeldstand ändern");
        $form->addInput("Von wem willst du den Kontostand ändern? (Spieler Name)", "Giulizo");
        $form->addInput("Wieviele Coins soll das Konto haben? (Coins Anzahl)", "1000");
        $form->addToggle("§8[§aLinks§8] §cPrivates Konto\n§8[§bRechts§8] §6Globales Konto", false);
        $form->sendToPlayer($player);

    }

    public static function deleteAccount(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                $playerrealname = $data[0];
                $pname = BankAPI::getRealNamebyString($data[0]);
                if($pname !== false){
                    $playerrealname = BankAPI::getRealNamebyString($data[0]);
                }
                if (MySQLProvider::existsAccount($playerrealname)) {
                    $whobank = false;
                    if($data[1] === true) {
                        if (MySQLProvider::existsGlobalAccount($playerrealname)) {
                            $whobank = true;
                        }
                    }
                    $id = MySQLProvider::getIDfromAccount($playerrealname, $whobank);
                    Banklog::deleteLog($id);
                    MySQLProvider::deleteAccount($id);
                    $player->sendMessage(Main::$prefix . "§aDu hast erfolgreich das Konto von §c{$playerrealname} §agelöscht!");

                }else {
                    self::errorform($player, $cfg->get("reciver-no-account"));
                }
            }
        });
        $form->setTitle("§l§cKonto löschen");
        $form->addInput("Von wem willst du das Konto löschen? (Spieler Name)", "Giulizo");
        $form->addToggle("§8[§aLinks§8] §cPrivates Konto\n§8[§bRechts§8] §6Globales Konto", false);
        $form->sendToPlayer($player);

    }

    public static function seePlayerLog(Player $player){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                $playerrealname = $data[0];
                $pname = BankAPI::getRealNamebyString($data[0]);
                if($pname !== false){
                    $playerrealname = BankAPI::getRealNamebyString($data[0]);
                }
                if (MySQLProvider::existsAccount($playerrealname)) {
                    $whobank = false;
                    if($data[1] === true) {
                        if (MySQLProvider::existsGlobalAccount($playerrealname)) {
                            $whobank = true;
                        }
                    }
                    $id = MySQLProvider::getIDfromAccount($playerrealname, $whobank);
                    $player->sendMessage(Main::$prefix . "§aDir wird nun der Banklog von §6{$playerrealname} §aangezeigt!");
                    BankAPI::adminsendlog($player, $id);
                }else {
                    self::errorform($player, $cfg->get("reciver-no-account"));
                }
            }
        });
        $form->setTitle("§l§cBanklog einsehen");
        $form->addInput("Von wem willst du den Banklog einsehen? (Spieler Name)", "Giulizo");
        $form->addToggle("§8[§aLinks§8] §cPrivates Konto\n§8[§bRechts§8] §6Globales Konto", false);
        $form->sendToPlayer($player);

    }

    /*

        ---------------- Global Account Forms ----------------

    */


    public static function memberManager(Player $player){
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch($result){
                case 0:
                    self::addGlobalBankZugriff($player);
                    break;
                case 1:
                    self::showGlobalBankZugriff($player);
                    break;
                case 2:
                    self::removeGlobalBankZugriff($player);
                    break;
                case 3:
                    break;

            }
            return true;
        });
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("managemember-title"));
        $form->setContent($config->get("managemember-content"));
        $form->addButton("§7» §fMitglieder hinzufügen");
        $form->addButton("§7» §fMitglieder einsehen");
        $form->addButton("§7» §fMitglieder entfernen");
        $form->sendToPlayer($player);
    }

    public static function addGlobalBankZugriff(Player $player){
        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) {
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if(Main::$instance->getServer()->getPlayer($data[0]) !== false){
                    $id = MySQLProvider::getIDfromAccount($player->getName(), true);
                        if($data[1]){
                            $deposit = true;
                        }else{
                            $deposit = false;
                        }
                        if($data[2]){
                            $withdraw = true;

                        }else{
                            $withdraw = false;

                        }
                        if($data[3]){
                            $trans = true;
                        }else{
                            $trans = false;
                        }
                        YAMLProvider::addInvite(Main::$instance->getServer()->getPlayer($data[0])->getName(), $id, $player->getName(), $deposit, $withdraw, $trans);
                        $player->sendMessage(Main::$prefix . "§7Der Spieler §e{$data[0]} §7wurde erfolgreich zu deiner Bank §aeingeladen§7.");
                        Main::$instance->getServer()->getPlayer($data[0])->sendMessage(Main::$prefix . "§7Du wurdest soeben zu der Bank von §e{$player->getName()} §aeingeladen§7.");
                        Main::$instance->getServer()->getPlayer($data[0])->sendMessage("§bVerwalte §adeine Einladungen ganz einfach mit §f/bank");
                }else{
                    self::errorform($player, $cfg->get("player-not-found"));
                }
            }

        });
        $form->setTitle("§l§aSpieler Einladen");
        $form->addInput("Spielernamen hier eintragen", "Spieler123");
        $form->addToggle("Deposit Rechte?", false);
        $form->addToggle("Withdraw Rechte?", false);
        $form->addToggle("Transfer Rechte?", false);
        $form->sendToPlayer($player);

    }

    public static function showGlobalBankZugriff(Player $player){
        $id = MySQLProvider::getIDfromAccount($player->getName(), true);
        $bankcfg = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        $namelist = [];
        foreach ($bankcfg->getALL(true) as $members) {
            $membername = str_replace(["-deposit", "-withdraw", "-transfer"], "", $members);
            if (!in_array($membername, $namelist)) {
                $namelist[] = $membername;
            }
        }
        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createSimpleForm(function (Player $player, int $data = null) {
            if($data !== null) {


            }
        });
        $form->setTitle("§l§aMitglieder");
        foreach($namelist as $member){
            $form->addButton("§7" . $member);
        }
        $form->addButton("§fSchließen");
        $form->sendToPlayer($player);

    }

    public static function removeGlobalBankZugriff(Player $player){
        $id = MySQLProvider::getIDfromAccount($player->getName(), true);
        $bankcfg = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        $namelist = [];
        foreach ($bankcfg->getALL(true) as $members) {
            $membername = str_replace(["-deposit", "-withdraw", "-transfer"], "", $members);
            if (!in_array($membername, $namelist)) {
                $namelist[] = $membername;
            }
        }
        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) use($namelist){
            $id = MySQLProvider::getIDfromAccount($player->getName(), true);
            if($data !== null) {
                if (in_array($data[0], $namelist)) {
                    YAMLProvider::deleteZugang($id, $data[0]);
                    YAMLProvider::deletePlayer($id, $data[0]);
                    $player->sendMessage(Main::$prefix . "§7Der Spieler §e{$data[0]} §7wurde erfolgreich von deiner Bank §centfernt§7.");
                    Main::$instance->getServer()->getPlayer($data[0])->sendMessage(Main::$prefix . "§7Du wurdest von der Bank von §e{$player->getName()} §centfernt§7.");
                }else{
                    $player->sendMessage(Main::$prefix . "§cDer Spieler §e{$data[0]} §ckann nicht entfernt werden, da er kein Mitglied der Bank ist!");
                }

            }
        });
        $form->setTitle("§l§aGeld Einzahlen");
        $form->addInput("Spielernamen hier eintragen", "Spieler123");
        $form->sendToPlayer($player);

    }

    public static function inviteManager(Player $player){
        $onlyinvites = YAMLProvider::getInvitesonly($player->getName());
        $invites = $onlyinvites->get("Invites", []);
        $invarray = [];

        foreach($invites as $invite){
            if(MySQLProvider::existsID($invite)) {
                $invarray[] = MySQLProvider::getOwnerfromID($invite);
            }else{
                YAMLProvider::deleteInvite($player, $invite);
            }
        }

        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createCustomForm(function (Player $player, $data) use ($invites, $invarray) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            $index = $result[0];
            if(!isset($invarray[$index])) {
                return true;
            }
            $ownername = $invarray[$index];
            $id = MySQLProvider::getIDfromAccount($ownername, true);
            $onlyinvites = YAMLProvider::getInvitesonly($player->getName());
            $rights = $onlyinvites->get($id, []);
            YAMLProvider::addPlayer($id, $player, $rights[1], $rights[2], $rights[3]);
            $playerdata =  new Config(Main::getInstance()->getDataFolder()."/user/{$player->getName()}.yml", 2);
            $playerdata->set($id, true);
            $playerdata->save();
            YAMLProvider::deleteInvite($player, $id);
            $player->sendMessage(Main::$prefix . "§aDu bist nun ein Mitglied der Bank von {$ownername}!");

            return true;
        });

        $form->setTitle("§l§6Einladungen");
        $form->addDropdown("Einladung auswählen", $invarray);
        $form->sendToPlayer($player);


    }

    public static function otherKonto(Player $player)
    {
        $playerdata =  new Config(Main::getInstance()->getDataFolder()."/user/{$player->getName()}.yml", 2);
        $accarray = [];
        foreach($playerdata->getAll(true) as $accs) {
            if(MySQLProvider::existsID($accs)){
                $accarray[] = MySQLProvider::getOwnerfromID($accs);
            }else{
                $playerdata->remove($accs);
                $playerdata->save();
            }
        }

        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createCustomForm(function (Player $player, $data) use ($accarray) {
            $result = $data;
            if ($result === null) {
                return true;
            }

            $index = $result[0];
            if(isset($accarray[$index])) {
                $name = $accarray[$index];
                $id = MySQLProvider::getIDfromAccount($name, true);
                BankForm::showOtherForm($player, $id);

            }


            return true;
        });
        $form->setTitle("§l§bAndere Konten");
        $form->addDropdown("Konto auswählen", $accarray);
        $form->sendToPlayer($player);
    }

    public static function showOtherForm(Player $player, $id)
    {
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) use($id){
            $result = $data;
            $config = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:

                    if($config->get($player->getName() . "-deposit") === true) {
                        BankForm::pushMoneyFormFremd($player, $id);
                    }
                    break;
                case 1:

                    if($config->get($player->getName() . "-withdraw") === true) {
                        BankForm::pullMoneyFormFremd($player, $id);
                    }

                case 2:

                    if($config->get($player->getName() . "-transfer") === true) {
                        BankForm::transferMoneyFormFremd($player, $id);
                    }

                    break;

                case 3:
                    self::bankleaveConfirm($player, $id);

                    break;
                case 4:

                    break;

            }


            return true;
        });
        $config = Main::getInstance()->getConfig();
        $form->setTitle($config->get("playerform-title"));
        $form->setContent(str_replace("{bank-inhalt}", MySQLProvider::getMoneyfromID($id), $config->get("playerform-content")));
        $pushallowed = "§7» §fGeld einzahlen\n§8[§aErlaubt§8]";
        $pushforbiden = "§7» §fGeld einzahlen\n§8[§cKein Zugriff§8]";
        $pullallowed = "§7» §fGeld auszahlen\n§8[§aErlaubt§8]";
        $pullforbiden = "§7» §fGeld auszahlen\n§8[§cKein Zugriff§8]";
        $transferallowed = "§7» §fGeld überweisen\n§8[§aErlaubt§8]";
        $transferforbiden = "§7» §fGeld überweisen\n§8[§cKein Zugriff§8]";
        $data = new Config(Main::getInstance()->getDataFolder()."/permissions/{$id}.yml", 2);
        if($data->get($player->getName() . "-deposit")) {
            $form->addButton($pushallowed);
        }else{
            $form->addButton($pushforbiden);
        }
        if($data->get($player->getName() . "-withdraw")) {
            $form->addButton($pullallowed);
        }else{
            $form->addButton($pullforbiden);
        }
        if($data->get($player->getName() . "-transfer")) {
            $form->addButton($transferallowed);
        }else{
            $form->addButton($transferforbiden);
        }

        $form->addButton("§7» §cKonto verlassen");

        $form->addButton("§7Schließen");

        $form->sendToPlayer($player);

    }
    public static function pushMoneyFormFremd(Player $player, int $id){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) use($id){
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if (is_numeric($data[0])) {
                    if (EconomyProvider::getPlayerMoney($player->getName()) >= $data[0]) {
                        $money = (int)$data[0];
                        AccountAPI::pushMoney($id, $player, $money);
                    } else {
                        self::errorform($player, $cfg->get("not-enough-pay"));
                    }

                } else {
                    $player->sendMessage(Main::$prefix . $cfg->get("only-money"));
                }
            }

        });
        $form->setTitle("§l§aGeld Einzahlen");
        $form->addInput("§fWähle die Anzahl aus die du Einzahlen willst", "1000");
        $form->sendToPlayer($player);

    }

    public static function pullMoneyFormFremd(Player $player, int $id){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) use($id){
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if(is_numeric($data[0])) {
                    if (MySQLProvider::getMoneyfromID($id) >= $data[0]) {
                        EconomyProvider::addMoneytoPlayer($player->getName(), $data[0]);
                        AccountAPI::pullMoney($id, $player, $data[0]);
                    } else {
                        self::errorform($player, $cfg->get("not-enough-get"));
                    }
                }else{
                    self::errorform($player, $cfg->get("no-input"));
                }
            }
        });
        $form->setTitle("§aGeld auszahlen");
        $form->addInput("Wähle die Anzahl aus die du Auszahlen willst", "1000");
        $form->sendToPlayer($player);

    }

    public static function transferMoneyFormFremd(Player $player, $id){

        $formapi = Main::getInstance()->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function (Player $player, $data) use($id){
            $cfg = Main::getInstance()->getConfig();
            if($data !== null){
                if(is_numeric($data[0])) {
                    $playerrealname = $data[1];
                    $pname = BankAPI::getRealNamebyString($data[1]);
                    if($pname !== false){
                        $playerrealname = BankAPI::getRealNamebyString($data[1]);
                    }
                    if (MySQLProvider::existsAccount($playerrealname) or MySQLProvider::existsGlobalAccount($playerrealname)) {
                        if(strtolower($playerrealname) != strtolower($player->getName())) {
                            if (AccountAPI::readMoney($player) >= $data[0]) {
                                $senderid = $id;
                                $recid = MySQLProvider::getIDfromAccount($playerrealname);
                                $whobank = false;
                                if($data[2] === true) {
                                    if (MySQLProvider::existsGlobalAccount($playerrealname)) {
                                        $recid = MySQLProvider::getIDfromAccount($playerrealname, true);
                                        $whobank = true;
                                    }
                                }
                                AccountAPI::transferMoney($senderid, $recid, $player, $playerrealname, $data[0], $whobank);

                            } else {
                                self::errorform($player, $cfg->get("not-enough-transfer"));
                            }

                        }else{
                            self::errorform($player, $cfg->get("no-own-transfer"));
                        }

                    }else{
                        self::errorform($player, $cfg->get("reciver-no-account"));
                    }
                }else{
                    self::errorform($player, $cfg->get("no-input"));

                }

            }
        });
        $form->setTitle("§aGeld überweisen");
        $form->addInput("Wie viel soll überwiesen werden? (Coins Anzahl)", "1000");
        $form->addInput("Zu wem soll es überwiesen werden? (Spieler Name)", "Giulizo");
        $form->addToggle("§8[§aLinks§8] §cPrivates Konto\n§8[§bRechts§8] §6Globales Konto", false);
        $form->sendToPlayer($player);

    }

    public static function bankleaveConfirm(Player $player, int $id){
        $api = $player->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $player, bool $data = null) use($id){
            $result = $data;
            if($result === null){
                return true;
            }
            switch($result){

                case true:
                    YAMLProvider::deleteZugang($id, $player->getName());
                    YAMLProvider::deletePlayer($id, $player->getName());
                    $player->sendMessage("§aDu bist nun kein Mitglied mehr von der Bank von ". MySQLProvider::getOwnerfromID($id) . ".");
                    break;
                case false:
                    $player->sendMessage("§cDu hast den Vorgang abgebrochen.");
                    break;
            }
            return true;
        });
        $form->setTitle("§e§lBist du dir sicher?");
        $form->setContent("§cWillst du das Konto wirklich unwiederuflich verlassen?");
        $form->setButton1("§7» §aJa");
        $form->setButton2("§7» §cAbbruch");
        $form->sendToPlayer($player);
    }

}