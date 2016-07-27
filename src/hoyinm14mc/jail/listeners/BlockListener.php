<?php
/*
 * This file is a part of UltimateParticles.
 * Copyright (C) 2016 hoyinm14mc
 *
 * UltimateParticles is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UltimateParticles is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UltimateParticles. If not, see <http://www.gnu.org/licenses/>.
 */

namespace hoyinm14mc\jail\listeners;

use hoyinm14mc\jail\base\BaseListener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\level\Position;

class BlockListener extends BaseListener{
    
    public function onBlockBreak(BlockBreakEvent $event){
        $t = $this->getPlugin()->data->getAll();
        $j = $this->getPlugin()->data1->getAll();
        $cfg = $this->getPlugin()->getConfig();
        foreach($j as $jail => $value){
            if($this->getPlugin()->insideJail($jail, new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel())) && $event->getPlayer()->isOp() !== true){
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage($this->getPlugin()->colorMessage("&b[&eJail&b] &4This area is restricted."));
            }
        }
        if($this->getPlugin()->isJailed($event->getPlayer()->getName()) !== false && $cfg->get("allow-block-break") !== true){
            $event->getPlayer()->sendMessage($this->getPlugin()->colorMessage("&cYou are not allowed to do this while being jailed!"));
            $event->setCancelled(true);
            if($cfg->get("enable-penalty") !== false && isset($t[$event->getPlayer()->getName()]["seconds"])){
                $this->getPlugin()->applyPenalty($event->getPlayer()->getName(), (int)$cfg->get("penalty-time"));
                $event->getPlayer()->sendMessage($this->getPlugin()->colorMessage("&eYou have been added ".$cfg->get("penalty-time")." as punishment!"));
            }
        }
    }
    
    public function onBlockPlace(BlockPlaceEvent $event){
        $t = $this->getPlugin()->data->getAll();
        $j = $this->getPlugin()->data1->getAll();
        $cfg = $this->getPlugin()->getConfig();
        foreach($j as $jail => $value){
            if($this->getPlugin()->insideJail($jail, new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel())) && $event->getPlayer()->isOp() !== true){
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage($this->getPlugin()->colorMessage("&b[&eJail&b] &4This area is restricted."));
                
            }
        }
        if($this->getPlugin()->isJailed($event->getPlayer()->getName()) !== false && $cfg->get("allow-block-place") !== true){
            $event->getPlayer()->sendMessage($this->getPlugin()->colorMessage("&cYou are not allowed to do this while being jailed!"));
            $event->setCancelled(true);
            if($cfg->get("enable-penalty") !== false && isset($t[$event->getPlayer()->getName()]["seconds"])){
                $this->getPlugin()->applyPenalty($event->getPlayer()->getName(), (int)$cfg->get("penalty-time"));
                $event->getPlayer()->sendMessage($this->getPlugin()->colorMessage("&eYou have been added ".$cfg->get("penalty-time")." as punishment!"));
            }
        }
    }
    
}
?>