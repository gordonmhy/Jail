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

namespace hoyinm14mc\jail;

use pocketmine\level\Position;
use pocketmine\Player;

interface JailAPI
{

    /**
     * Do this before accessing Jail externally
     * @return Jail
     */
    public static function getInstance(): Jail;

    /**
     * Allow external access to mines control
     * @return Mines
     */
    public function getMines();

    /**
     * Checks if a player's profile exist in Jail's data
     * @param string $player_name
     * @return bool
     */
    public function playerProfileExists(string $player_name): bool;

    /**
     * Checks if the player is in jail-selection-mode
     * @param Player $player
     * @return bool
     */
    public function isSelectionMode(Player $player): bool;

    /**
     * Checks if the player is positioned inside a jail
     * @param string $jail
     * @param Position $pos
     * @return bool
     */
    public function insideJail(string $jail, Position $pos): bool;

    /**
     * Checks if the player already selected two corners as an area
     * @param Player $player
     * @return bool
     */
    public function hasAreaSelected(Player $player): bool;

    /**
     * Returns an array of player names who are jailed
     * @return array
     */
    public function getAllJailedPlayerNames(): array;

    /**
     * To jail a player into a jail for a specific amount of time for a reason
     * @param Player $player
     * @param string $jail_name
     * @param int $time
     * @param string $reason
     * @return bool
     */
    public function jail(Player $player, string $jail_name, int $time = -1, string $reason = "no reason"): bool;

    /**
     * Checks if a player is jailed without time counting
     * @param string $player_name
     * @return bool
     */
    public function isJailTimeInfinite(string $player_name): bool;

    /**
     * To unjail a player, whatever jail he/she is located
     * @param string $player_name
     * @return bool
     */
    public function unjail(string $player_name): bool;

    /**
     * Checks if a player is jailed
     * @param string $player_name
     * @return bool
     */
    public function isJailed(string $player_name): bool;

    /**
     * List all jailed players, as a message
     * @return string
     */
    public function jailedToString(): string;

    /**
     * To set/create a jail.
     * The jail name could be duplicated with an existing jail, which will
     * reset that jail with new preferences
     * @param string $jail_name
     * @param Position $pos
     * @param Position $c1
     * @param Position $c2
     * @param bool $bail
     * @param bool $escape
     */
    public function setJail(string $jail_name, Position $pos, Position $c1, Position $c2, bool $bail = false, bool $escape = false);

    /**
     * To delete an existing jail
     * @param string $jail_name
     * @return bool
     */
    public function delJail(string $jail_name): bool;

    /**
     * Checks if a jail exists
     * @param string $jail_name
     * @return bool
     */
    public function jailExists(string $jail_name): bool;

    /**
     * Lists all available jails, as a message
     * @return string
     */
    public function jailsToString(): string;

    /**
     * Teleports a player into a jail.
     * The player won't be allowed to modify the jail structure
     * @param Player $player
     * @param string $jail
     * @return bool
     */
    public function tpJail(Player $player, string $jail): bool;

    /**
     * Adds a specific amount of punishmental jail-time for the player
     * as its penalty
     * @param string $player_name
     * @param int $time
     * @return bool
     */
    public function applyPenalty(string $player_name, int $time = 10): bool;

    /**
     * To let players vote a specific target player.
     * When votes exceed the limit set in the configuration file,
     * the player will be jailed for a specific amount of time
     * set in the configuration file.
     * @param string $player_name
     * @param string $voter
     * @return bool
     */
    public function voteForJail(string $player_name, string $voter): bool;

    /**
     * To unvote a player who was voted by a specific voter.
     * @param string $player_name
     * @param string $devoter
     * @return bool
     */
    public function unvotePlayer(string $player_name, string $devoter): bool;

    /**
     * Gets the number a votes a player has.
     * @param string $player_name
     * @return int
     */
    public function getVotesNumber(string $player_name): int;

}
