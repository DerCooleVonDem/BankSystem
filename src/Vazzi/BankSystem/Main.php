<?php

declare(strict_types=1);

namespace Vazzi\BankSystem;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use Vazzi\BankSystem\command\BankCommand;
use Vazzi\BankSystem\Provider\MySQLProvider;
use pocketmine\permission\Permission;

class Main extends PluginBase{

    public static $prefix;
    public static $instance;
    public static $economy;

    /**
     * @var MySQLProvider
     */
    private $db;

    public function onLoad(){
        self::$instance = $this;
    }

    public function onEnable() {
        $this->saveResource("config.yml");
        $config = $this->getConfig();
        self::$prefix = $config->get("prefix");
        @mkdir($this->getDataFolder() . "/players");
        $this->getServer()->getLogger()->info(self::$prefix . "BankSystem activated - developed by Vazzi & DerCooleVonDem");
        $this->registerCommand();
        self::registerPerms();
        date_default_timezone_set("Europe/Berlin");
        $this->db = new MySQLProvider($this);
        self::$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");

    }

    public static function getInstance() {
        return self::$instance;
    }

    public function registerCommand(){
        $cmds = $this->getServer()->getCommandMap();
        $cmds->register('bank', new BankCommand());
    }

    public function registerPerms(){
        $config = $this->getConfig();
        self::registerPermission(new Permission($config->get("perm"), "Default Permission", Permission::DEFAULT_FALSE));
        self::registerPermission(new Permission($config->get("admin-perm"), "", Permission::DEFAULT_OP));
    }

    public static function registerPermission(Permission $perm, Permission $parent = null){
        if($parent instanceof Permission){
            $parent->getChildren()[$perm->getName()] = true;

            return self::registerPermission($perm);
        }
        Server::getInstance()->getPluginManager()->addPermission($perm);

        return Server::getInstance()->getPluginManager()->getPermission($perm->getName());
    }

}
