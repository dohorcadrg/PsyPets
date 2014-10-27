<?php
abstract class PetQuest
{
    private $_data;

    protected $questProgress;
    protected $participantIds;
    protected $pets;

    protected function __construct(&$progress, $pets)
    {
        $this->_data = $progress;
        $this->questProgress = json_decode($progress['data']);
        $this->pets = $pets;

        $this->participantIds = fetch_multiple_by('SELECT petid FROM psypets_pet_quest_pets WHERE questid=' . $progress['idnum'], 'petid');
    }

    /**
     * @param Pet $pet
     * @return bool
     */
    public function HasParticipant($pet)
    {
        return array_key_exists($pet->ID(), $this->participantIds);
    }

    abstract public function Work();

    /** @param array $args */
    /** @return array */
    abstract protected function Init($args);

    public static function Insert($questClass, $pets, $args)
    {
        $progress = $questClass::Init($args);

        $progress['quest'] = $questClass;

        // insert new quest progress record
        fetch_none('
            INSERT INTO psypets_pet_quest_progress (quest, data) VALUES (' . quote_smart($questClass) . ', ' . quote_smart(json_encode($progress)) . ')
        ');

        $progress['idnum'] = insert_id();

        // insert records for participating pets

        $inserts = array();
        foreach($pets as $pet)
            $inserts[] = '(' . $pet->ID() . ', ' . $progress['idnum'] . ')';

        fetch_none('INSERT INTO psypets_pet_quest_pets (petid, questid) VALUES ' . implode(',', $inserts));

        return new $questClass($progress, $pets);
    }

    protected function Update()
    {
        $this->_data['data'] = json_encode($this->questProgress);

        fetch_none('
            UPDATE psypets_pet_quest_progress
            SET data=' . quote_smart($this->_data['data']) . '
            WHERE idnum=' . (int)$this->_data['idnum'] . '
            LIMIT 1
        ');
    }

    /**
     * @param string $text
     * @return int
     */
    protected function AddLog($text)
    {
        global $now;

        fetch_none('
            INSERT INTO psypets_pet_quest_logs (timestamp, questid, text) VALUES
            (' . (int)$now . ', ' . (int)$this->questProgress['idnum'] . ', ' . quote_smart($text) . ')
        ');

        return $GLOBALS['database']->InsertID();
    }

    /**
     * @param string $text
     * @return int
     */
    protected function AddJournal($text, $logId)
    {
        global $now;

        fetch_none('
            INSERT INTO psypets_pet_quest_logs (timestamp, questid, logid, text) VALUES
            (' . (int)$now . ', ' . (int)$this->questProgress['idnum'] . ', ' . (int)$logId . ', ' . quote_smart($text) . ')
        ');

        return $GLOBALS['database']->InsertID();
    }

    /**
     * @param int $progressId
     * @param array $pets
     * @return mixed
     */
    public static function Load(&$progress, $pets)
    {
        $questClass = $progress['quest'];
        return new $questClass($progress, $pets);
    }

    /**
     * @param User $user
     * @return array
     */
    public static function SelectForUser($user, $pets)
    {
        $quests = array();

        $progresses = fetch_multiple('
            SELECT * FROM psypets_pet_quest_progress
            LEFT JOIN psypets_pet_quest_pets ON psypets_pet_quest_progress.idnum=psypets_pet_quest_pets.questid
            LEFT JOIN monster_pets ON psypets_pet_quest_pets.petid=monster_pets.idnum
            WHERE monster_pets.user=' . quote_smart($user->UserName()) . '
        ');

        foreach($progresses as $progress)
            $quests[] = self::Load($progress, $pets);

        return $quests;
    }
}
