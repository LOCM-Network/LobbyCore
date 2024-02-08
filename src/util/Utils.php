<?php

declare(strict_types=1);

namespace locm\lobby\util;

use faz\common\Debug;
use Generator;
use locm\lobby\LobbyCore;
use mmm545\libgamespyquery\GameSpyQuery;
use mmm545\libgamespyquery\GameSpyQueryException;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class Utils {

    /**
     * @throws GameSpyQueryException
     */
    public static function query(string $address, int $port) : array {
        return GameSpyQuery::query($address, $port);
    }

    public static function parseContentFromAddress(string $address) :?string{
        $servers = LobbyCore::getInstance()->getConfig()->get("servers");
        return $servers[$address]["content"] ?? "";
    }

    public static function parseNameFromAddress(string $address, int $port) :?string{
        $servers = LobbyCore::getInstance()->getConfig()->get("servers");
        foreach ($servers as $data) {
            if($data["address"] == $address && $data["port"] == $port) {
                return $data["name"];
            }
        }
        return null;
    }

//    public static function teleport(Player $player, string $address) :void{
//        LobbyCore::getInstance()->getScheduler()->scheduleRepeatingTask(new class($player, $address) extends Task {
//            private int $cooldown = 5;
//
//            public function __construct(
//                private Player $player,
//                private string $address
//            ){}
//
//            public function onRun(): void {
//                if($this->cooldown > 0) {
//                    $this->cooldown--;
//                    $this->player->sendTitle("§eＬＯＣＭ ＴＲＡＮＳＦＥＲ", "§fBạn sẽ được dịch chuyển trong§e " . $this->cooldown . "§f giây", 0, 20, 0);
//                    return;
//                }
//                if($this->player->isClosed()) return;
//                $player = $this->player;
//                $address = $this->address;
//                StarGateAtlantis::getInstance()->transferPlayer($player, $address);
//                $serverExplode = explode(":", $address);
//                $player->transfer($serverExplode[0], (int) $serverExplode[1]);
//                $this->getHandler()->cancel();
//            }
//        }, 20);
//    }

    public static function setJoinItem(Player $player) :void {
        $item = VanillaItems::COMPASS();
        $item->setCustomName("§l§eＳｅｒｖｅｒｓ");
        $player->getInventory()->setItem(0, $item);
    }


    public static function getAllServer() :array {
        $servers = LobbyCore::getInstance()->getConfig()->get("servers");
        $result = [];
        foreach($servers as $address => $data) {
            $result[] = $address;
        }
        return $result;
    }

    /**
     * @return array{online: int, max: int}|null
     */
    public static function getServerPlayers(string $address, int $port) :?Generator {
        return yield from Await::promise(function($resolve) use($address, $port) {
            try {
                $query = self::query($address, $port);
                Debug::spaceDump($query);
            }catch (GameSpyQueryException) {
                $resolve(null);
                return;
            }
            $currentPlayers = (int) $query["Players"];
            $maxPlayers = (int) $query["MaxPlayers"];
            $resolve([
                "online" => $currentPlayers,
                "max" => $maxPlayers
            ]);
        });
    }

    public static function getAllMemberInServers() :int {
        $servers = LobbyCore::getInstance()->getConfig()->get("servers");
        $result = 0;
        foreach($servers as $address => $data) {
            $explode = explode(":", $address);
            try {
                $query = self::query($explode[0], (int) $explode[1]);
            }catch (GameSpyQueryException $exception) {
                continue;
            }
            $playerCount = 0;
            $currentPlayers = $query["players"];
            if(isset($currentPlayers[0]) && $currentPlayers[0] != "") {
                $playerCount = count($currentPlayers);
            }
            $result += $playerCount;
        }
        return $result;
    }


    public static function is_valid_domain_name(string $domain_name) {
        return (preg_match("/([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*:(\d{1,5})/i", $domain_name) //valid chars check
            and preg_match("/.{1,253}/", $domain_name) //overall length check
            and preg_match("/[^\.]{1,63}(\.[^\.]{1,63})*/", $domain_name)); //length of each label
    }

    public static function isValidIP(string $ip) {
        return (preg_match("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}):(\d{1,5})/", $ip) !== false);
    }

    public static function parseBigFont(string $string) :string {
        $upcase = ["A" => "Ａ", "B" => "Ｂ", "C" => "Ｃ", "D" => "Ｄ", "E" => "Ｅ", "F" => "Ｆ", "G" => "Ｇ", "H" => "Ｈ", "I" => "Ｉ", "J" => "Ｊ", "K" => "Ｋ", "L" => "Ｌ", "M" => "Ｍ", "N" => "Ｎ", "O" => "Ｏ", "P" => "Ｐ", "Q" => "Ｑ", "R" => "Ｒ", "S" => "Ｓ", "T" => "Ｔ", "U" => "Ｕ", "V" => "Ｖ", "W" => "Ｗ", "X" => "Ｘ", "Y" => "Ｙ", "Z" => "Ｚ"];
        $downcase = ["a" => "ａ", "b" => "ｂ", "c" => "ｃ", "d" => "ｄ", "e" => "ｅ", "f" => "ｆ", "g" => "ｇ", "h" => "ｈ", "i" => "ｉ", "j" => "ｊ", "k" => "ｋ", "l" => "ｌ", "m" => "ｍ", "n" => "ｎ", "o" => "ｏ", "p" => "ｐ", "q" => "ｑ", "r" => "ｒ", "s" => "ｓ", "t" => "ｔ", "u" => "ｕ", "v" => "ｖ", "w" => "ｗ", "x" => "ｘ", "y" => "ｙ", "z" => "ｚ"];
        return strtr($string, $upcase + $downcase);
    }

}