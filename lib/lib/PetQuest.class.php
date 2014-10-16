<?php
abstract class PetQuest
{
    private $questProgress;

    protected $questProgressData;
    protected $participantIds;
    protected $petArrays;

    protected function __construct(&$progress, &$userpets)
    {
        $this->questProgress = $progress;
        $this->questProgressData = json_decode($progress['data']);
        $this->petArrays = &$userpets;

        $this->participantIds = fetch_multiple_by('SELECT petid FROM psypets_pet_quest_pets WHERE questid=' . $progress['idnum'], 'petid');
    }

    abstract public function Work();

    /** @param array $args */
    /** @return array */
    abstract protected function Init($args);

    public static function Insert($questClass, &$userpets, $args)
    {
        $progress = $questClass::Init($args);

        // @TODO: create new record:
        /*
        fetch_none('
            INSERT INTO psypets_pet_quest_progress (data) VALUES
        ');
        */
        // @TODO: create records for participating pets

        return new $questClass($progress, $userpets);
    }

    protected function Update()
    {
        $this->questProgress['data'] = json_encode($this->questProgressData);

        fetch_none('
            UPDATE psypets_pet_quest_progress
            SET data=' . quote_smart($this->questProgress['data']) . '
            WHERE idnum=' . (int)$this->questProgress['idnum'] . '
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
     * @param array $userpets
     * @return mixed
     */
    public static function Load(&$progress, &$userpets)
    {
        $questClass = $progress['quest'];
        return new $questClass($progress, $userpets);
    }
}