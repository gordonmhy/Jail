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
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class TpjailCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch (strtolower($cmd->getName())) {
            case "tpjail":
                if (isset($args[0]) !== true) {
                    return false;
                }
                if ($issuer->hasPermission("jail.command.jailtp") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("no.permission"));
                    return true;
                }
                $jail = $args[0];
                if (isset($args[1])) {
                    $name = $args[1];
                }
                if ($this->getPlugin()->jailExists($jail) !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("jail.not.exist"));
                    return true;
                }
                if (isset($args[1])) {
                    $target = $this->getPlugin()->getServer()->getPlayer($name);
                }
                if (isset($args[1]) && $target === null) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("player.not.exist"));
                    return true;
                }
                if (isset($args[1])) {
                    if ($issuer instanceof Player !== true) {
                        $issuer->sendMessage($this->getPlugin()->getMessage("only.works.in.game"));
                        return true;
                    }
                    if ($this->getPlugin()->tpJail($target, $jail) !== false) {
                        $issuer->sendMessage(str_replace("%target%", $args[1], str_replace("%jail%", $jail, $this->getPlugin()->getMessage("tpjail.you.tp.target.to.jail"))));
                        $target->sendMessage(str_replace("%jail%", $jail, $this->getPlugin()->getMessage("tpjail.you.being.tp.to.jail")));
                        return true;
                    }
                } else {
                    if ($this->getPlugin()->tpJail($issuer, $jail) !== false) {
                        $issuer->sendMessage($this->getPlugin()->getMessage("tpjail.you.tp.to.that"));
                        return true;
                    }
                }
                return true;
                break;
        }
        return false;
    }

}

