<?php

namespace Vazzi\BankSystem\Provider;

use Vazzi\BankSystem\Main;

class EconomyProvider
{

    public static function getPlayerMoney($playername){
        return Main::$economy->mymoney($playername);
    }

    public static function addMoneytoPlayer($playername, $money){
        Main::$economy->addMoney($playername, $money);
    }

    public static function removeMoneyfromPlayer($playername, $money){
        Main::$economy->reduceMoney($playername, $money);
    }

}