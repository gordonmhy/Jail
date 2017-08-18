<?php
/*
 * This file is a part of Jail.
 * Copyright (C) 2017 hoyinm14mc
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

use hoyinm14mc\jail\base\BaseEconomy;
use pocketmine\Player;
use pocketmine\item\Item;

class Pocketmoney extends BaseEconomy
{

    public function bail(Player $player): bool
    {
        if ($this->getPlugin()->isJailed(strtolower($player->getName())) !== true) {
            $player->sendMessage($this->getPlugin()->getMessage("you.not.jailed"));
            return false;
        }
        $t = $this->getPlugin()->data->getAll();
        $money = $this->getPlugin()->getEco()->getMoney(strtolower($player->getName()));
        if ($money < ($this->getPlugin()->prisoner_time[strtolower($player->getName())] * $this->getPlugin()->getConfig()->get("bail-per-second") + 1)) {
            $player->sendMessage(str_replace("%money%", ($this->getPlugin()->prisoner_time[strtolower($player->getName())] * ($this->getPlugin()->getConfig()->get("bail-per-second")) + 1), $this->getPlugin()->getMessage("bail.money.not.enough")));
            return false;
        }
        $this->getPlugin()->getEco()->setMoney(strtolower($player->getName()), $money - ($this->getPlugin()->prisoner_time[strtolower($player->getName())] * ($this->getPlugin()->getConfig()->get("bail-per-second")) + 1));
        $player->sendMessage(str_replace("%deduction%", ($this->getPlugin()->prisoner_time[strtolower($player->getName())] * ($this->getPlugin()->getConfig()->get("bail-per-second")) + 1), str_replace("%remaining%", $this->getPlugin()->getEco()->getMoney(strtolower($player->getName())), $this->getPlugin()->getMessage("bail.money.remaining"))));
        $this->getPlugin()->unjail(strtolower($player->getName()));
    }

    public function sellHand(Player $player): bool
    {
        if ($this->getPlugin()->isJailed(strtolower($player->getName())) !== true) {
            $player->sendMessage($this->getPlugin()->getMessage("you.not.jailed"));
            return false;
        }
        $item = $player->getInventory()->getItemInHand();
        if ($item->getCount() < 1) {
            $player->sendMessage($this->getPlugin()->getMessage("mine.item.count.notEnough"));
            return false;
        }
        if ($item->getId() != $this->getPlugin()->getConfig("item")) {
            $player->sendMessage($this->getPlugin()->getMessage("mine.item.notAvailable"));
            return false;
        }
        $this->getPlugin()->getEco()->setMoney(strtolower($player->getName()), $this->getPlugin()->getEco()->getMoney(strtolower($player->getName())) + $item->getCount() * $this->getPlugin()->getConfig()->get("money-per-block"));
        $player->getInventory()->setItemInHand(Item::get(0));
        $player->sendMessage(str_replace("%moneyEarned%", $item->getCount() * $this->getPlugin()->getConfig()->get("money-per-block"), str_replace("%totalMoney%", $this->getPlugin()->getEco()->getMoney(strtolower($player->getName())), str_replace("%blocksSold%", $item->getCount(), $this->getPlugin()->getMessage("mine.sellHand.success")))));
        return true;
    }

}

