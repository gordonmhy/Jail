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

namespace hoyinm14mc\jail\tasks\updater;

use hoyinm14mc\jail\base\BaseTask;

class AutoUpdateChecker extends BaseTask
{

    public function onRun($currentTick)
    {
        $this->getPlugin()->getLogger()->info($this->getPlugin()->colorMessage("&eFetching latest version from repository..."));
        if(is_dir($this->getPlugin()->getServer()->getDataPath()."tmp")){
            $this->getPlugin()->getLogger()->info($this->getPlugin()->colorMessage("&4Error: Mobile device not supported!"));
        }else if(fopen("http://www.google.com:80/","r") !== true){
            $this->getPlugin()->getLogger()->info($this->getPlugin()->colorMessage("&4Error: No internet connectivity!"));
        }else{
            $this->getPlugin()->getServer()->getScheduler()->scheduleAsyncTask(new AsyncUpdateChecker());
        }
    }

}

?>