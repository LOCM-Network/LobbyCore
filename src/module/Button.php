<?php

declare(strict_types=1);

namespace locm\lobby\module;


use cosmicpe\floatingtext\Loader;
use czechpmdevs\multiworld\util\WorldUtils;
use locm\lobby\LobbyCore;
use pocketmine\world\Position;

class Button {

    private CONST PAGE_ID = "1";

    private string $nextButton = "";
    private string $previousButton = "";

    private int $currentPage;

    private array $pages = [
        ["§eＬＥＧＥＮＤ ＯＦ ＣＲＡＦＴ ＭＡＳＴＥＲ", "§fĐược ra đời vào nửa thập kỉ trước qua bao nhiêu lần thử nghiệm và vấp ngã", "§fLOCM đang từng bước chuyển mình", "§fvươn lên là một trong những cụm máy chủ hàng đầu Việt Nam."],
        ["§eＬＥＧＥＮＤ ＯＦ ＣＲＡＦＴ ＭＡＳＴＥＲ", "§fLOCM vinh dự là một trong những máy chủ của Việt Nam", "§fđược góp mặt trong TOP 36 máy chủ trên toàn thế giới."],
        ["§eＬＥＧＥＮＤ ＯＦ ＣＲＡＦＴ ＭＡＳＴＥＲ", "§fTheo dữ liệu thống kê được", "đã có khoảng 15.000 tài khoản XBOX ", "§ftham gia trải nghiệm các Gameplay của cụm máy chủ LOCM."],
        ["§eＬＥＧＥＮＤ ＯＦ ＣＲＡＦＴ ＭＡＳＴＥＲ", "§fLOCM tự tin là một trong những máy chủ hàng đầu", "§ftại Việt Nam chú trọng vào việc phát triển cộng đồng ", "§fvà quảng bá trên các nền tảng mạng."],
        ["§eＬＥＧＥＮＤ ＯＦ ＣＲＡＦＴ ＭＡＳＴＥＲ", "§fLOCM hiện đang sở hữu 5 máy chủ con khác nhau ", "§fvới Gameplay đa dạng và vẫn đang phát triển", "§fnhằm phục vụ nhu cầu của người chơi."],
    ];

    public function getPages() :array {
        return $this->pages;
    }

    public function getNextButton() :string {
        return $this->nextButton;
    }

    public function getPreviousButton() :string {
        return $this->previousButton;
    }

    public function setNextButton(Position $position) :void {
        $this->nextButton = $position->__toString();
    }

    public function setPreviousButton(Position $position) :void {
        $this->previousButton = $position->__toString();
    }

    public function getCurrentPage() :int {
        return $this->currentPage;
    }

    public function getPage(int $page) :string {
        return $this->pages[$page];
    }

    public function init() :void {
        $this->currentPage = 0;
        $this->parseButton();
    }

    public function getNextPage() :array {
        $nextIDpage = $this->currentPage + 1;
        if($nextIDpage >= count($this->pages)) {
            $nextIDpage = 0;
        }
        $page = $this->pages[$nextIDpage];
        $this->currentPage = $nextIDpage;
        return $page;
    }

    public function getPreviousPage() :array {
        $previousIDpage = $this->currentPage - 1;
        if($previousIDpage < 0) {
            $previousIDpage = count($this->pages) - 1;
        }
        $page = $this->pages[$previousIDpage];
        $this->currentPage = $previousIDpage;
        return $page;
    }

    public function nextPage(string $idPage) :void {
        $contents = $this->getNextPage();
        $this->appendPage($contents);
    }

    public function previousPage(string $idPage) :void {
        $contents = $this->getPreviousPage();
        $this->appendPage($contents);
    }

    public function appendPage(array $content) :void {
        $world = WorldUtils::getLoadedWorldByName("lobbytong");
        $wm = Loader::getInstance()->getWorldManager()->get($world);
        $text = $wm->getText(3);
        if($text == null) return;
        $content = implode("\n", $content);
        $text->setLine($content);
        $wm->update(3, $text);
    }

    public function handleNext(string $idPage) :void {
        $this->nextPage($idPage);
    }

    public function handlePrevious(string $idPage) :void {
        $this->previousPage($idPage);
    }

    public function handleButton(Position $position) :void {
        $stringPosition = $position->__toString();
        if($stringPosition == $this->nextButton) {
            $this->handleNext(self::PAGE_ID);
        } else if($stringPosition == $this->previousButton) {
            $this->handlePrevious(self::PAGE_ID);
        }
    }

    public function save() :void {
        LobbyCore::getInstance()->getConfig()->set("button", [
            "next" => $this->nextButton,
            "previous" => $this->previousButton,
        ]);
    }

    public function parseButton() :void {
        $button = LobbyCore::getInstance()->getConfig()->get("button");
        $this->nextButton = $button["next"];
        $this->previousButton = $button["previous"];
    }

}