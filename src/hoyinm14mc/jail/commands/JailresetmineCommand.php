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


use hoyinm14mc\jail\base\BaseCommand;
use hoyinm14mc\jail\Mines;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class JailresetmineCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch ($cmd->getName()) {
            case "jailresetmine":
                if ($issuer->hasPermission("jail.command.jailresetmine") !== true) {
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
                $mines = new Mines($this->getPlugin());
                if ($mines->hasMineSet($t[strtolower($issuer->getName())]["jail"]) !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("mine.not.set"));
                    return true;
                }
                $mines->resetMine($t[strtolower($issuer->getName())]["jail"]);
                $issuer->sendMessage($this->getPlugin()->getMessage("mine.reset.success"));
                return true;
                break;
        }
    }

}

