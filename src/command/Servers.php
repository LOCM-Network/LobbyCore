<?php

declare(strict_types=1);

namespace locm\lobby\command;

use locm\lobby\form\ServersForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Servers extends Command {

    public function __construct() {
        parent::__construct("servers", "locm list servers ", "/servers");
        $this->setPermission("locm.command.servers");
    }

    public function execute(CommandSender $sender, string $label, array $args) :bool {
        if($sender instanceof Player) {
            $sender->sendForm(new ServersForm());
        }
        return true;
    }
}