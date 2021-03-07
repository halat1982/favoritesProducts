<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context,
    Site\BitrixUser;
    Site\FavoritesConstants;

$fav = new Favorites();
$fav->execute();

class Favorites implements FavoritesConstants
{

    protected $errors = array();
    private $requestFieldsArray;
    private $user;
    private $favoritesIDs = array();
    private $action;    

    public function __construct()
    {
        $this->requestFieldsArray = Context::getCurrent()->getRequest()->getPostList()->toArray();
        $this->user = new BitrixUser();
    }

    public function execute()
    {
        $this->getProductsFromCookies();
        $this->removeCookies();
        if ($this->user->IsAuthorized()) {
            $this->userPropertyHandler();
        } else {
            $this->cookiesHandler();
        }

        $this->returnResponse();
    }

    private function returnResponse()
    {
        if (!empty($this->action)) {
            echo json_encode(["status" => "success", "action" => $this->action]);
        } else {
            array_push($this->errors, "Неизвестен статус обрабатываемого товара. Обратитесь в техподдержку");
            echo json_encode(["status" => "error", "data" => $this->errors]);
        }
        die();
    }

    private function getProductsFromCookies()
    {
        if (isset($_COOKIE[self::COOKIES_NAME])) {

                $strCookie = $_COOKIE[self::COOKIES_NAME];
                $arrCookies = unserialize($strCookie);
                foreach($arrCookies as $cookie){
                    $this->favoritesIDs[] = $cookie;
                }
        }
    }

    private function userPropertyHandler()
    {
        $this->favoritesIDs = array_unique(array_merge($this->favoritesIDs, $this->user->fields[self::USER_FIELD_NAME]));
        $this->composeProducts($this->user->fields[self::USER_FIELD_NAME]);

        $this->user->setProperty(self::USER_FIELD_NAME, $this->favoritesIDs);
    }

    private function composeProducts(array $productsID)
    {
        if (in_array($this->requestFieldsArray["product_id"], $productsID)) {
            $this->removeIdFromFavorites();
            $this->action = "remove";
        } else {
            $this->favoritesIDs[] = $this->requestFieldsArray["product_id"];
            $this->action = "add";
        }
    }

    private function removeIdFromFavorites()
    {
        foreach ($this->favoritesIDs as $k => $id) {
            if ($id == $this->requestFieldsArray["product_id"]) {
                unset($this->favoritesIDs[$k]);
            }
        }
    }

    private function cookiesHandler()
    {
        $this->composeProducts($this->favoritesIDs);
        $this->setIDsToCookies();
    }

    private function setIDsToCookies()
    {
        $cookieArray = Array();
        foreach ($this->favoritesIDs as $id) {
            $cookieArray[$id] = $id;
            $strCookies = serialize($cookieArray);
            setcookie(self::COOKIES_NAME, $strCookies, time() + 52000, '/');
        }
    }

    private function removeCookies()
    {
        if (isset($_COOKIE[self::COOKIES_NAME])) {
            setcookie(self::COOKIES_NAME, "", time() - 20, '/');
        }
    }
}

