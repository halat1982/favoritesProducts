<?php

namespace Site;

class BitrixUser extends \CUser
{

    public $user;
    private $ID;
    private $allowedUserFields = array(
        "WORK_PHONE"
    );
    public $fields = array();

    public function __construct()
    {
        global $USER;
        $this->user = $USER;
        $this->ID = $this->user->GetID();
        if ($this->user->IsAuthorized()) {
            $this->setParameters($this->user);
        }
    }

    public function setProperty($propName, $propValue)
    {
        $this->user->Update($this->ID, array($propName => $propValue));
    }

    private function setParameters($user)
    {
        $this->fields = $this->getUserRow($user);
    }

    private function getUserRow($user)
    {
        $filter = array
        (
            "ID" => $user->GetID(),
            "ACTIVE" => "Y",
        );

        $order = array('sort' => 'asc');
        $tmp = 'sort'; 
        $rsUsers = \CUser::GetList($order, $tmp, $filter, array("SELECT" => array("UF_*")));
        return $rsUsers->fetch();
    }
}