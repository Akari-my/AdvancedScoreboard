<?php

namespace Akari\event;

use Akari\AdvancedScoreboard;
use Akari\utils\scoreboard;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityLevelChangeEvent;

class LevelChangeEvent implements Listener{

    /** @var AdvancedScoreboard $plugin */
    private $plugin;

    /**
     * @param AdvancedScoreboard $plugin
     */
    public function __construct($plugin){
        $this->plugin = $plugin;
    }

    public function onChange(EntityLevelChangeEvent $event) {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            $setscore = new scoreboard($this->plugin);
            $setscore->removeScore($player);
        }
    }
}
