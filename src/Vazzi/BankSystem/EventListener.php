<?php


namespace Vazzi\BankSystem;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use Vazzi\BankSystem\Provider\YAMLProvider;

class EventListener implements Listener
{

    public function onJoin(PlayerJoinEvent $event)
    {
        YAMLProvider::createUserData($event->getPlayer());

    }
}