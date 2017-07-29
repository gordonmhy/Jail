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

use hoyinm14mc\jail\Jail;
use hoyinm14mc\jail\base\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

class SwitchjailCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch (strtolower($cmd->getName())) {
            case "switchjail":
                if (isset($args[1]) !== true) {
                    return false;
                }
                if ($issuer->hasPermission("jail.command.switchjail") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("no.permission"));
                    return true;
                }
                $name = $args[0];
                if ($this->getPlugin()->isJailed($name) !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("player.not.jailed"));
                    return true;
                }
                $jail = $args[1];
                if ($this->getPlugin()->jailExists($jail) !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("jail.not.exist"));
                    return true;
                }
                $t = $this->getPlugin()->data->getAll();
                $t[$name]["jail"] = $jail;
                $this->getPlugin()->data->setAll($t);
                $this->getPlugin()->data->save();
                $player = $this->getPlugin()->getServer()->getPlayer($name);
                if ($player !== null) {
                    $this->getPlugin()->tpJail($player, $t[$name]["jail"]);
                    $player->sendMessage($this->getPlugin()->getMessage("switchjail.been.switched"));
                }
                $issuer->sendMessage(str_replace("%player%", $name, $this->getPlugin()->getMessage("switchjail.sender.success")));
                return true;
                break;
        }
    }

}

