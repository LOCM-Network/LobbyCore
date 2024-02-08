<?php

declare(strict_types=1);

namespace locm\lobby\server;

use alemiz\sga\StarGateAtlantis;
use Generator;
use locm\lobby\entity\Npc;
use locm\lobby\LobbyCore;
use locm\lobby\util\Utils;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class Server {

    private string $name;
    private string $address;
    private int $port;

    private string $description;

    private bool $isOnline;

    private int $onlinePlayers;
    private int $maxPlayers;

    private bool $isSupportWaterDog;

    public function __construct(string $name, string $address, int $port, string $description, bool $isSupportWaterDog) {
        $this->name = $name;
        $this->address = $address;
        $this->port = $port;
        $this->description = $description;
        $this->isSupportWaterDog = $isSupportWaterDog;
    }

    public function getName() : string {
        return $this->name;
    }

    public function getAddress() : string {
        return $this->address;
    }

    public function getPort() : int {
        return $this->port;
    }

    public function getDescription() : string {
        return $this->description;
    }

    public function isOnline() : bool {
        return $this->isOnline;
    }

    public function isSupportWaterDog() : bool {
        return $this->isSupportWaterDog;
    }

    public function getOnlinePlayers() : int {
        return $this->onlinePlayers;
    }

    public function getMaxPlayers() : int {
        return $this->maxPlayers;
    }

    public function fetchAsync() : Generator {
        $data = yield from Utils::getServerPlayers($this->address, $this->port);
        $mainServer = LobbyCore::getInstance()->getServer();
        if($data === null) {
            $this->isOnline = false;
            $this->onlinePlayers = 0;
            $this->maxPlayers = 0;
            $mainServer->getLogger()->info("Failed to fetch data from " . $this->address . ":" . $this->port);
            return;
        }
        $this->isOnline = true;
        $this->onlinePlayers = $data["online"];
        $this->maxPlayers = $data["max"];
        $mainServer->getLogger()->info("Fetched data from " . $this->address . ":" . $this->port);
    }

    public function transfer(Player $player) : void {
        if($this->isSupportWaterDog) {
            StarGateAtlantis::getInstance()->transferPlayer($player, $this->getName());
        }else{
            $player->transfer($this->getAddress(), $this->getPort());
        }
    }

    public function getNPC(Player $player) :Npc {
        $nbt = CompoundTag::create();
        $nbt->setString("serverName", $this->getName());

        $npc = new Npc(
            $player->getLocation(),
            $player->getSkin(),
            $nbt
        );
        return $npc;
    }

    public static function fromArray(array $data) : self {
        return new self(
            $data["name"],
            $data["address"],
            $data["port"],
            $data["description"],
            $data["isSupportWaterDog"]
        );
    }
}