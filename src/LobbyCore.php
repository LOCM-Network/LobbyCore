<?php

declare(strict_types=1);

namespace locm\lobby;

use locm\lobby\command\Servers;
use locm\lobby\item\Compass;
use locm\lobby\module\Button;
use locm\lobby\task\LobbyTask;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class LobbyCore extends PluginBase {
    use SingletonTrait;

    public static int $cacheMem = 0;

    private Button $button;

    public function onLoad() :void{
        self::setInstance($this);
    }

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("lobbycore", new Servers());
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
        ItemFactory::getInstance()->register(new Compass(), true);
        $this->button = new Button();
        $this->button->init();
        $this->getServer()->getNetwork()->setName("§l§eＬＯＣＭ§r");
        $this->getScheduler()->scheduleRepeatingTask(new LobbyTask(), 20 * 30);
    }

    public function getButtonPage() :Button {
        return $this->button;
    }

}