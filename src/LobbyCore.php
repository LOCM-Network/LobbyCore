<?php

declare(strict_types=1);

namespace locm\lobby;

use locm\lobby\command\Servers;
use locm\lobby\entity\Npc;
use locm\lobby\module\Button;
use locm\lobby\server\ServerList;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

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
        $this->button = new Button();
        $this->button->init();
        $this->getServer()->getNetwork()->setName("§l§eＬＯＣＭ§r");

        EntityFactory::getInstance()->register(Npc::class, function (World $world, CompoundTag $nbt) : Npc {
            return new Npc(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        }, ["NPC_Lobby"]);

        ServerList::load();
    }

    public function getButtonPage() :Button {
        return $this->button;
    }

}