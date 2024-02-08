<?php

declare(strict_types=1);

namespace locm\lobby;

use locm\lobby\entity\Npc;
use locm\lobby\form\ServersForm;
use locm\lobby\util\Utils;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerTransferEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\player\Player;
use pocketmine\Server;

class EventHandler implements Listener {

    public function onMove(PlayerMoveEvent $event) :void {
        $forwardboost = 3;
        $highboost = 1;
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $subtract = $player->getLocation()->floor()->subtract(0, 1, 0);
        if($world->getBlock($subtract)->getTypeId() == BlockTypeIds::GOLD) {
            switch ($player->getHorizontalFacing()) {
                case Facing::NORTH:
                    $player->setMotion(new Vector3(0, $highboost, -($forwardboost)));
                    break;
                case Facing::EAST:
                    $player->setMotion(new Vector3(-($forwardboost), $highboost, 0));
                    break;
                case Facing::SOUTH:
                    $player->setMotion(new Vector3(0, $highboost, $forwardboost));
                    break;
                case Facing::WEST:
                    $player->setMotion(new Vector3($forwardboost, $highboost, 0));
                    break;
            }
        }
        $maxDistance = 1.5;

        if ($event->getFrom()->distance($event->getTo()) < 0.1) {
            return;
        }
        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($maxDistance, $maxDistance, $maxDistance), $player) as $entity) {
            if ($entity instanceof Npc) {
                $angle = atan2($player->getLocation()->z - $entity->getLocation()->z, $player->getLocation()->x - $entity->getLocation()->x);
                $yaw = (($angle * 180) / M_PI) - 90;
                $angle = atan2((new Vector2($entity->getLocation()->x, $entity->getLocation()->z))->distance(new Vector2($player->getLocation()->x, $player->getLocation()->z)), $player->getLocation()->y - $entity->getLocation()->y);
                $pitch = (($angle * 180) / M_PI) - 90;
                $pk = new MovePlayerPacket();
                $pk->actorRuntimeId = $entity->getId();
                $pk->position = $entity->getLocation()->add(0, $entity->getEyeHeight(), 0);
                $pk->yaw = $yaw;
                $pk->pitch = $pitch;
                $pk->headYaw = $yaw;
                $pk->onGround = $entity->onGround;
                $player->getNetworkSession()->sendDataPacket($pk);
            }
        }
    }

    public function onInteractAir(PlayerInteractEvent $event) : void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($item->getTypeId() == ItemTypeIds::COMPASS) {
            $player->sendForm(new ServersForm());
            $event->cancel();
        }
    }



    public function onDamage(EntityDamageEvent $event){
        $event->cancel();
    }

    public function onExhaust(PlayerExhaustEvent $event){
        $event->cancel();
    }

    public function onDrop(PlayerDropItemEvent $event){
        $event->cancel();
    }

    public function onBreak(BlockBreakEvent $event){
        if(!$this->isOp($event->getPlayer())) $event->cancel();
    }

    public function onPlace(BlockPlaceEvent $event){
        if(!$this->isOp($event->getPlayer())) $event->cancel();
    }

    public function onJoin(PlayerJoinEvent $event) :void {
        $player = $event->getPlayer();
        $event->setJoinMessage("§l§a●§f " . $player->getName());
        if(!$player->hasPlayedBefore()) {
            Utils::setJoinItem($player);
        }
        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
    }

    public function onQuit(PlayerQuitEvent $event) :void {
        $player = $event->getPlayer();
        $event->setQuitMessage("§l§c●§f " . $player->getName());
    }

    public function onTransfer(PlayerTransferEvent $event) :void {
        $player = $event->getPlayer();
        $address = $event->getAddress();
        $port = $event->getPort();
        $name = Utils::parseNameFromAddress($address, $port);
        Server::getInstance()->broadcastMessage("§l§e" . $player->getName() . "§f đang dịch chuyển sang §l§e" . $name);
    }

    public function onClick(PlayerInteractEvent $event) :void {
        $block = $event->getBlock();
        if($block->getTypeId() == BlockTypeIds::STONE_BUTTON) {
            $button = LobbyCore::getInstance()->getButtonPage();
            $button->handleButton($block->getPosition());
            Server::getInstance()->broadcastMessage($event->getBlock()->getPosition()->__toString());
        }
    }

    private function isOp(Player $player) :bool {
        return Server::getInstance()->isOp($player->getName());
    }

    public function onQuery(QueryRegenerateEvent $event) :void {
        $event->getQueryInfo()->setPlayerCount(LobbyCore::$cacheMem);
    }

}