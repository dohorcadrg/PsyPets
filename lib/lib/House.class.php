<?php
class House
{
    private $_data;
    private $user;
    private $addons, $rooms;
    private $inventory = false;

    private function __construct($data, $owner)
    {
        $this->_data = $data;
        $this->user = $owner;
        $this->addons = explode(',', $data['addons']);
        $this->rooms = explode(',', $data['rooms']);
    }

    public function Hours()
    {
        return floor((time() - $this->_data['lasthour']) / (60 * 60));
    }

    public function HasAddOn($addon) { return in_array($addon, $this->addons); }

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
}