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

class JailTimingTask extends BaseTask
{

    public function onRun(int $currentTick)
    {
        $t = $this->getPlugin()->data->getAll();
        foreach ($this->getPlugin()->getAllJailedPlayerNames() as $name) {
            if ($this->getPlugin()->getConfig()->get("offline-time-counting") !== false) {
                if ($t[$name]["jailed"] !== false && isset($this->getPlugin()->prisoner_time[$name]) !== false) {
                    $this->getPlugin()->prisoner_time[$name] = $this->getPlugin()->prisoner_time[$name] - 1;
                    if ($this->getPlugin()->prisoner_time[$name] < 0) {
                        $this->getPlugin()->unjail($name);
                    }
                }
            } else {
                if ($this->getPlugin()->getServer()->getPlayer($name) !== null
                    && $t[$name]["jailed"] !== false
                    && isset($this->getPlugin()->prisoner_time[$name]) !== false
                ) {
                    $this->getPlugin()->prisoner_time[$name] = $this->getPlugin()->prisoner_time[$name] - 1;
                    if ($this->getPlugin()->prisoner_time[$name] < 0) {
                        $this->getPlugin()->unjail($name);
                    }
                }
            }
        }
    }

}

