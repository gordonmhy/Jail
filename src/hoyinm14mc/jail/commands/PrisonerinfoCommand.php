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
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class PrisonerinfoCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch ($cmd->getName()) {
            case "prisonerinfo":
                if ($issuer->hasPermission("jail.command.prisonerinfo") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("no.permission"));
                    return true;
                }
                if (isset($args[0]) !== true) {
                    return false;
                }
                $prisoner = $args[0];
                if ($this->getPlugin()->isJailed(strtolower($prisoner)) !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("player.not.jailed"));
                    return true;
                }
                $t = $this->getPlugin()->data->getAll();
                $jail = $t[$prisoner]["jail"];
                $seconds = (string)$this->getPlugin()->prisoner_time[strtolower($prisoner)];
                $reason = $t[$prisoner]["reason"];
                $secs = $seconds;
                $mins = 0;
                while ($secs >= 60) {
                    $secs = $secs - 60;
                    $mins++;
                }
                $hrs = 0;
                while ($mins >= 60) {
                    $hrs++;
                    $mins = $mins - 60;
                }
                $time = ($hrs < 10 ? "0" : "") . (string)$hrs . ":" . ($mins < 10 ? "0" : "") . (string)$mins . ":" . ($secs < 10 ? "0" : "") . $secs;
                $issuer->sendMessage(str_replace("%jail%", $jail,
                    str_replace("%time_left%", $time,
                        str_replace("%reason%", $reason,
                            str_replace("%prisoner%", $prisoner, $this->getPlugin()->getMessage("prisonerinfo"))))));
                return true;
                break;
        }
    }

}