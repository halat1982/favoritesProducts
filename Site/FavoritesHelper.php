<?php

namespace Site;

class FavoritesHelper implements FavoritesConstants
{

    private $user;    

    public function __construct()
    {
        $this->user = new BitrixUser();
    }

    public function getFavoritesIDs()
    {
        $favoritesIDs = Array();
        if (isset($_COOKIE[self::COOKIES_NAME])) {
            $strCookie = $_COOKIE[self::COOKIES_NAME];
            $arrCookies = unserialize($strCookie);
            foreach($arrCookies as $cookie){
                $favoritesIDs[] = $cookie;
            }
        }

        if($this->user->IsAuthorized()){
            if(!empty($this->user->fields[self::USER_FAVORITE_NAME])){
                $favoritesIDs = array_unique(array_merge($favoritesIDs, $this->user->fields[self::USER_FAVORITE_NAME]));
            }
        }

        return $favoritesIDs;
    }
}