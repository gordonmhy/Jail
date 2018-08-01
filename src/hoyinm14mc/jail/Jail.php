<?php
/*
 * This file is the main class of Jail.
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

namespace hoyinm14mc\jail;

use hoyinm14mc\jail\commands\BailCommand;
use hoyinm14mc\jail\commands\DeljailCommand;
use hoyinm14mc\jail\commands\JailCommand;
use hoyinm14mc\jail\commands\JailedCommand;
use hoyinm14mc\jail\commands\JailinfoCommand;
use hoyinm14mc\jail\commands\JailmineCommand;
use hoyinm14mc\jail\commands\JailresetmineCommand;
use hoyinm14mc\jail\commands\JailsCommand;
use hoyinm14mc\jail\commands\JailsellhandCommand;
use hoyinm14mc\jail\commands\PrisonerinfoCommand;
use hoyinm14mc\jail\commands\SetjailCommand;
use hoyinm14mc\jail\commands\SwitchjailCommand;
use hoyinm14mc\jail\commands\TpjailCommand;
use hoyinm14mc\jail\commands\UnjailCommand;
use hoyinm14mc\jail\commands\VotejailCommand;
use hoyinm14mc\jail\listeners\BlockListener;
use hoyinm14mc\jail\listeners\PlayerListener;
use hoyinm14mc\jail\listeners\EntityListener;
use hoyinm14mc\jail\listeners\sign\BailListener;
use hoyinm14mc\jail\listeners\sign\ResetmineListener;
use hoyinm14mc\jail\listeners\sign\SellhandListener;
use hoyinm14mc\jail\tasks\JailTimingTask;
use hoyinm14mc\jail\tasks\TimeBroadcastTask;

//use hoyinm14mc\jail\tasks\updater\AsyncUpdateChecker;
//use hoyinm14mc\jail\tasks\updater\AutoUpdateChecker;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;

class Jail extends PluginBase implements JailAPI
{

    /**
     * @var static $this |null
     */
    private static $instance = null;

    /**
     * @var array:array
     */
    public $c1_tmp = [];

    /**
     * @var array:array
     */
    public $c2_tmp = [];

    /**
     * @var array:string
     */
    public $pos_tmp = [];

    /**
     * @var array:string
     */
    public $jailName_tmp = [];

    /**
     * @var array:string
     */
    public $selection_mode = [];

    /**
     * @var array:bool
     */
    public $allowBail_tmp = [];

    /**
     * @var array:bool
     */
    public $allowVisit_tmp = [];

    /**
     * @var null|object
     */
    private $eco = null;

    /**
     * @var array
     */
    private $lang = [];

    /**
     * @var Config
     */
    public $data;

    /**
     * @var Config
     */
    public $data1;

    /**
     * Player Name [STRING] => Time remaining [INT]
     * @var array
     */
    public $prisoner_time = [];

    /**
     * @param string $command
     * @param bool $lang
     * @return array
     */
    public function getCommandMessage(string $command, bool $lang = false) : array
    {
        if ($lang === false) {
            $lang = $this->getConfig()->get("default-lang");
        }
        $command = strtolower($command);
        return $this->lang[$lang]["commands"][$command];
    }

    /**
     * @param string $key
     * @param bool $lang
     * @return string
     */
    public function getMessage(string $key, bool $lang = false) : string
    {
        if ($lang !== true) {
            $lang = $this->getConfig()->get("default-lang");
        }
        return isset($this->lang[$lang][$key]) !== true ? $key : $this->colorMessage($this->lang[$lang][$key]);
    }

    public function onEnable()
    {
        $this->getLogger()->info("Loading configurations...");
        if (is_dir($this->getDataFolder()) !== true) {
            mkdir($this->getDataFolder());
        }
        $oldVersion = $this->getDescription()->getVersion();
        if (file_exists($this->getDataFolder() . "config.yml") !== false) {
            $this->reloadConfig();
            if ($this->getConfig()->exists("v") !== true || $this->getConfig()->get("v") !== $this->getDescription()->getVersion()) {
                $oldVersion = $this->getConfig()->get("v");
                if (file_exists($this->getDataFolder() . "config.yml.old")) {
                    unlink($this->getDataFolder() . "config.yml.old");
                }
                $oldV = (string)$this->getConfig()->get("v");
                $this->getLogger()->info("Update found!  Updating configuration...");
                $this->getLogger()->info("All settings are being reset. The old config is saved in the plugin data folder.");
                if (file_exists($this->getDataFolder() . "config.old.v" . $oldV . ".yml") !== false) {
                    unlink($this->getDataFolder() . "config.old.v" . $oldV . ".yml");
                }
                rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.old.v" . $oldV . ".yml");
            }
        }
        $this->saveDefaultConfig();
        $this->reloadConfig();
        $this->data = new Config($this->getDataFolder() . "players.yml", Config::YAML, array());
        $this->data1 = new Config($this->getDataFolder() . "jails.yml", Config::YAML, array());
        $this->initializeLanguage();
        $ecoPlugs = [
            "PocketMoney",
            "EconomyAPI",
            "EconomyPlus"
        ];
        foreach ($ecoPlugs as $ecoPlug) {
            $pl = $this->getServer()->getPluginManager()->getPlugin($ecoPlug);
            if ($pl !== null) {
                $this->eco = $pl;
            }
        }
        if ($this->eco !== null) {
            $this->getLogger()->info($this->colorMessage("Loaded with " . $this->getEco()->getName() . "!"));
        }
        $t = $this->data->getAll();
        foreach ($t as $name => $value) {
            if (isset($t[strtolower($name)]["seconds"]) !== false && $t[strtolower($name)]["jailed"] !== false) {
                $this->prisoner_time[strtolower($name)] = $t[strtolower($name)]["seconds"];
            }
        }
        self::$instance = $this;
        $this->getCommand("deljail")->setExecutor(new DeljailCommand($this));
        $this->getCommand("jail")->setExecutor(new JailCommand($this));
        $this->getCommand("jails")->setExecutor(new JailsCommand($this));
        $this->getCommand("setjail")->setExecutor(new SetjailCommand($this));
        $this->getCommand("unjail")->setExecutor(new UnjailCommand($this));
        $this->getCommand("jailed")->setExecutor(new JailedCommand($this));
        $this->getCommand("switchjail")->setExecutor(new SwitchjailCommand($this));
        $this->getCommand("tpjail")->setExecutor(new TpjailCommand($this));
        $this->getCommand("bail")->setExecutor(new BailCommand($this));
        $this->getCommand("votejail")->setExecutor(new VotejailCommand($this));
        $this->getCommand("jailmine")->setExecutor(new JailmineCommand($this));
        $this->getCommand("jailsellhand")->setExecutor(new JailsellhandCommand($this));
        $this->getCommand("jailresetmine")->setExecutor(new JailresetmineCommand($this));
        $this->getCommand("jailinfo")->setExecutor(new JailinfoCommand($this));
        $this->getCommand("prisonerinfo")->setExecutor(new PrisonerinfoCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new JailTimingTask($this), 20);
        $this->getScheduler()->scheduleRepeatingTask(new TimeBroadcastTask($this), 3);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new BlockListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EntityListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new BailListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SellhandListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new ResetmineListener($this), $this);
        $this->getLogger()->info($this->colorMessage("&eChecking data compatibility..."));
        if (version_compare($oldVersion, $this->getDescription()->getVersion(), ">=") && $this->updateSettings($oldVersion) !== false) {
            $this->getLogger()->info($this->colorMessage("All data is compatible to this version!"));
        }
        $this->getLogger()->info($this->colorMessage("&aLoaded Successfully!"));
        /*if ($this->getConfig()->get("scheduled-update-checker") !== false) {
            $this->getLogger()->info($this->colorMessage("&eInitialized scheduled update checker"));
            $this->getScheduler()->scheduleRepeatingTask(new AutoUpdateChecker($this), 60 * 20 * (int)$this->getConfig()->get("scheduled-update-checker-interval"));
        } else if ($this->getConfig()->get("updater-startup-fetch") !== false) {
            $this->getLogger()->info($this->colorMessage("&eFetching latest version from repository..."));
            $this->getLogger()->info($this->colorMessage("&eResult will appear when server query is started."));
            if (Utils::getOS() == "ios" || Utils::getOS() == "android") {
                $this->getLogger()->info($this->colorMessage("&4Error: Mobile hosted servers are not supported!"));
            } else {
                if ($this->getConfig()->get("update-checker-channel") == "*") {
                    $this->getScheduler()->scheduleAsyncTask(new AsyncUpdateChecker(null, "poggit"));
                    $this->getScheduler()->scheduleAsyncTask(new AsyncUpdateChecker(null, "github"));
                } else {
                    $this->getScheduler()->scheduleAsyncTask(new AsyncUpdateChecker(null, $this->getConfig()->get("update-checker-channel")));
                }
            }
        }*/
    }

    public function onDisable()
    {
        $t = $this->data->getAll();
        foreach ($this->prisoner_time as $name => $seconds) {
            if (isset($t[$name]["seconds"]) !== false) {
                $t[$name]["seconds"] = $seconds;
                unset($this->prisoner_time[$name]);
            }
        }
        $this->data->setAll($t);
        $this->data->save();
    }

    /**
     * @param string $oldVersion
     * @return bool
     */
    private function updateSettings(string $oldVersion): bool
    {
        $no_update = true;
        $t = $this->data->getAll();
        $j = $this->data1->getAll();
        if (version_compare($oldVersion, "1.3.0", "<") !== false) {
            foreach (array_keys($j) as $jail) {
                if (isset($j[$jail]["allow-visit"]) !== true) {
                    $j[$jail]["allow-visit"] = $this->getConfig()->get("allow-visit");
                    if ($no_update !== false) $no_update = false;
                    $this->getLogger()->info($this->colorMessage("Updating " . $jail . "'s data"));
                }
                if (isset($j[$jail]["allow-escape-area"]) !== false) {
                    unset($j[$jail]["allow-escape-area"]);
                    $this->getLogger()->info($this->colorMessage("Updating " . $jail . "'s data"));
                }
            }
            $this->data1->setAll($j);
            $this->data1->save();
            foreach (array_keys($t) as $name) {
                if (isset($t[$name]["voteForJail"]) !== false) {
                    unset($t[$name]["voteForJail"]);
                    $t[$name]["VoteForJail"]["votes"] = 0;
                    $t[$name]["VoteForJail"]["votedBy"] = [];
                    if ($no_update !== false) $no_update = false;
                    $this->getLogger()->info($this->colorMessage("Updating " . $name . "'s data"));
                }
            }
            $this->data->setAll($t);
            $this->data->save();
        }
        return $no_update;
    }

    private function initializeLanguage()
    {
        if (file_exists($this->getDataFolder() . "messages.yml") !== false) {
            unlink($this->getDataFolder() . "messages.yml");
        }
        foreach ($this->getResources() as $resource) {
            if ($resource->isFile() and substr(($filename = $resource->getFilename()), 0, 5) === "lang_") {
                $this->lang[substr($filename, 5, -5)] = json_decode(file_get_contents($resource->getPathname()), true);
            }
        }
        $lang = $this->getConfig()->get("default-lang");
        $this->lang["def"] = (new Config($this->getDataFolder() . "messages.yml", Config::YAML, $this->lang[$lang]))->getAll();
    }

    /**
     * @return Jail
     */
    public static function getInstance(): Jail
    {
        return self::$instance;
    }

    /**
     * @return object
     */
    public function getEco()
    {
        return $this->eco;
    }

    /**
     * @return Mines
     */
    public function getMines()
    {
        return new Mines($this);
    }

    /**
     * @param string $message
     * @return string
     */
    public function colorMessage(string $message): string
    {
        $colors = [
            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "k", "l", "m", "n", "o", "r"
        ];
        $search = [];
        $replace = [];
        foreach ($colors as $code) {
            $search[] = "&" . $code;
            $replace[] = TextFormat::ESCAPE . $code;
        }

        return str_replace($search, $replace, $message);
    }

    /**
     * @param int $time
     * @return int
     */
    public function convertTime(int $time): int
    {
        switch ($this->getConfig()->get("time-unit")) {
            case "min":
                return $time * 60;
            case "sec":
                return $time;
            case "hr":
                return $time * 60 * 60;
            default:
                return $time;
        }
    }

    /**
     * @param string $player_name
     * @return bool
     */
    public function playerProfileExists(string $player_name): bool
    {
        $t = $this->data->getAll();
        return isset($t[strtolower($player_name)]) !== false
            && isset($t[strtolower($player_name)]["jailed"]) !== false;
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
        return (min($j[$jail]["c1"]["x"],
                $j[$jail]["c2"]["x"]) <= $pos->x) && (max($j[$jail]["c1"]["x"],
                $j[$jail]["c2"]["x"]) >= $pos->x) && (min($j[$jail]["c1"]["y"],
                $j[$jail]["c2"]["y"]) <= $pos->y) && (max($j[$jail]["c1"]["y"],
                $j[$jail]["c2"]["y"]) >= $pos->y) && (min($j[$jail]["c1"]["z"],
                $j[$jail]["c2"]["z"]) <= $pos->z) && (max($j[$jail]["c1"]["z"],
                $j[$jail]["c2"]["z"]) >= $pos->z) && ($j[$jail]["pos"]["level"] == $pos->getLevel()->getName());
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function hasAreaSelected(Player $player): bool
    {
        return array_key_exists(strtolower($player->getName()), $this->c1_tmp) &&
            array_key_exists(strtolower($player->getName()), $this->c2_tmp);
    }

    /**
     * @return array
     */
    public function getAllJailedPlayerNames(): array
    {
        $t = $this->data->getAll();
        $jailed = [];
        foreach ($t as $p => $value) {
            if (isset($t[$p]["jailed"]) !== false && $t[$p]["jailed"] !== false) {
                $jailed[] = $p;
            }
        }
        return $jailed;
    }

    /**
     * @param Player $player
     * @param string $jail_name
     * @param int $time
     * @param string $reason
     * @return bool
     */
    public function jail(Player $player, string $jail_name, int $time = -1, string $reason = "no reason"): bool
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
        //If time is infinite, then 'seconds' key will not be set.
        if ($time != -1) {
            $t[strtolower($player->getName())]["seconds"] = $this->convertTime($time);
            $this->prisoner_time[strtolower($player->getName())] = $this->convertTime($time);
        }
        $t[strtolower($player->getName())]["reason"] = $reason;
        $t[strtolower($player->getName())]["gamemode"] = $player->getGamemode();
        $t[strtolower($player->getName())]["inventory"] = $player->getInventory()->getContents();
        $this->data->setAll($t);
        $this->data->save();
        $player->setGamemode(0);
        $player->getInventory()->clearAll();
        $player->getInventory()->setItemInHand(Item::get(274));
        $player->sendMessage(
            str_replace("%time%", ($time != -1 ? $time : "infinite"),
                str_replace("%reason%", $reason, $this->getMessage("jail.success.prisoner"))));
        $player->teleport(
            new Position(
                $j[$jail_name]["pos"]["x"],
                $j[$jail_name]["pos"]["y"],
                $j[$jail_name]["pos"]["z"],
                $this->getServer()->getLevelByName($j[$jail_name]["pos"]["level"])));
        $this->getLogger()->info($this->colorMessage("&6Jailed player " . strtolower($player->getName()) . " for " . ($time == -1 ? "infinite time" : ($time > 1 ? $time . " " . $this->getConfig()->get("time-unit") . "s" : $time . " " . $this->getConfig()->get("time-unit"))) . "\nReason: " . $reason));
        return true;
    }

    /**
     * @param string $player_name
     * @return bool
     */
    public function isJailTimeInfinite(string $player_name): bool
    {
        $t = $this->data->getAll();
        if ($this->isJailed(strtolower($player_name)) !== true) {
            return false;
        }
        return isset($this->prisoner_time[strtolower($player_name)]) !== true;
    }

    /**
     * @param string $player_name
     * @return bool
     */
    public function unjail(string $player_name): bool
    {
        $t = $this->data->getAll();
        if ($this->isJailed(strtolower($player_name)) !== true) {
            return false;
        }
        $gm = $t[strtolower($player_name)]["gamemode"];
        $contents = $t[strtolower($player_name)]["inventory"];
        $t[strtolower($player_name)]["jailed"] = false;
        unset($t[strtolower($player_name)]["jail"]);
        if ($this->isJailTimeInfinite(strtolower($player_name)) !== true) {
            unset($t[strtolower($player_name)]["seconds"]);
            unset($this->prisoner_time[strtolower($player_name)]);
        }
        unset($t[strtolower($player_name)]["reason"]);
        unset($t[strtolower($player_name)]["gamemode"]);
        unset($t[strtolower($player_name)]["inventory"]);
        $this->data->setAll($t);
        $this->data->save();
        $player = $this->getServer()->getPlayer($player_name);
        if ($player !== null) {
            $player->setGamemode($gm);
            //The spawn location can be changed by executing '/setspawn' command in EssentialsPE
            $player->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
            $player->getInventory()->clearAll();
            $player->getInventory()->setContents($contents);
            $player->sendMessage($this->getMessage("unjail.you.success"));
        } else {
            $t[strtolower($player_name)]["unjailedSettings"]["gm"] = $gm;
            $t[strtolower($player_name)]["unjailedSettings"]["inv"] = $contents;
            $this->data->setAll($t);
            $this->data->save();
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
        if ($this->playerProfileExists(strtolower($player_name))) {
            return (bool)$t[strtolower($player_name)]["jailed"];
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
     * @param bool $visit
     * @param bool $escape
     */
    public function setJail(string $jail_name, Position $pos, Position $c1, Position $c2, bool $bail = true, bool $visit = true)
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
        $j[$jail_name]["allow-visit"] = $visit;
        $j[$jail_name]["mine"]["isSet"] = false;
        $this->data1->setAll($j);
        $this->data1->save();
    }

    /**
     * @param string $jail_name
     * @return bool
     */
    public function delJail(string $jail_name): bool
    {
        $t = $this->data->getAll();
        $j = $this->data1->getAll();
        if ($this->jailExists($jail_name) !== true) {
            return false;
        }
        unset($j[$jail_name]);
        $this->data1->setAll($j);
        $this->data1->save();
        if (count(array_keys($j)) <= 0) {
            foreach (array_keys($t) as $player) {
                if ($t[strtolower($player)]["jailed"] !== false) {
                    if ($t[strtolower($player)]["jail"] == $jail_name) {
                        $this->unjail($player);
                    }
                }
            }
        } else {
            foreach (array_keys($t) as $player) {
                if ($t[strtolower($player)]["jailed"] !== false) {
                    if ($t[strtolower($player)]["jail"] == $jail_name) {
                        $t[strtolower($player)]["jail"] = (string)array_rand(array_keys($j));
                    }
                }
            }
            $this->data->setAll($t);
            $this->data->save();
        }
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
        return implode(", ", $jails);
    }

    /**
     * @param Player $player
     * @param string $jail
     * @return bool
     */
    public function tpJail(Player $player, string $jail): bool
    {
        $j = $this->data1->getAll();
        if ($this->isJailed(strtolower($player->getName())) !== false) {
            return false;
        }
        if ($this->jailExists($jail) !== true) {
            return false;
        }
        $player->teleport(new Position(
            $j[$jail]["pos"]["x"],
            $j[$jail]["pos"]["y"],
            $j[$jail]["pos"]["z"],
            $this->getServer()->getLevelByName($j[$jail]["pos"]["level"])));
        return true;
    }

    /**
     * @param string $player_name
     * @param int $time
     * @return bool
     */
    public function applyPenalty(string $player_name, int $time = 10): bool
    {
        $t = $this->data->getAll();
        if ($this->isJailed(strtolower($player_name)) !== true) {
            return false;
        }
        $this->prisoner_time[strtolower($player_name)] = $this->prisoner_time[strtolower($player_name)] + $this->convertTime($time);
        $this->data->setAll($t);
        $this->data->save();
        return true;
    }

    /**
     * @param string $player_name
     * @param string $voter
     * @return bool
     */
    public function voteForJail(string $player_name, string $voter): bool
    {
        $t = $this->data->getAll();
        if ($this->playerProfileExists(strtolower($player_name)) !== true) {
            return false;
        }
        if (in_array($voter, $t[strtolower($player_name)]["VoteForJail"]["votedBy"]) !== false) {
            return false;
        }
        $t[strtolower($player_name)]["VoteForJail"]["votes"] = $t[strtolower($player_name)]["VoteForJail"]["votes"] + 1;
        $t[strtolower($player_name)]["VoteForJail"]["votedBy"][] = $voter;
        $this->data->setAll($t);
        $this->data->save();
        if ($this->getServer()->getPlayer(strtolower($player_name)) === null) {
            return false;
        }
        $player = $this->getServer()->getPlayer($player_name);
        $player->sendMessage(str_replace("%votes%", $t[strtolower($player_name)]["VoteForJail"]["votes"], str_replace("%max%", $this->getConfig()->get("votes-to-jail-player"), $this->getMessage("vote.target.voteAdded"))));
        if ($t[strtolower($player_name)]["VoteForJail"]["votes"] >= $this->getConfig()->get("votes-to-jail-player")) {
            $this->jail($player, array_rand(array_keys($this->data1->getAll())), 45, "Jailed automatically due to enough votes");
            $t[strtolower($player_name)]["VoteForJail"]["votes"] = 0;
            $t[strtolower($player_name)]["VoteForJail"]["votedBy"] = [];
            $this->data->setAll($t);
            $this->data->save();
            $this->getServer()->broadcastMessage(str_replace("%player%", $player_name, $this->getMessage("votes.enough.jail.broadcast")));
        }
        return true;
    }

    /**
     * @param string $player_name
     * @return int
     */
    public function getVotesNumber(string $player_name): int
    {
        $t = $this->data->getAll();
        if ($this->playerProfileExists(strtolower($player_name)) !== true) {
            //This is kind of an issue. We wouldn't know if the player has 0 votes or the player hasn't joined the server before.
            return 0;
        }
        return $t[strtolower($player_name)]["VoteForJail"]["votes"] ?? 0;
    }

    /**
     * @param string $player_name
     * @param string $devoter
     * @return bool
     */
    public function unvotePlayer(string $player_name, string $devoter): bool
    {
        $t = $this->data->getAll();
        if ($this->playerProfileExists(strtolower($player_name)) !== true) {
            return false;
        }
        if (in_array($devoter, $t[strtolower($player_name)]["VoteForJail"]["votedBy"]) !== true) {
            return false;
        }
        $t[strtolower($player_name)]["VoteForJail"]["votes"] = $t[strtolower($player_name)]["VoteForJail"]["votes"] - 1;
        unset($t[strtolower($player_name)]["VoteForJail"]["votedBy"][array_search(strtolower($devoter), $t[strtolower($player_name)]["VoteForJail"]["votedBy"])]);
        $this->data->setAll($t);
        $this->data->save();
        return true;
    }

    /*
     * ####################
     *       DATABASE
     * ####################
     */

    /*private function initializeDatabase(): bool
    {
        $cfg = $this->getConfig();
        $host = $cfg->get("host");
        $username = $cfg->get("username");
        $password = $cfg->get("password");
        $databaseName = $cfg->get("databaseName");
        $connection = new \mysqli($host, $username, $password, $databaseName);
        if ($connection->connect_error) {
            $this->getLogger()->info("SQL Server connection error: " . $connection->connect_error);
            return false;
        }
        $this->connection = $connection;
        $this->getLogger()->info("SQL server connection detected!");
        return true;
    }*/

}


