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
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

class SetjailCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, $label, array $args)
    {
        switch ($cmd->getName()) {
            case "setjail":
                if (isset($args[0]) !== true) {
                    return false;
                }
                if ($issuer instanceof Player !== true) {
                    $issuer->sendMessage($this->getPlugin()->colorMessage("&cCommand only works in-game!"));
                    return true;
                }
                if ($issuer->hasPermission("jail.command.setjail") !== true) {
                    $issuer->sendMessage($this->getPlugin()->colorMessage("&cYou don't have permission for this!"));
                    return true;
                }
                if ($issuer->getGamemode() != 1) {
                    $issuer->sendMessage($this->getPlugin()->colorMessage("&cYou must be in creative mode to use this command!"));
                    return true;
                }
                $this->getPlugin()->selection_mode[] = $issuer->getName();
                $this->getPlugin()->jailName_tmp[$issuer->getName()] = $args[0];
                $issuer->sendMessage($this->getPlugin()->colorMessage("&bInitialized jail &a" . $args[0] . " &bcreation procedure."));
                $issuer->sendMessage($this->getPlugin()->colorMessage("&ePlease tap the position where the prisoner will be teleported while they are jailed into this jail."));
                $issuer->sendMessage($this->getPlugin()->colorMessage("&7Re-join server to deactivate jail creation mode."));
                return true;
                break;
        }
    }

}

?>