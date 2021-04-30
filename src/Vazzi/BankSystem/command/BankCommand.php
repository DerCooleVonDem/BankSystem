<?php

namespace Vazzi\BankSystem\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\Player;
use Vazzi\BankSystem\BankAPI;
use Vazzi\BankSystem\Main;

class BankCommand extends Command
{

    public function __construct()
    {
        parent::__construct("bank");
        $this->setDescription(Main::getInstance()->getConfig()->get("cmd-description"));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){
            BankAPI::mainForm($sender);
        }
    }
}