<?php

declare(strict_types=1);

namespace locm\lobby\entity;


use faz\common\form\FastForm;
use locm\lobby\form\InformationForm;
use locm\lobby\LobbyCore;
use locm\lobby\server\Server;
use locm\lobby\server\ServerList;
use locm\lobby\util\Utils;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Npc extends Human {

    private string $serverName;
    private ?Server $lServer;

    private array $communicates = [
        "&l&fXin chào!!!!",
        "&l&fBạn muốn tham gia máy chủ nào?",
        "&l&fBạn ấn vào tôi để xem thông tin máy chủ nhé!!",
        "&l&fTôi buồn quá, bạn chơi tôi đi :(",
        "&l&fTôi là NPC đây, bạn cần gì không?",
    ];

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
        parent::__construct($location, $skin, $nbt);
    }

    public function initEntity(CompoundTag $nbt): void{
        parent::initEntity($nbt);
        $this->setNameTagAlwaysVisible();
        $this->serverName = $nbt->getString("serverName");
        $this->lServer = ServerList::get($this->serverName);
        $this->setScale(2);
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setString("serverName", $this->serverName);
        return $nbt;
    }

    public function getServerName() :string {
        return $this->serverName;
    }

    public function entityBaseTick(int $tickDiff = 1): bool{
        if ($this->isClosed()) {
            return false;
        }
        $mainServer = LobbyCore::getInstance()->getServer();
        if($mainServer->getTick() % 200 == 0) {
            $this->updateQuery();
        }

        foreach($this->getWorld()->getEntities() as $entity) {
            if($entity instanceof Player) {
                if($entity->getPosition()->distance($this->getPosition()) <= 5) {
                    $this->lookAt($entity->getPosition());
                    if($mainServer->getTick() % 30 == 0) {
                        $message = $this->communicates[array_rand($this->communicates)];
                        $entity->sendPopup(TextFormat::colorize("&l&f[&e" . Utils::parseBigFont($this->getServerName())  . "&f]\n" . $message));
                    }
                }
            }
        }

        return parent::entityBaseTick($tickDiff);
    }

    public function attack(EntityDamageEvent $source): void {
        parent::attack($source);
        if($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if($damager instanceof Player) {
                if($damager->isSneaking() && LobbyCore::getInstance()->getServer()->isOp($damager->getName())) {
                    if($damager->getInventory()->getItemInHand()->getTypeId() == ItemTypeIds::STICK) {
                        FastForm::question($damager, "Question", "§l§eBạn có muốn xóa NPC này không?",
                            "Có", "Không",
                            function(bool $data) :void{
                            if($data) {
                                $this->flagForDespawn();
                            }
                        });
                        return;
                    }
                } else {
                    $damager->sendForm(new InformationForm($this->getServerName()));
                }
            }
        }
        $source->cancel();
    }

    public function updateQuery() :void {
        ServerList::requestServerList($this->getServerName(), function(Server $server) :void{
            if($server->isOnline()) {
                $playerCount = $server->getOnlinePlayers();
                $maxPlayers = $server->getMaxPlayers();
                $nameTag = "§e" . Utils::parseBigFont($this->getServerName()) . "\n§l§7【§e " . $playerCount . " §7/ §e" . $maxPlayers . " §7】";
            } else {
                $nameTag = "§e" . Utils::parseBigFont($this->getServerName()) . "\n§l§7【§c OFFLINE §7】";
            }
            $this->setNameTag($nameTag);
        });
    }
}