<?php

declare(strict_types=1);

namespace locm\lobby\form;

use jojoe77777\FormAPI\SimpleForm;
use locm\lobby\LobbyCore;
use locm\lobby\server\Server;
use locm\lobby\server\ServerList;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class InformationForm extends SimpleForm {

    private string $serverName;

    public function __construct(string $serverName){
        parent::__construct($this->getCallable());
        $this->serverName = $serverName;
        $this->setTitle("§l§b§lＴＥＬＥＰＯＲＴ");
        $description = ServerList::get($serverName)->getDescription() ?? "§l§cMáy chủ đang bảo trì hoặc không hoạt động, vui lòng thử lại sau!";
        $this->setContent(TextFormat::colorize($description));
        $this->addButton("§l§f●§0 Dịch chuyển §f●");
    }

    public function getCallable(): ?callable{
        return function (Player $player, ?int $data) :void{
            if(is_null($data)) return;
            if($data == 0) {
                $item = $player->getInventory()->getItemInHand();
                if($item->getTypeId() == ItemTypeIds::ARROW && LobbyCore::getInstance()->getServer()->isOp($player->getName())) {
                    $npc = ServerList::get($this->serverName)->getNpc($player);
                    $npc->spawnToAll();
                }

                ServerList::requestServerList($this->serverName,
                    function(Server $server) use ($player) :void{
                    if($server->isOnline()) {
                        $server->transfer($player);
                    } else {
                        $player->sendMessage("§l§cMáy chủ đang bảo trì hoặc không hoạt động, vui lòng thử lại sau!");
                    }
                });
            }
        };
    }
}