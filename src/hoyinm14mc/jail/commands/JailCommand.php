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

class JailCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch (strtolower($cmd->getName())) {
            case "jail":
                if (count($args) < 3) {
                    return false;
                }
                if ($issuer->hasPermission("jail.command.jail") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("no.permission"));
                    return true;
                }
                $target = $this->getPlugin()->getServer()->getPlayer($args[0]);
                if ($target === null) {

                    $issuer->sendMessage($this->getPlugin()->getMessage("player.not.exist"));
                    return true;
                }
                $jail = $args[1];
                $time = $args[2];
                if (isset($args[3]) !== false) {
                    $reason = $this->getReason($args);
                }
                if ($this->getPlugin()->jailExists($jail) !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("jail.not.exist"));
                    return true;
                }
                if ($this->getPlugin()->getConfig()->get("op-can-be-jailed") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("jail.you.not.jail.that.player"));
                    return true;
                }
                if ($time != "-i" && (is_numeric($time) !== true || $time > 6000)) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("jail.invalid.time"));
                    return true;
                }
                if ($this->getPlugin()->isJailed(strtolower($target->getName())) !== false) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("jail.already.jailed"));
                    return true;
                }
                if ($this->getPlugin()->jail($target, $jail, ($time == "-i" ? -1 : $time), (isset($args[3]) ? $reason : "no reason")) !== false) {
                    $issuer->sendMessage(str_replace("%player%", strtolower($target->getName()), str_replace("%time%", ($time == "-i" ? "infinite" : $time), $this->getPlugin()->getMessage("jail.success.sender"))));
                }
                return true;
                break;
        }
    }

    private function getReason(array $msg): string
    {
        unset ($msg[0]);
        unset ($msg[1]);
        unset ($msg[2]);
        return implode(" ", $msg);
    }

}

