<?php

namespace Akari;

use Akari\event\LevelChangeEvent;
use Akari\task\AdvancedTask;
use Akari\commands\AsCommand;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\plugin\PluginBase;

class AdvancedScoreboard extends PluginBase implements Listener{

    public const LIST = "list";

    /** @var AdvancedScoreboard */
    private static $plugin;

    public function onEnable(){
        static::$plugin = $this;

        $this->getScheduler()->scheduleRepeatingTask(new AdvancedTask($this), $this->getConfig()->get("interval-time", 20));
        $this->getServer()->getPluginManager()->registerEvents(new LevelChangeEvent($this), $this);
        $this->getServer()->getCommandMap()->register("as", new AsCommand($this));

        $this->getLogger()->info(TF::AQUA . "==========( ADVANCEDSCOREBOARD )=========");
        $this->getLogger()->info(TF::GRAY . "» Version: " . $this->getDescription()->getVersion());
        $this->getLogger()->info(TF::GRAY . "» Author: Akari_my");
        $this->getLogger()->info(TF::GRAY . "» Support: https://discord.gg/hcQCmsvE");
        $this->getLogger()->info(TF::AQUA . "==========( ADVANCEDSCOREBOARD )=========");
    }

    /**
     * @return AdvancedScoreboard
     */
    public static function getInstance() : AdvancedScoreboard{
        return static::$plugin;
    }

    public function translate(Player $player, string $message) : string{
        $message = str_replace('{PING}', $player->getPing(), $message);
        $message = str_replace('{NAME}', $player->getName(), $message);
        $message = str_replace('{IP}', $player->getAddress(), $message);
        $message = str_replace('{ITEM_ID}', $player->getInventory()->getItemInHand()->getId(), $message);
        $message = str_replace('{X}', $player->getPosition()->getFloorX(), $message);
        $message = str_replace('{Y}', $player->getPosition()->getFloorY(), $message);
        $message = str_replace('{Z}', $player->getPosition()->getFloorZ(), $message);
        $level = $player->getLevel();
        $message = str_replace('{WORLDNAME}', $level->getFolderName(), $message);
        $message = str_replace('{WORLDPLAYERS}', count($level->getPlayers()), $message);
        $message = str_replace('{TICKS}', $this->getServer()->getTickUsage(), $message);
        $message = str_replace('{TPS}', $this->getServer()->getTicksPerSecond(), $message);
        $message = str_replace('{ONLINE}', count($this->getServer()->getOnlinePlayers()), $message);
        $message = str_replace('{MAX_ONLINE}', $player->getServer()->getMaxPlayers(), $message);
        $message = str_replace("{DATE}", date("H:i a"), $message);
        $message = str_replace("{RANDOMCOLOR}", $this->getColor(), $message);
        $message = $this->reviewAllPlugins($player, $message);
        return TF::colorize((string) $message);
    }

    public function reviewAllPlugins(Player $player, string $message) : string{
        $PurePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        if (!is_null($PurePerms)) {
            $message = str_replace('{RANK}', $PurePerms->getUserDataMgr()->getGroup($player)->getName(), $message);
            $message = str_replace('{PREFIX}', $PurePerms->getUserDataMgr()->getNode($player, "prefix"), $message);
            $message = str_replace('{SUFFIX}', $PurePerms->getUserDataMgr()->getNode($player, "suffix"), $message);
        }

        $EconomyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if (!is_null($EconomyAPI)) {
            $message = str_replace('{MONEY}', $EconomyAPI->myMoney($player), $message);
        }
        $FactionsPro = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");
        $factionName = $FactionsPro->getPlayerFaction($player->getName());
        if(!is_null($FactionsPro)){
            $message = str_replace('{FACTION}', $FactionsPro->getPlayerFaction($player->getName()), $message);
            $message = str_replace('{FPOWER}', $FactionsPro->getFactionPower($factionName), $message);
        }else{
            $this->getLogger()->info(TF::DARK_PURPLE."FactionsPro not found");
            $message = str_replace('{FACTION}', "PLUGIN NOT INSTALLED", $message);
            $message = str_replace('{FPOWER}', "PLUGIN NOT INSTALLED", $message);
        }

        $Logger = $this->getServer()->getPluginManager()->getPlugin("CombatLogger");
        if (!is_null($Logger)) {
            $message = str_replace('{COMBATLOGGER}', $Logger->getTagDuration($player), $message);
        }else{
            $this->getLogger()->info(TF::DARK_PURPLE."CombatLogger not found");
            $message = str_replace('{COMBATLOGGER}', "PLUGIN NOT INSTALLED", $message);
        }

        $kdr = $this->getServer()->getPluginManager()->getPlugin("KDR");
        if (!is_null($kdr)) {
            $message = str_replace('{KDR}', $kdr->getProvider()->getKillToDeathRatio($player), $message);
            $message = str_replace('{DEATHS}', $kdr->getProvider()->getPlayerDeathPoints($player), $message);
            $message = str_replace('{KILLS}', $kdr->getProvider()->getPlayerKillPoints($player), $message);
        }else{
            $message = str_replace('{KDR}', "PLUGIN NOT INSTALLED", $message);
            $message = str_replace('{DEATHS}', "PLUGIN NOT INSTALLED", $message);
            $message = str_replace('{KILLS}', "PLUGIN NOT INSTALLED", $message);
        }

        $CPS = $this->getServer()->getPluginManager()->getPlugin("PreciseCpsCounter");
        if (!is_null($CPS)) {
            $message = str_replace('{CPS}', $CPS->getCps($player), $message);
        }else{
            $message = str_replace('{CPS}', "PLUGIN NOT INSTALLED", $message);
        }

        $RedSkyBlock = $this->getServer()->getPluginManager()->getPlugin("RedSkyBlock");
        if (!is_null($RedSkyBlock)) {
            $message = str_replace('{ISLAND_NAME}', $RedSkyBlock->getIslandName($player), $message);
            $message = str_replace('{ISLAND_MEMBERS}', $RedSkyBlock->getMembers($player), $message);
            $message = str_replace('{ISLAND_BANNED}', $RedSkyBlock->getBanned($player), $message);
            $message = str_replace('{ISLAND_LOCKED_STATUS}', $RedSkyBlock->getLockedStatus($player), $message);
            $message = str_replace('{ISLAND_SIZE}', $RedSkyBlock->getSize($player), $message);
            $message = str_replace('{ISLAND_RANK}', $RedSkyBlock->calcRank(strtolower($player->getName())), $message);
            $message = str_replace('{ISLAND_VALUE}', $RedSkyBlock->getValue($player), $message);
        }
        return $message;
    }

    /**
     * @return string
     */
    public function getColor() : string{
        $colors = [TF::DARK_BLUE, TF::DARK_GREEN, TF::DARK_AQUA, TF::DARK_RED, TF::DARK_PURPLE, TF::GOLD, TF::GRAY, TF::DARK_GRAY, TF::BLUE, TF::GREEN, TF::AQUA, TF::RED, TF::LIGHT_PURPLE, TF::YELLOW, TF::WHITE];
        return $colors[rand(0,14)];
    }
}
