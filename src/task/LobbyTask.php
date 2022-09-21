<?php


declare(strict_types=1);

namespace locm\lobby\task;

use locm\lobby\LobbyCore;
use locm\lobby\util\Utils;
use pocketmine\scheduler\Task;
use slapper\entities\SlapperEntity;
use slapper\entities\SlapperHuman;

class LobbyTask extends Task {

    public function onRun(): void{
        $allMembers = Utils::getAllMemberInServers();
        LobbyCore::$cacheMem = $allMembers;
        $defaultWorld = LobbyCore::getInstance()->getServer()->getWorldManager()->getDefaultWorld();
        foreach($defaultWorld->getEntities() as $entity) {
            if($entity instanceof SlapperHuman) {
                $entity->updateQuery();
            }
        }
    }
}