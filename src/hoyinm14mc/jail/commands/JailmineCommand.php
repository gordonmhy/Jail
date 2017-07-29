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

class JailmineCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch ($cmd->getName()) {
            case "jailmine":
                if ($issuer instanceof Player !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("only.works.in.game"));
                    return true;
                }
                if ($issuer->hasPermission("jail.command.jailmine") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("no.permission"));
                    return true;
                }
                if (isset($args[1]) !== true) {
                    return false;
                }
                $jail = $args[1];
                if ($this->getPlugin()->jailExists($jail) !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("jail.not.exist"));
                    return true;
                }
                $mines = new Mines($this->getPlugin());
                switch (strtolower($args[0])) {
                    case "reset":
                        if ($mines->hasMineSet($jail) !== true) {
                            $issuer->sendMessage($this->getPlugin()->getMessage("mine.not.set"));
                            return true;
                        }
                        $mines->resetMine($jail);
                        $issuer->sendMessage(str_replace("%jail%", $jail, $this->getPlugin()->getMessage("mine.reset.successful")));
                        return true;
                        break;
                    case "set":
                        $mines->mineName_tmp[strtolower($issuer->getName())] = $jail;
                        $issuer->sendMessage(str_replace("%jail%", $jail, $this->getPlugin()->getMessage("mine.set.initialization")));
                        $issuer->sendMessage($this->getPlugin()->getMessage("mine.set.tap.corner.1"));
                        return true;
                        break;
                    case "rm":
                        if ($mines->hasMineSet($jail) !== true) {
                            $issuer->sendMessage($this->getPlugin()->getMessage("mine.not.set"));
                            return true;
                        }
                        $mines->rmMine($jail);
                        $issuer->sendMessage(str_replace("%jail%", $jail, $this->getPlugin()->getMessage("mine.remove.successful")));
                        return true;
                        break;
                    case "check":
                        $issuer->sendMessage($mines->hasMineSet($jail) !== true ? $this->getPlugin()->getMessage("mine.not.set") : $this->getPlugin()->getMessage("mine.is.set"));
                        return true;
                        break;
                    default:
                        return false;
                }
                break;
        }
    }

}

