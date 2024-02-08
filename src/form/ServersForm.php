<?php

declare(strict_types=1);

namespace locm\lobby\form;


use jojoe77777\FormAPI\SimpleForm;
use locm\lobby\LobbyCore;
use pocketmine\player\Player;

class ServersForm extends SimpleForm {

    private array $servers;

    public function __construct() {
        parent::__construct($this->getCallable());
        $this->servers = LobbyCore::getInstance()->getConfig()->get("servers");
        $this->setTitle("§l§b§lＬＯＣＭ ＳＥＲＶＥＲＳ");
        foreach($this->servers as $server => $data) {
            $this->addButton("§l§f●§0 " . $data["name"] . " §f●");
        }
    }

    public function getCallable(): ?callable {
        return function(Player $player, ?int $data) :void {
            if(is_null($data)) return;
            $serverName = array_values($this->servers)[$data]["name"];
            $form = new InformationForm($serverName);
            $player->sendForm($form);
        };
    }
}