<?php

declare(strict_types=1);

namespace locm\lobby\form;

use jojoe77777\FormAPI\SimpleForm;
use locm\lobby\util\Utils;
use pocketmine\player\Player;

class InfomationForm extends SimpleForm {

    private string $address;

    public function __construct(string $address){
        parent::__construct($this->getCallable());
        $this->address = $address;
        $this->setTitle("§l§b§lＴＥＬＥＰＯＲＴ");
        $this->setContent(Utils::parseContentFromAddress($address) ?? "");
        $this->addButton("§l§f●§0 Dịch chuyển §f●");
    }

    public function getCallable(): ?callable{
        return function (Player $player, ?int $data) :void{
            if(is_null($data)) return;
            if($data == 0) {
                Utils::teleport($player, $this->address);
            }
        };
    }
}