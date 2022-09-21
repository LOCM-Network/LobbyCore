<?php

declare(strict_types=1);

namespace locm\lobby\entity;


use locm\lobby\form\InfomationForm;
use locm\lobby\util\Utils;
use mmm545\libgamespyquery\GameSpyQuery;
use mmm545\libgamespyquery\GameSpyQueryException;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector2;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;

class Npc extends Human {

    private string $address;

    private string $baseNameTag;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
        parent::__construct($location, $skin, $nbt);
    }

    public function initEntity(CompoundTag $nbt): void{
        parent::initEntity($nbt);
        $this->setNameTagAlwaysVisible();
        $this->address = $nbt->getString("address");
        $this->baseNameTag = $nbt->getString("name");
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setString("name", $this->baseNameTag);
        $nbt->setString("address", $this->address);
        return $nbt;
    }

    public function getAddress() :string {
        return $this->address;
    }

    public function getBaseNameTag() :string {
        return $this->baseNameTag;
    }

    public function entityBaseTick(int $tickDiff = 1): bool{
        if ($this->isClosed()) {
            return false;
        }

        if(Server::getInstance()->getTick() % 200 == 0) {
            $this->updateQuery();
        }
        return parent::entityBaseTick($tickDiff);
    }

    public function attack(EntityDamageEvent $source): void {
        parent::attack($source);
        if($source instanceof EntityDamageByEntityEvent) {
            $damger = $source->getDamager();
            if($damger instanceof Player) {
                $damger->sendForm(new InfomationForm($this->getAddress()));
                if($damger->isSneaking() && Server::getInstance()->isOp($damger->getName())) {
                    if($damger->getInventory()->getItemInHand()->getId() == ItemIds::STICK) {
                        $this->flagForDespawn();
                        return;
                    }
                }
            }
        }
        $source->cancel();
    }

    public function updateQuery() :void {
        $address = explode(":", $this->address);
        try {
            $query = GameSpyQuery::query($address[0], (int)$address[1]);
        }catch (GameSpyQueryException $e){
            $this->setNameTag(Utils::parseBigFont($this->getBaseNameTag()) . "\n§cServer is offline");
            return;
        }
        $playerCount = 0;
        $currentPlayers = $query->get("players");
        if(isset($currentPlayers[0]) && $currentPlayers[0] != "") {
            $playerCount = count($currentPlayers);
        }
        $this->setNameTag(Utils::parseBigFont($this->getBaseNameTag()) . "\n§l§7【§e " . $playerCount . " §7/ §e" . $query->get("maxplayers") . " §7】");
    }
}