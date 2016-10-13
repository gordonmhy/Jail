<?php
/*
 * This file is the main class of Jail.
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

namespace hoyinm14mc\jail;

use hoyinm14mc\jail\commands\BailCommand;
use hoyinm14mc\jail\commands\DeljailCommand;
use hoyinm14mc\jail\commands\JailCommand;
use hoyinm14mc\jail\commands\JailedCommand;
use hoyinm14mc\jail\commands\JailsCommand;
use hoyinm14mc\jail\commands\SetjailCommand;
use hoyinm14mc\jail\commands\SwitchjailCommand;
use hoyinm14mc\jail\commands\TpjailCommand;
use hoyinm14mc\jail\commands\UnjailCommand;
use hoyinm14mc\jail\commands\UpdaterExecutor;
use hoyinm14mc\jail\listeners\BlockListener;
use hoyinm14mc\jail\listeners\PlayerListener;
use hoyinm14mc\jail\listeners\EntityListener;
use hoyinm14mc\jail\listeners\ServerListener;
use hoyinm14mc\jail\tasks\JailTimingTask;
use hoyinm14mc\jail\tasks\TimeBroadcastTask;
use hoyinm14mc\jail\tasks\updater\AsyncUpdateChecker;
use hoyinm14mc\jail\tasks\updater\AutoUpdateChecker;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Jail extends PluginBase
{

    /**
     * @var multitype:array
     */
    public $c1_tmp = [];

    /**
     * @var multitype:array
     */
    public $c2_tmp = [];

    /**
     * @var multitype:string
     */
    public $pos_tmp = [];

    /**
     * @var multitype:string
     */
    public $jailName_tmp = [];

    /**
     * @var multitype:string
     */
    public $selection_mode = [];

    /**
     * @var null|object
     */
    private $eco = null;
    
    private $langList = [
		"def" => "Default",
		"en" => "English",
		"zh" => "繁體中文",
	];
	private $lang = [];
    
    public function getCommandMessage(string $command, $lang = false) : array{
		if($lang === false){
			$lang = $this->getConfig()->get("default-lang");
		}
		$command = strtolower($command);
			return $this->lang[$lang]["commands"][$command];
	}
	
	public function getMessage(string $key, array $params = [], string $player = "console", $lang = false) : string{
		if($lang === false){
			$lang = $this->getConfig()->get("default-lang");
		}
		return $this->colorMessage($this->lang[$lang][$key], $params);
		
		return "Language matching key \"$key\" does not exist.";
	}
	
    public function onEnable()
    {
        $this->getLogger()->info("Loading configurations...");
        if (is_dir($this->getDataFolder()) !== true) {
            mkdir($this->getDataFolder());
        }
        $this->saveDefaultConfig();
        if ($this->getConfig()->exists("v") !== true || $this->getConfig()->get("v") != $this->getDescription()->getVersion()) {
            $this->getLogger()->info("Update found!  Updating configuration...");
            $this->getLogger()->info("All settings are being reset. The old config is saved in the plugin data folder.");
            if (file_exists($this->getDataFolder() . "config.yml.old")) {
                unlink($this->getDataFolder() . "config.yml.old");
            }
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.yml.old");
            $this->saveDefaultConfig();
        }
        $this->reloadConfig();
        $this->data = new Config($this->getDataFolder() . "players.yml", Config::YAML, array());
        $this->data1 = new Config($this->getDataFolder() . "jails.yml", Config::YAML, array());
        $this->initializeLanguage();
        $ecoPlugs = [
            "PocketMoney",
            "EconomyAPI"
        ];
        foreach ($ecoPlugs as $ecoPlug) {
            $pl = $this->getServer()->getPluginManager()->getPlugin($ecoPlug);
            if ($pl !== null) {
                $this->eco = $pl;
            }
        }
        if ($this->eco !== null) {
            $this->getLogger()->info($this->colorMessage("Loaded with " . $ecoPlug . "!"));
        }
        $this->getCommand("deljail")->setExecutor(new DeljailCommand($this));
        $this->getCommand("jail")->setExecutor(new JailCommand($this));
        $this->getCommand("jails")->setExecutor(new JailsCommand($this));
        $this->getCommand("setjail")->setExecutor(new SetjailCommand($this));
        $this->getCommand("unjail")->setExecutor(new UnjailCommand($this));
        $this->getCommand("jailed")->setExecutor(new JailedCommand($this));
        $this->getCommand("switchjail")->setExecutor(new SwitchjailCommand($this));
        $this->getCommand("tpjail")->setExecutor(new TpjailCommand($this));
        $this->getCommand("bail")->setExecutor(new BailCommand($this));
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new JailTimingTask($this), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new TimeBroadcastTask($this), 3);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new BlockListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EntityListener($this), $this);
        $this->getLogger()->info($this->colorMessage("&aLoaded Successfully!"));
        if ($this->getConfig()->get("scheduled-update-checker") !== false) {
            $this->getLogger()->info($this->colorMessage("&eInitialized scheduled update checker"));
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new AutoUpdateChecker($this), 60 * 20 * (int)$this->getConfig()->get("scheduled-update-checker-interval"));
        } else if ($this->getConfig()->get("updater-start-fetch") !== false) {
            $this->getLogger()->info($this->colorMessage("&eFetching latest version from repository..."));
            $this->getLogger()->info($this->colorMessage("&eResult will appear when server query is started."));
            if(is_dir($this->getServer()->getDataPath()."tmp")){
                $this->getLogger()->info($this->colorMessage("&4Error: Mobile device not supported!"));
            }else{
                $this->getServer()->getScheduler()->scheduleAsyncTask(new AsyncUpdateChecker());
            }
        }
    }
    
    private function initializeLanguage(){
		foreach($this->getResources() as $resource){
			if($resource->isFile() and substr(($filename = $resource->getFilename()), 0, 5) === "lang_"){
				$this->lang[substr($filename, 5, -5)] = json_decode(file_get_contents($resource->getPathname()), true);
			}
		}
		$lang = $this->getConfig()->get("default-lang");
		$this->lang["def"] = (new Config($this->getDataFolder()."messages.yml", Config::YAML, $this->lang[$lang]))->getAll();
	}

    /**
     * @return object
     */
    public function getEco()
    {
        return $this->eco;
    }

    /**
     * @param string $message
     * @return string
     */
    public function colorMessage($message){
		$colors = [
			"0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "k", "l", "m", "n", "o", "r"
		];
		foreach($colors as $code){
			$search[] = "&".$code;
			$replace[] = TextFormat::ESCAPE.$code;
		}

		return str_replace($search, $replace, $message);
	}

    /**
     * Creates a profile for the player when they first join the server
     * @param string $player_name
     * @return bool
     */
    public function initializePlayerProfile(string $player_name): bool
    {
        $t = $this->data->getAll();
        if ($this->playerProfileExists($player_name)) {
            return false;
        }
        $t[$player_name]["jailed"] = false;
        $t[$player_name]["gamemode"] = $this->getServer()->getDefaultGamemode();
        $this->data->setAll($t);
        $this->data->save();
        return true;
    }

    /**
     * @param string $player_name
     * @return bool
     */
    public function playerProfileExists(string $player_name): bool
    {
        $t = $this->data->getAll();
        return (bool)array_key_exists($player_name, $t);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function isSelectionMode(Player $player): bool
    {
        return in_array(strtolower($player->getName()), $this->selection_mode);
    }

    /**
     * @param string $jail
     * @param Position $pos
     * @return bool
     */
    public function insideJail(string $jail, Position $pos): bool
    {
        $j = $this->data1->getAll();
        if ($this->jailExists($jail) !== true) {
            return false;
        }
        return (bool)(min($j[$jail]["c1"]["x"], $j[$jail]["c2"]["x"]) <= $pos->x) && (max($j[$jail]["c1"]["x"], $j[$jail]["c2"]["x"]) >= $pos->x) && (min($j[$jail]["c1"]["y"], $j[$jail]["c2"]["y"]) <= $pos->y) && (max($j[$jail]["c1"]["y"], $j[$jail]["c2"]["y"]) >= $pos->y) && (min($j[$jail]["c1"]["z"], $j[$jail]["c2"]["z"]) <= $pos->z) && (max($j[$jail]["c1"]["z"], $j[$jail]["c2"]["z"]) >= $pos->z) && ($j[$jail]["pos"]["level"] == $pos->getLevel()->getName());
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function hasAreaSelected(Player $player): bool
    {
        return (bool)array_key_exists(strtolower($player->getName()), $this->c1_tmp) && array_key_exists(strtolower($player->getName()), $this->c2_tmp);
    }

    /**
     * @return array
     */
    public function getAllJailedPlayerNames(): array
    {
        $t = $this->data->getAll();
        $jailed = [];
        foreach ($t as $p => $value) {
            if ($t[$p]["jailed"] !== false) {
                $jailed[] = $p;
            }
        }
        return (array)$jailed;
    }

    /**
     * @param Player $player
     * @param string $jail_name
     * @param int $minutes
     * @param string $reason
     * @return bool
     */
    public function jail(Player $player, string $jail_name, int $minutes = -1, string $reason = "no reason"): bool
    {
        $t = $this->data->getAll();
        $j = $this->data1->getAll();
        if ($this->jailExists($jail_name) !== true) {
            return false;
        }
        if ($this->isJailed(strtolower($player->getName())) !== false) {
            return false;
        }
        $t[strtolower($player->getName())]["jailed"] = true;
        $t[strtolower($player->getName())]["jail"] = $jail_name;
        if ($minutes != -1) {
            $t[strtolower($player->getName())]["seconds"] = $minutes * 60;
        }
        $t[strtolower($player->getName())]["reason"] = $reason;
        $t[strtolower($player->getName())]["gamemode"] = $player->getGamemode();
        $this->data->setAll($t);
        $this->data->save();
        $player->setGamemode(($this->getConfig()->get("enable-penalty") !== true ? 2 : 0));
        $player->sendMessage($this->colorMessage("&eYou have been jailed for " . ($minutes != -1 ? $minutes : "infinite") . " minutes!\n&eReason: " . $reason));
        $player->teleport(new Position($j[$jail_name]["pos"]["x"], $j[$jail_name]["pos"]["y"], $j[$jail_name]["pos"]["z"], $this->getServer()->getLevelByName($j[$jail_name]["pos"]["level"])));
        $this->getLogger()->info($this->colorMessage("&6Jailed player " . strtolower($player->getName()) . " for " . ($minutes == -1 ? "infinite time" : ($minutes > 1 ? $minutes . " minutes" : $minutes . " minute")) . "\nReason: " . $reason));
        return true;
    }

    /**
     * @param string $player_name
     * @return bool
     */
    public function isJailTimeInfinity(string $player_name): bool
    {
        $t = $this->data->getAll();
        if ($this->isJailed($player_name) !== true) {
            return false;
        }
        return (bool)!isset($t[$player_name]["seconds"]);
    }

    /**
     * @param string $player_name
     * @return bool
     */
    public function unjail(string $player_name): bool
    {
        $t = $this->data->getAll();
        if ($this->isJailed($player_name) !== true) {
            return false;
        }
        $gm = $t[$player_name]["gamemode"];
        $t[$player_name]["jailed"] = false;
        unset($t[$player_name]["jail"]);
        if ($this->isJailTimeInfinity($player_name) !== true) {
            unset($t[$player_name]["seconds"]);
        }
        unset($t[$player_name]["reason"]);
        unset($t[$player_name]["gamemode"]);
        $this->data->setAll($t);
        $this->data->save();
        $player = $this->getServer()->getPlayer($player_name);
        if ($player !== null) {
            //Currently default spawn. Soon maybe implement EssentialsPE support
            $player->setGamemode($gm);
            $player->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
            $player->sendMessage($this->getMessage("you-unjailed-success"));
        }
        $this->getLogger()->info($this->colorMessage("&6Unjailed player " . $player_name));
        return true;
    }

    /**
     * Allows checking offline players
     * @param string $player_name
     * @return bool
     */
    public function isJailed(string $player_name): bool
    {
        $t = $this->data->getAll();
        if ($this->playerProfileExists($player_name)) {
            return (bool)$t[$player_name]["jailed"];
        }
        return false;
    }

    /**
     * @return string
     */
    public function jailedToString(): string
    {
        return implode(", ", $this->getAllJailedPlayerNames());
    }

    /**
     * @param string $jail_name
     * @param Position $pos
     * @param Position $c1
     * @param Position $c2
     * @param bool $bail
     * @param bool $escape
     */
    public function setJail(string $jail_name, Position $pos, Position $c1, Position $c2, bool $bail, bool $escape)
    {
        $j = $this->data1->getAll();
        $j[$jail_name]["pos"]["x"] = $pos->x;
        $j[$jail_name]["pos"]["y"] = $pos->y;
        $j[$jail_name]["pos"]["z"] = $pos->z;
        $j[$jail_name]["pos"]["level"] = $pos->getLevel()->getName();
        $j[$jail_name]["c1"]["x"] = $c1->x;
        $j[$jail_name]["c1"]["y"] = $c1->y;
        $j[$jail_name]["c1"]["z"] = $c1->z;
        $j[$jail_name]["c1"]["level"] = $c1->getLevel()->getName();
        $j[$jail_name]["c2"]["x"] = $c2->x;
        $j[$jail_name]["c2"]["y"] = $c2->y;
        $j[$jail_name]["c2"]["z"] = $c2->z;
        $j[$jail_name]["c2"]["level"] = $c2->getLevel()->getName();
        $j[$jail_name]["allow-bail"] = $bail;
        $j[$jail_name]["allow-escape-area"] = $escape;
        $this->data1->setAll($j);
        $this->data1->save();
    }

    /**
     * @param string $jail_name
     * @return bool
     */
    public function delJail(string $jail_name): bool
    {
        $j = $this->data1->getAll();
        if ($this->jailExists($jail_name) !== true) {
            return false;
        }
        unset($j[$jail_name]);
        $this->data1->setAll($j);
        $this->data1->save();
        return true;
    }

    /**
     * @param string $jail_name
     * @return bool
     */
    public function jailExists(string $jail_name): bool
    {
        $j = $this->data1->getAll();
        return (bool)array_key_exists($jail_name, $j);
    }

    /**
     * @return string
     */
    public function jailsToString(): string
    {
        $j = $this->data1->getAll();
        $jails = array_keys($j);
        return (string)implode(", ", $jails);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function tpJail(Player $player): bool
    {
        $t = $this->data->getAll();
        $j = $this->data1->getAll();
        if ($this->isJailed(strtolower($player->getName())) !== true) {
            return false;
        }
        $player->teleport(new Position($j[$t[strtolower($player->getName())]["jail"]]["pos"]["x"], $j[$t[strtolower($player->getName())]["jail"]]["pos"]["y"], $j[$t[strtolower($player->getName())]["jail"]]["pos"]["z"], $this->getServer()->getLevelByName($j[$t[strtolower($player->getName())]["jail"]]["pos"]["level"])));
        return true;
    }

    /**
     * Adds additional time to the prisoner
     * @param string $player_name
     * @param int $minutes
     * @return bool
     */
    public function applyPenalty(string $player_name, int $minutes = 10): bool
    {
        $t = $this->data->getAll();
        if ($this->isJailed($player_name) !== true) {
            return false;
        }
        $t[$player_name]["seconds"] = $t[$player_name]["seconds"] + ($minutes * 60);
        $this->data->setAll($t);
        $this->data->save();
        return true;
    }

}

?>
