<?php
class House
{
    private $_data;
    private $user;
    private $addons;
    private $inventory = false;

    private function __construct($data, $owner)
    {
        $this->_data = $data;
        $this->user = $owner;
        $this->addons = explode(',', $data['addons']);
    }

    /**
     * @param User $owner
     */
    public static function SelectForUser($owner)
    {
        $data = fetch_single('SELECT FROM monster_houses WHERE userid=' . (int)$owner->ID() . ' LIMIT 1');
        if($data)
            return new House($data, $owner);
        else
            return null;
    }
}