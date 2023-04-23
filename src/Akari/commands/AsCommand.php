<?php

namespace Akari\commands;

use Akari\AdvancedScoreboard;
use Akari\utils\scoreboard;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AsCommand extends Command{

    protected AdvancedScoreboard $plugin;

    public function __construct(AdvancedScoreboard $plugin){
        $this->plugin = $plugin;
        parent::__construct("as", "As Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if ($sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Use this Command in-game.");
        }
        $form = new scoreboard($this->plugin);
        $form->AsForm($sender);
    }
}
