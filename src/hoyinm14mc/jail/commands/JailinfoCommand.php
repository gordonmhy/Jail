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

class JailinfoCommand extends BaseCommand
{

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch ($cmd->getName()) {
            case "jailinfo":
                if ($issuer->hasPermission("jail.command.jailinfo") !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("no.permission"));
                    return true;
                }
                if (isset($args[0]) !== true) {
                    return false;
                }
                $jail = $args[0];
                if ($this->getPlugin()->jailExists($jail) !== true) {
                    $issuer->sendMessage($this->getPlugin()->getMessage("jail.not.exist"));
                    return true;
                }
                $j = $this->getPlugin()->data1->getAll();
                $t = $this->getPlugin()->data->getAll();
                $info_PrisonerNames = [];
                foreach ($t as $prisonerName => $value) {
                    if ($t[strtolower($prisonerName)]["jail"] == $jail
                        && $this->getPlugin()->isJailed(strtolower($prisonerName)) !== false
                    ) {
                        $info_PrisonerNames[$prisonerName] = $prisonerName;
                    }
                }
                $info_PrisonerCount = count($info_PrisonerNames);
                $info_JailPos = (string)$j[$jail]["pos"]["x"] . ":" .
                    (string)$j[$jail]["pos"]["y"] . ":" .
                    (string)$j[$jail]["pos"]["z"] . ":" .
                    (string)$j[$jail]["pos"]["level"];
                $info_JailC1Pos = (string)$j[$jail]["c1"]["x"] . ":" .
                    (string)$j[$jail]["c1"]["y"] . ":" .
                    (string)$j[$jail]["c1"]["z"] . ":" .
                    (string)$j[$jail]["c1"]["level"];
                $info_JailC2Pos = (string)$j[$jail]["c2"]["x"] . ":" .
                    (string)$j[$jail]["c2"]["y"] . ":" .
                    (string)$j[$jail]["c2"]["z"] . ":" .
                    (string)$j[$jail]["c2"]["level"];
                $mines = new Mines($this->getPlugin());
                $info_isMineSet = (string)($mines->hasMineSet($jail) ? "true" : "false");
                //Information output to issuer
                $issuer->sendMessage(str_replace("%jail%", $jail,
                    str_replace("%pos%", $info_JailPos,
                        str_replace("%c1%", $info_JailC1Pos,
                            str_replace("%c2%", $info_JailC2Pos,
                                str_replace("%count%", $info_PrisonerCount,
                                    str_replace("%prisoners%", implode(",", $info_PrisonerNames),
                                        str_replace("%isMineSet%", $info_isMineSet, $this->getPlugin()->getMessage("jailinfo")))))))));
                return true;
                break;
        }
    }

}