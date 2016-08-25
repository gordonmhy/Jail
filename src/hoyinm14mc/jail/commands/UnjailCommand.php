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

namespace hoyinm14mc\jail\commands;

use hoyinm14mc\jail\base\BaseCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class UnjailCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, $label, array $args)
    {
        switch ($cmd->getName()) {
            case "unjail":
                if (isset($args[0]) !== true) {
                    return false;
                }
                if ($issuer->hasPermission("jail.command.unjail") !== true) {
                    $issuer->sendMessage($this->getPlugin()->colorMessage("&cYou don't have permission for this!"));
                    return true;
                }
                $name = $args[0];
                if ($this->getPlugin()->isJailed($name) !== true) {
                    $issuer->sendMessage($this->getPlugin()->colorMessage("&cPlayer is not jailed!"));
                    return true;
                }
                if ($this->getPlugin()->unjail($name) !== false) {
                    $issuer->sendMessage($this->getPlugin()->colorMessage("&aYou unjailed " . $name));
                }
                return true;
                break;
        }
    }

}

?>