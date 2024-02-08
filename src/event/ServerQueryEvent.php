<?php

declare(strict_types=1);

namespace locm\lobby\event;

use locm\lobby\server\Server;
use pocketmine\event\Event;

class ServerQueryEvent extends Event {

    private Server $server;

    public function __construct(Server $server) {
        $this->server = $server;
    }

    public function getServer() : Server {
        return $this->server;
    }
}