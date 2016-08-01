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

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class AsyncUpdateInstaller extends AsyncTask
{

    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $ver;
    /**
     * @var bool
     */
    private $error;
    private $file_pl;
    private $file_new_pl;
    private $path;

    public function __construct(string $url, string $ver, string $file_pl, string $file_new_pl, string $path)
    {
        $this->url = $url;
        $this->ver = $ver;
        $this->file_pl = $file_pl;
        $this->file_new_pl = $file_new_pl;
        $this->path = $path;
    }

    public function onRun()
    {
        unlink($this->path);
        $file = fopen($this->file_new_pl, 'w+');
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 PocketMine-MP"]);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        file_put_contents($this->file_new_pl, curl_exec($ch));
        curl_close($ch);
        fclose($file);
    }

    public function onCompletion(Server $server)
    {
        $plugin = $server->getPluginManager()->getPlugin("Jail");
        if ($this->error !== true) {
            $plugin->getLogger()->info($plugin->colorMessage("&aSuccessfully updated version to &d" . $this->ver . "&a!"));
            $plugin->getLogger()->info($plugin->colorMessage("&aRestart server to take effect!"));
        }
    }

}

?>