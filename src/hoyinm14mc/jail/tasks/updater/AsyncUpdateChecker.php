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

namespace hoyinm14mc\jail\tasks\updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

class AsyncUpdateChecker extends AsyncTask
{

    public function __construct()
    {
    }

    public function onRun()
    {
        $iden = json_decode(Utils::getURL("https://api.github.com/repos/hoyinm14mc/Jail/releases"), true);
        $iden = $iden[0];
        $arr = [];
        $arr["ver"] = substr($iden["name"], 5);
        $arr["url"] = $iden["assets"][0]["browser_download_url"];
        $this->setResult($arr);
    }

    public function onCompletion(Server $server)
    {
        $plugin = $server->getPluginManager()->getPlugin("Jail");
        if ($this->getResult()["ver"] != strtolower($plugin->getDescription()->getVersion())) {
            $plugin->getLogger()->info($plugin->colorMessage("&bYour version is not the latest one! Latest version: &f" . $this->getResult()["ver"]));
            $file_pl = "Jail_v" . $plugin->getDescription()->getVersion() . ".phar";
            $file_new_pl = "Jail_v" . $this->getResult()["ver"] . ".phar";
            $path = $plugin->getServer()->getPluginPath() . $file_pl;
            if ($plugin->getConfig()->get("updater-auto-install") !== false) {
                $plugin->getLogger()->info($plugin->colorMessage("&eInstalling new version..."));
                if (file_exists($path) !== false) {
                    $plugin->getServer()->getScheduler()->scheduleAsyncTask(new AsyncUpdateInstaller($this->getResult()["url"], $this->getResult()["ver"], $file_pl, $file_new_pl, $path));
                } else {
                    $plugin->getLogger()->info($plugin->colorMessage("&4An error occured upon installation of latest version!"));
                    $plugin->getLogger()->info($plugin->colorMessage("&4You can still manually download and install it: &f" . $this->getResult()["url"]));
                }
            } else {
                $plugin->getLogger()->info($plugin->colorMessage("^6Download link: &f" . $this->getResult()["url"]));
            }
        } else {
            $plugin->getLogger()->info($plugin->colorMessage("&6No update found!"));
        }
    }

}

?>