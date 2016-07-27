<?php
/*
 * This file is a part of UltimateParticles.
 * Copyright (C) 2016 hoyinm14mc
 *
 * UltimateParticles is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UltimateParticles is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UltimateParticles. If not, see <http://www.gnu.org/licenses/>.
 */

namespace hoyinm14mc\jail\commands;

use hoyinm14mc\jail\base\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

class SwitchjailCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, $label, array $args)
    {
        switch ($cmd->getName()) {
            case "switchjail":
                if (isset($args[1]) !== true) {
                    return false;
                }
                if ($issuer->hasPermission("jail.command.switchjail") !== true) {
                    $issuer->sendMessage($this->getPlugin()->colorMessage("&cYou don't have permission for this!"));
                    return true;
                }
                $name = $args[0];
                if($this->getPlugin()->isJailed($name) !== true){
                    $issuer->sendMessage($this->getPlugin()->colorMessage("&cPlayer with that name isn't jailed!"));
                    return true;
                }
                $jail = $args[1];
                if($this->getPlugin()->jailExists($jail) !== true){
                    $issuer->sendMessage($this->getPlugin()->colorMessage("&cTarget jail doesn't exist!"));
                    return true;
                }
                $t = $this->getPlugin()->data->getAll();
                $t[$name]["jail"] = $jail;
                $this->getPlugin()->data->setAll($t);
                $this->getPlugin()->data->save();
                $player = $this->getPlugin()->getServer()->getPlayer($name);
                if($player !== null){
                    $this->getPlugin()->tpJail($player);
                    $player->sendMessage($this->getPlugin()->colorMessage("&dYou have been switched to another jail."));
                }
                $issuer->sendMessage($this->getPlugin()->colorMessage("&dYou switched ".$name." to another jail!"));
                return true;
                break;
        }
    }

}

?>