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

namespace hoyinm14mc\jail\listeners;

use hoyinm14mc\jail\base\BaseListener;
use hoyinm14mc\jail\tasks\updater\Updater;
use pocketmine\event\server\ServerCommandEvent;

class ServerListener extends BaseListener
{

    public function onServerCommand(ServerCommandEvent $event)
    {
        $updater = new Updater();
        if ($updater->isUpdateAwaiting() !== false) {
            $cmd = $event->getCommand();
            if ($cmd == "Y") {

            } else if ($cmd == "n") {
                $this->getPlugin()->getLogger()->info($this->getPlugin()->colorMessage("&eYou selected 'no'"));
                $this->getPlugin()->getLogger()->info($this->getPlugin()->colorMessage("&eYou may download it by yourself here: " . $this->getPlugin()->update->get("url")));
                $updater->setUpdateAwaiting(false);
            } else {
                $this->getPlugin()->getLogger()->info($this->getPlugin()->colorMessage("&bYour version is not the latest one! Latest version: &f" . $this->getPlugin()->update->get("ver")));
                $this->getPlugin()->getLogger()->info($this->getPlugin()->colorMessage("&cDo you want to download and install it now? (Y/n)"));
            }
            $event->setCancelled(true);
        }
    }

}

?>