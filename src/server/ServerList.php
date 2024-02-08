<?php

declare(strict_types=1);

namespace locm\lobby\server;

use Closure;
use Generator;
use locm\lobby\LobbyCore;
use pocketmine\utils\Utils;
use SOFe\AwaitGenerator\Await;

class ServerList {

    private static array $servers;

    public static function load() : void {
        $servers = LobbyCore::getInstance()->getConfig()->get("servers", []);
        foreach($servers as $data) {
            $server = new Server($data["name"], $data["address"], $data["port"], $data["description"], $data["water_dog"]);
            self::add($server);
        }
    }

    public static function add(Server $server) : void {
        self::$servers[$server->getName()] = $server;
    }

    public static function get(string $name) : ?Server {
        return self::$servers[$name] ?? null;
    }

    public static function getAll() : array {
        return self::$servers;
    }

    public static function remove(string $name) : void {
        unset(self::$servers[$name]);
    }

    public static function requestServerList(string $name, Closure $closure) : void {
        Utils::validateCallableSignature(function(bool $isOnline){}, $closure);
        $server = self::get($name);

        if($server === null) {
            $closure(false);
            return;
        }

        Await::f2c(function() use($server, $closure) : Generator {
            yield $server->fetchAsync();
            $closure($server->isOnline());
        });
    }
}