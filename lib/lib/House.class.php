<?php
class House
{
    public static $MAX_SIZE = 50000;
    public static $MAX_HOURS = 72;

    private $_data;
    /** @var User */ private $user;
    private $addons, $rooms;

    private $materials = false;

    private function __construct(&$data, $owner)
    {
        $this->_data =& $data;
        $this->user = $owner;
        $this->addons = explode(',', $data['addons']);
        $this->rooms = explode(',', $data['rooms']);
    }

    /** @return int */ public function Hours() { return floor((time() - $this->_data['lasthour']) / (60 * 60)); }
    /** @return int */ public function Size() { return $this->_data['maxbulk']; }
    /** @return bool */ public function HasAddOn($addon) { return in_array($addon, $this->addons); }

    public function InventorySize() { return $this->_data['curbulk']; }

    public function RecalculateInventorySize()
    {
        $bulk = 0;
        $data = fetch_multiple('SELECT monster_items.bulk*COUNT(monster_inventory.idnum) AS totalBulk FROM monster_inventory LEFT JOIN monster_items ON monster_items.itemname=monster_inventory.itemname WHERE monster_inventory.user=' . quote_smart($this->user->Username()) . ' AND monster_inventory.location LIKE \'home%\' GROUP BY monster_inventory.itemname');
        foreach($data as $subTotal)
            $bulk += $subTotal['totalBulk'];

        if($bulk != $this->_data['curbulk'])
        {
            $this->_data['curbulk'] = $bulk;
            $this->Update(array('curbulk'));
        }
    }

    public function AddInventory($itemName, $location, $maker, $message, $quantity = 1)
    {
        global $now;

        if($quantity == 0)
            return;

        $item = get_item_byname($itemName);

        if($item === false)
        {
            echo "adding bulk inventory (1)<br />\n" .
                "There is no item named '$itemName'<br />\n";
            die();
        }

        $q_user = quote_smart($this->user->Username());
        $q_maker = quote_smart($maker);
        $q_itemName = quote_smart($itemName);
        $q_message = quote_smart($message);
        $q_location = quote_smart($location);

        if(substr($location, 0, 4) == 'home')
        {
            $this->_data['bulk'] += $item['bulk'] * $quantity;

            if($this->materials !== false && substr($location, 0, 6) != 'home/$')
                $this->AddMaterials($itemName, $quantity);
        }

        $item_data = "($q_user, $q_maker, $q_itemName, " . $item['durability'] . ", $q_message, $q_location, $now)";

        $command = "INSERT INTO `monster_inventory` (`user`, `creator`, `itemname`, `health`, `message`, `location`, `changed`) VALUES $item_data";
        if($quantity > 1)
            $command .= str_repeat(', ' . $item_data, $quantity - 1);

        fetch_none($command);
    }

    public function IsFull()
    {
        return $this->_data['curbulk'] >= min($this->_data['maxbulk'], House::$MAX_SIZE);
    }

    public function CapHours()
    {
        global $now;

        if($this->Hours() > self::$MAX_HOURS)
        {
            $this->_data['lasthour'] = $now - (self::$MAX_HOURS * 60 * 60);
            $this->Update(array('lasthour'));
        }
    }

    public function FirstLogIn()
    {
        global $now;

        $this->_data['lasthour'] = $now;
        $this->Update(array('lasthour'));
    }

    public function PassHours($hours = 1)
    {
        $seconds = $hours * 3600;

        fetch_none('
            UPDATE monster_houses
            SET lasthour=lasthour+' . $seconds . '
            WHERE idnum=' . $this->_data['idnum'] . '
            LIMIT 1
        ');

        $this->_data['lasthour'] += $seconds;
    }

    /**
     * @param User $owner
     * @return House|null
     */
    public static function SelectForUser($owner)
    {
        $data = fetch_single('SELECT * FROM monster_houses WHERE userid=' . (int)$owner->ID() . ' LIMIT 1');
        if($data)
            return new House($data, $owner);
        else
            return null;
    }

    /**
     * @param array $data
     * @param User $owner
     * @return House
     */
    public static function Load(&$data, $owner)
    {
        return new House($data, $owner);
    }

    /**
     * @param array $fields
     */
    public function Update($fields)
    {
        $updates = array();

        foreach ($fields as $field)
            $updates[] = '`' . $field . '`=' . quote_smart($this->_data[$field]);

        if(count($updates) > 0)
            fetch_none('UPDATE monster_houses SET ' . implode(', ', $updates) . ' WHERE idnum=' . (int)$this->_data['idnum'] . ' LIMIT 1');
    }


    function RoomTabsHTML($currentRoom)
    {
        global $SETTINGS;

        $m_rooms = $this->rooms;

        $rooms = array('Common');

        foreach($m_rooms as $room)
            $rooms[] = $room;

        $html = '<ul class="tabbed">';

        $i = 0;
        foreach($rooms as $room)
        {
            if($i > $this->_data['max_rooms_shown'])
                break;

            $classes = array();
            if(substr($room, 0, 1) == '$')
                $classes[] = 'locked-room';
            if($room == $currentRoom)
                $classes[] = 'activetab';

            $html .= ' <li class="' . implode(' ', $classes) . '"><nobr><a href="/myhouse.php?room=' . urlencode($room) . '" class="js-load-room" data-room="' . htmlspecialchars($room) . '">' . str_replace('$', '', $room) . '</a></nobr></li>';

            $i++;
        }

        $i = 0;
        foreach($this->addons as $room)
        {
            if($i >= $this->_data['max_addons_shown'])
                break;

            $classes = array('addontab');
            if($room == $currentRoom)
                $classes[] = 'activetab';

            $html .= ' <li class="' . implode(' ', $classes) . '" style="background-image: url(//' . $SETTINGS['static_domain'] . '/gfx/addons/' . urlize($room) . '.png);"><nobr><a href="/myhouse/addon/' . urlize($room) . '.php">' . $room . '</a></nobr></li>';

            $i++;
        }

        $html .= '<li style="border: 0; background-color: transparent;"><a href="/myhouse/managerooms.php"><img src="/gfx/pencil_small.png" height="13" width="15" alt="(manage rooms)" style="vertical-align:text-bottom;" /></a></li>';

        $html .= '</ul>';

        return $html;
    }

    private function AddMaterials($itemName, $quantity)
    {
        if(array_key_exists($itemName, $this->materials))
            $this->materials[$itemName]['qty'] += $quantity;
        else
        {
            $this->materials[$itemName] = array(
                'qty' => $quantity,
                'itemname' => $itemName,
            );
        }
    }

    public function LoadMaterials()
    {
        $this->materials = fetch_multiple_by('
            SELECT COUNT(idnum) AS qty,itemname
            FROM monster_inventory
            WHERE
                user=' . quote_smart($this->user->Username()) . '
                AND location LIKE \'home%\'
                AND location NOT LIKE \'home/$\'
            GROUP BY itemname
        ', 'itemname');
    }

    public function FindComfortItems($stat, $maxQuantity = 3)
    {
        if($this->materials === false)
        {
            throw new Exception('House::FindComfortItems was called before the house\'s materials were not loaded.');
        }

        $itemsFound = array();

        if(count($this->materials) > 0)
        {
            $itemList = ashuffle($this->materials);

            foreach($itemList as $itemName=>$quantity)
            {
                $itemDetails = get_item_byname($itemName);

                if($itemDetails['hourly' . $stat] > 0)
                {
                    $itemsFound[] = $itemDetails;
                    if(count($itemsFound) >= $maxQuantity)
                        break;
                }
            }
        }

        return $itemsFound;
    }
}