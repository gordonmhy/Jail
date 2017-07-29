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

class VotejailCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch ($cmd->getName()) {
            case "votejail":
                if (isset($args[0]) !== true) {
                    return false;
                }
                if ($issuer->hasPermission("jail.command.votejail") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("no.permission"));
                    return true;
                }
                $target = $this->getPlugin()->getServer()->getPlayer($args[0]);
                if ($target === null) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("player.not.exists"));
                    return true;
                }
                $t = $this->getPlugin()->data->getAll();
                if (in_array(strtolower($issuer->getName()), $t[strtolower($target->getName())]["VoteForJail"]["votedBy"]) !== false) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("vote.sender.votedAlready"));
                    return true;
                }
                if ($this->getPlugin()->voteForJail(strtolower($target->getName()), strtolower($issuer->getName())) !== false) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("vote.sender.votedSuccessfully"));
                } else {
                    $issuer->sendMessage($this->getPlugin()->getMessage("command.error.duringExecution"));
                }
                return true;
                break;
        }
    }

}

