<?php

declare(strict_types=1);

namespace locm\lobby\item;

use locm\lobby\form\ServersForm;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Compass extends \pocketmine\item\Compass {

    public function __construct() {
        parent::__construct(new ItemIdentifier(ItemIds::COMPASS, 0), "§l§eＳｅｒｖｅｒｓ");
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{
        $player->sendForm(new ServersForm());
        return parent::onClickAir($player, $directionVector);
    }

}