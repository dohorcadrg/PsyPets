<?php
class FireplaceAddOn
{
    private $_data;
    private $user;

    private function __construct($data, $owner)
    {
        $this->_data = $data;
        $this->user = $owner;
    }

    public static function SelectForUser($user)
    {
        $data = fetch_single('SELECT * FROM psypets_fireplaces WHERE userid=' . $user->ID() . ' LIMIT 1');

        if($data)
            return new FirePlaceAddOn($data, $user);
        else
            return null;
    }

}