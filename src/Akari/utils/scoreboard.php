<?php

namespace Akari\utils;

use Akari\AdvancedScoreboard;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;

class scoreboard{

    protected AdvancedScoreboard $plugin;

    public function __construct(AdvancedScoreboard $plugin){
        $this->plugin = $plugin;
    }

    /** @var string[] */
    private static $scoreboard = [];

    /**
     * @param Player $player
     * @param string $displayName
     * @param int    $sortOrder
     * @param string $displaySlot
     * @return void
     */
    public function createScore(Player $player, string $displayName, int $sortOrder = 0, string $displaySlot = "sidebar"){
        if(isset(self::$scoreboard[$player->getName()])){
            $this->removeScore($player);
        }
        $packet = new SetDisplayObjectivePacket();
        $packet->displaySlot = $displaySlot;
        $packet->objectiveName = "objective";
        $packet->displayName = $displayName;
        $packet->criteriaName = "dummy";
        $packet->sortOrder = $sortOrder;
        $player->sendDataPacket($packet);
        self::$scoreboard[$player->getName()] = $player->getName();
    }

    /**
     * @param Player $player
     * @return void
     */
    public function removeScore(Player $player) : void{
        $packet = new RemoveObjectivePacket();
        $packet->objectiveName = "objective";
        $player->sendDataPacket($packet);
        unset(self::$scoreboard[$player->getName()]);
    }

    /**
     * @param Player $player
     * @param array  $messages
     * @return void
     */
    public function setScoreLines(Player $player, array $messages, bool $translate = false) : void{
        $line = 1;
        foreach ($messages as $message) {
            $this->setScoreLine($player, $line, $message, $translate);
            $line++;
        }
    }

    /**
     * @param Player $player
     * @param int    $line
     * @param string $customName
     * @return bool
     */
    public function setScoreLine(Player $player, int $line, string $message, bool $translate = false) : void{
        if(!isset(self::$scoreboard[$player->getName()])) {
            return;
        }

        if($line <= 0 or $line > 15) {
            return;
        }

        if ($translate) {
            $message = $this->plugin->translate($player, $message);
        }

        $pkline = new ScorePacketEntry();
        $pkline->objectiveName = "objective";
        $pkline->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $pkline->customName = $message;
        $pkline->score = $line;
        $pkline->scoreboardId = $line;

        $packet = new SetScorePacket();
        $packet->type = SetScorePacket::TYPE_CHANGE;
        $packet->entries[] = $pkline;
        $player->sendDataPacket($packet);
    }

    public function AsForm(Player $player){
        $form = new SimpleForm(function (Player $player, int $data=null) {
            if ($data === null){
                return true;
            }
            switch ($data){
                case 0:
                    $this->createScore[$player->getName()] = $player->getName();
                    return true;
                    break;
                case 1:
                    $this->removeScore($player);
                    return true;
            }
            return false;
        });
        $form->setTitle(TextFormat::GOLD . "§lADVANCED SCOREBOARDS");
        $form->setContent("Select an option");
        $form->addButton(TextFormat::BOLD . "§l§aShow Scoreboard");
        $form->addButton(TextFormat::BOLD . "§l§cHide Scoreboard");
        $form->sendToPlayer($player);
    }
}