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

namespace hoyinm14mc\jail\commands;

use hoyinm14mc\jail\economy\Economyplus;
use hoyinm14mc\jail\base\BaseCommand;
use hoyinm14mc\jail\economy\Economyapi;
use hoyinm14mc\jail\economy\Pocketmoney;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class BailCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch (strtolower($cmd->getName())) {
            case "bail":
                if ($issuer->hasPermission("jail.command.bail") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("no.permission"));
                    return true;
                }
                if ($issuer instanceof Player !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("only.works.in.game"));
                    return true;
                }
                if ($this->getPlugin()->isJailed(strtolower($issuer->getName())) !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("you.not.jailed"));
                    return true;
                }
                $t = $this->getPlugin()->data->getAll();
                $j = $this->getPlugin()->data1->getAll();
                if ($this->getPlugin()->getEco() === null || ($this->getPlugin()->getConfig()->get("allow-bail") !== true && filter_var($j[$t[$issuer->getName()]["jail"]]["allow-bail"], FILTER_VALIDATE_BOOLEAN) !== true)) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("bail.feature.disabled"));
                    return true;
                }
                if ($this->getPlugin()->isJailTimeInfinite(strtolower($issuer->getName())) !== false) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("bail.not.use"));
                    return true;
                }
                switch ($this->getPlugin()->getEco()->getName()) {
                    case "EconomyAPI":
                        $eco = new Economyapi($this->getPlugin());
                        $eco->bail($issuer);
                        break;
                    case "PocketMoney":
                        $eco = new Pocketmoney($this->getPlugin());
                        $eco->bail($issuer);
                        break;
                    case "EconomyPlus":
                        $eco = new Economyplus($this->getPlugin());
                        $eco->bail($issuer);
                        break;
                }
                return true;
                break;
        }
        return false;
    }

}

