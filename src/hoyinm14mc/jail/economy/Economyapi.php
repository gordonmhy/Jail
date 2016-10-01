<?php
/*
 * This file is a part of Jail.
 * Copyright (C) 2016 hoyinm14mc
 *
 * Jail is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jail is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jail. If not, see <http://www.gnu.org/licenses/>.
 */

namespace hoyinm14mc\jail\economy;

use hoyinm14mc\jail\Jail;
use hoyinm14mc\jail\base\BaseEconomy;
use pocketmine\Player;

class Economyapi extends BaseEconomy
{

    public function bail(Player $player): bool
    {
        if ($this->getPlugin()->isJailed(strtolower($player->getName())) !== true) {
            $player->sendMessage($this->getPlugin()->getMessage("you-not-jailed"));
            return false;
        }
        $t = $this->getPlugin()->data->getAll();
        $money = $this->getPlugin()->getEco()->getInstance()->myMoney($player);
        if ($money < ($t[strtolower($player->getName())]["seconds"] * $this->getPlugin()->getConfig()->get("bail-per-second") + 1)) {
            $player->sendMessage($this->getPlugin()->colorMessage("&cYou don't have enough money to bail!\n&cYou need " . ($t[strtolower($player->getName())]["seconds"] * ($this->getPlugin()->getConfig()->get("bail-per-second")) + 1)));
            return false;
        }
        $this->getPlugin()->getEco()->getInstance()->reduceMoney($player, ($t[strtolower($player->getName())]["seconds"] * ($this->getPlugin()->getConfig()->get("bail-per-second")) + 1));
        $this->getPlugin()->unjail(strtolower($player->getName()));
        $player->sendMessage($this->getPlugin()->getMessage("economy-unjailed-success"));
        $player->sendMessage("Bank : -$" . ($t[strtolower($player->getName())]["seconds"] * ($this->getPlugin()->getConfig()->get("bail-per-second")) + 1) . " | $" . $this->getPlugin()->getEco()->getInstance()->myMoney($player) . " remaining in your account");
        return true;
    }

}

?>