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

use hoyinm14mc\jail\Jail;
use hoyinm14mc\jail\base\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

class SetjailCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, $label, array $args)
    {
        switch (strtolower($cmd->getName())) {
            case "setjail":
                if (isset($args[0]) !== true) {
                    return false;
                }
                if ($issuer instanceof Player !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("only.works.in.game"));
                    return true;
                }
                if ($issuer->hasPermission("jail.command.setjail") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("no.permission"));
                    return true;
                }
                if ($issuer->getGamemode() != 1) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("setjail.must.creative.mode"));
                    return true;
                }
                $this->getPlugin()->selection_mode[] = strtolower($issuer->getName());
                $this->getPlugin()->jailName_tmp[strtolower($issuer->getName())] = $args[0];
                $issuer->sendMessage(str_replace("%jail%", $args[0], $this->getPlugin()->getMessage("setjail.initialization.start")));
                $issuer->sendMessage($this->getPlugin()->getMessage("setjail.initialize.2"));
                $issuer->sendMessage($this->getPlugin()->getMessage("setjail.initialize.3"));
                return true;
                break;
        }
    }

}

?>