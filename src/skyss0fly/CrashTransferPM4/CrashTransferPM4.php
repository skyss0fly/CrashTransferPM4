<?php


namespace skyss0fly\CrashTransferPM4;

use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

/**
 * Class CrashTransfer
 * @package skyss0fly\CrashTransferPM4
 */
class CrashTransferPM4 extends PluginBase implements Listener {
    
    public static $settings;

    public function onLoad(): void
    {
        $this-> Customconfig = new Config(
            $this->getFile() . "src/lang/Language" . $this->getConfig()->get("en") . ".yml"
        );
    }
    
    public function onEnable():void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        self::$settings = $this->config->getAll();
        if(!is_numeric(self::$settings["Server"]["Port"])){
            $this->getLogger()->critical($this->getMessage("error.serveripconfig"));
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        $this->getLogger()->info($this->getMessage("general.transferingplayers") . self::$settings["Server"]["Address"] . ":" . self::$settings["Server"]["Port"]);
    }
    
    public function onDisable(): void
    {
        if($this->getServer()->isRunning()) return;
        $players = $this->getServer()->getOnlinePlayers();
        if(sizeof($players) === 0) return;
        if(!self::$settings["Warning"]["Enabled"] || self::$settings["Warning"]["Delay"] <= 0){
            $this->transferPlayers($players);
            return;
        }
        for($i = self::$settings["Warning"]["Delay"]; $i >= 0; $i--){
            if($i === 0){
                $this->transferPlayers($players);
                return;
            }
            foreach($players as $player){
                if(!$player instanceof Player) continue;
                $player->sendMessage(str_replace("{seconds-left}", $i, CrashTransferPM4::$settings["Warning"]["Message"]));
            }
            sleep(1);
        }
    }
    
    /**
     * @param array $players
     */
    public function transferPlayers(array $players){
        $this->getLogger()->info($this->getMessage("general.transferinginprogress1"));
        foreach($players as $player){
            if(!$player instanceof Player) continue;
            $player->transfer(self::$settings["Server"]["Address"], self::$settings["Server"]["Port"]);
            $this->getLogger()->info($this->getMessage("general.transferinginprogress2") . $player->getName());
        }
        $this->getLogger()->info($this->getMessage("general.transferedplayers"));
    }

    public function getMessage(string $key, array $replaces = array()): string {
        if($rawMessage = $this->messages->getNested($key)) {
            if(is_array($replaces)) {
                foreach($replaces as $replace => $value) {
                    $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
                }
            }

            return $rawMessage;
        }

        return $key;
    }

}
