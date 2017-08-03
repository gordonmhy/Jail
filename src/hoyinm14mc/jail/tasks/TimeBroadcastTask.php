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

namespace hoyinm14mc\jail\tasks;

use hoyinm14mc\jail\base\BaseTask;

class TimeBroadcastTask extends BaseTask
{

    private $dots = "";

    private $emoji = 0;

    public function onRun(int $currentTick)
    {
        if ($this->getPlugin()->getConfig()->get("show-prisoner-timer") !== false) {
            foreach ($this->getPlugin()->getAllJailedPlayerNames() as $name) {
                $player = $this->getPlugin()->getServer()->getPlayer($name);
                if ($player !== null) {
                    $length = strlen("&3You have been jailed!");
                    if (strlen($this->dots) + 5 != $length) {
                        $this->dots = $this->dots . ">";
                    } else {
                        $this->dots = "";
                    }
                    if ($this->emoji < 15) {
                        $this->emoji = $this->emoji + 1;
                    } else {
                        $this->emoji = 0;
                    }
                    $t = $this->getPlugin()->data->getAll();
                    $j = $this->getPlugin()->data1->getAll();
                    $bail_dis = ("&2" . $this->getPlugin()->getMessage("timer.broadcast.bail") . ": &d") . (($this->getPlugin()->getEco() !== null && $this->getPlugin()->getConfig()->get("allow-bail") !== false && filter_var($j[$t[strtolower($player->getName())]["jail"]]["allow-bail"], FILTER_VALIDATE_BOOLEAN) !== false) ? ($this->getPlugin()->isJailTimeInfinite(strtolower($player->getName())) !== false) ? $this->getPlugin()->getMessage("timer.broadcast.notallowed") : "$" . (($this->getPlugin()->prisoner_time[strtolower($player->getName())] * ($this->getPlugin()->getConfig()->get("bail-per-second")))) : ($this->getPlugin()->getMessage("timer.broadcast.disabled"))) . ($this->emoji < 8 ? " ^o^" : " ");
                    if (isset($this->getPlugin()->prisoner_time[strtolower($name)])) {
                        $time = $this->getPlugin()->prisoner_time[strtolower($name)];
                        $seconds = $time;
                        $minutes = 0;
                        $hours = 0;
                        while ($seconds > 59) {
                            $minutes = $minutes + 1;
                            $seconds = $seconds - 60;
                        }
                        while ($minutes > 59) {
                            $hours = $hours + 1;
                            $minutes = $minutes - 60;
                        }
                        $time_dis = "&e" . ($hours < 10 ? "0" . $hours : $hours) . "&b:&e" . ($minutes < 10 ? "0" . $minutes : $minutes) . "&b:&e" . ($seconds < 10 ? "0" . $seconds : $seconds);
                        $player->sendTip($this->getPlugin()->colorMessage($this->getPlugin()->getMessage("timer.broadcast.jailed") . "\n" . $this->getPlugin()->getMessage("timer.broadcast.timeleft") . ": " . $time_dis . "\n" . $bail_dis . "&r\n&l&c" . $this->dots));
                    } else {
                        $player->sendTip($this->getPlugin()->colorMessage($this->getPlugin()->getMessage("timer.broadcast.jailed") . "\n" . $this->getPlugin()->getMessage("timer.broadcast.timeleft") . ": &e" . $this->getPlugin()->getMessage("timer.broadcast.infinite") . "\n" . $bail_dis . "&r\n&l&c" . $this->dots));
                    }
                }
            }
        }
    }

}

