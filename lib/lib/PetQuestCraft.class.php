<?php
class PetQuestCraft extends PetQuest
{
    protected $craftTable;
    protected $craftIdnum;
    protected $craftRecord;

    public static $TYPE_TO_SKILL = array(
        'smith' => 'smi',
        'tailor' => 'tai',
    );

    public function __construct($progress, $pets)
    {
        parent::__construct($progress, $pets);

        $this->craftTable = $this->questProgress['table'];
        $this->craftIdnum = $this->questProgress['idnum'];
        $this->craftRecord = fetch_single('SELECT * FROM `' . $this->craftTable . '` WHERE idnum=' . (int)$this->craftIdnum . ' LIMIT 1');
    }

    public function Init($args)
    {
        global $now;

        switch($args['type'])
        {
            case 'smith': $table = 'psypets_smiths'; break;
            case 'tailor': $table = 'psypets_tailors'; break;
            default: throw new Exception('unknown craft quest type: ' . $args['type']);
        }

        $difficulty = (int)$args['difficulty'];
        $minDifficulty = $difficulty - 5;
        $maxDifficulty = $difficulty + 2;

        $month = date('n', $now);

        $possibilities = fetch_multiple('
            SELECT idnum
            FROM ' . $table . '
            WHERE
                difficulty>' . $minDifficulty . ' AND difficulty<' . $maxDifficulty . ' AND
                min_month<=' . $month . ' AND max_month >=' . $month . '
        ');

        $craft = $possibilities[array_rand($possibilities)];

        return array(
            'type' => $args['type'],
            'planningToDo' => floor($craft['complexity'] / 3),
            'materialsNeeded' => explode(',', $craft['ingredients']),
            'workToDo' => $craft['complexity'],
            'yield' => explode(',', $craft['makes']),
        );
    }

    public function Work()
    {
        if($this->questProgress['planningToDo'] > 0)
            $this->Plan();
        else if(count($this->questProgress['materialsNeeded']) > 0)
            $this->Collect();
        else if($this->questProgress['workToDo'] > 0)
            $this->Build();
        else
            $this->Finish();
    }

    protected function Plan()
    {
        $stat = self::$TYPE_TO_SKILL[$this->questProgress['type']];

        $skillBook = $this->FindSkillBook();
        $computer = $this->FindItemNamed(Item::$COMPUTERS);
        $paper = $this->FindItemNamed('Paper');

        $possibilities = array();

        // go to the library
        $possibilities[] = 1;

        if($computer !== false)
        {
            $possibilities[] = 2; // look stuff up online
            $possibilities[] = 3; // flesh out ideas on computer
        }

        // read book
        if($skillBook !== false)
            $possibilities[] = 4;

        // sketch stuff out on paper
        //if($paper)
        //    $possibilities[] = 5;

        // group discussion
        if(count($this->pets) > 0)
            $possibilities[] = 6;

        if($this->questProgress['type'] == 'tailor')
        {
            $mannequin = $this->FindItemNamed('Mannequin');
            if($mannequin)
                $possibilities[] = 7;
        }

        switch($possibilities[array_rand($possibilities)])
        {
            case 1:
                $description = $this->ListParticipants() . ' went to The Library to look for references.';
                $this->Train(array('int' => 60, $stat => 40));
                break;

            case 2:
                $description = $this->ListParticipants() . ' used ' . $computer . ' to look online for information.';
                $this->Train(array('int' => 60, $stat => 40));
                break;

            case 3:
                $description = $this->ListParticipants() . ' used ' . $computer . ' to flesh out ideas.';
                $this->Train(array('int' => 40, $stat => 40, 'wit' => 20));
                break;

            case 4:
                $description = $this->ListParticipants() . ' looked through ' . $skillBook . ' for ideas.';
                $this->Train(array('int' => 60, $stat => 40));
                break;

            /*
            case 5:
                $description = $this->ListParticipants() . ' sketched out some ideas on Paper.';
                $this->Train(array('int' => 40, $stat => 40, 'wit' => 20));
                // @TODO: turn paper into notes item, depending on skill (ex: Smithing Notes), which count as "books" that can be studied
                // greater pet skills = better book (Simple Smithing Notes, Smithing Notes, Advanced Smithing Notes, Masterful Smithing Notes?)
                break;
            */

            case 6:
                // @TODO: choose adjective based on pet personalities (ex: serious vs. fun)
                $description = $this->ListParticipants() . ' talked excitedly about the project.';
                $this->Train(array('int' => 40, $stat => 40, 'wit' => 20));
                break;

            case 7:
                $description = $this->ListParticipants() . ' used ' . $mannequin . ' to flesh out ideas.';
                $this->Train(array($stat => 60, 'int' => 20, 'wit' => 20));
                break;
        }

        $fraction = 1.25;
        $skill = 0;

        foreach($this->pets as $pet)
        {
            $skill += $pet->Intelligence() * 0.4 + $pet->Smithing() * 0.4 + $pet->Improvisation() * 0.2;
            $fraction *= 0.8;
        }

        $skill = ceil($skill * $fraction);

        // @TODO: log & journal
        // $logId = $this->AddLog('');
        // $this->AddJournal('', $logId);

        if($skill > $this->questProgress['planningToDo'])
            $this->questProgress['planningToDo'] = 0;
        else
            $this->questProgress['planningToDo'] -= $skill;
    }

    protected function Collect()
    {
        // @TODO: look in house for any materials needed; clear currentlyCollecting if the material being looked for is no longer needed

        if(count($this->questProgress['materialsNeeded']) == 0) return;

        if(!array_key_exists('currentlyCollecting', $this->questProgress) || $this->questProgress['currentlyCollecting'] == false)
        {
            $this->questProgress['currentlyCollecting'] = $this->questProgress['materialsNeeded'][array_rand($this->questProgress['materials_needed'])];

            // @TODO: fill this out depending on material being sought out:
            $this->questProgress['currentlyCollectingType'] = 'mine';
            $this->questProgress['currentlyCollectingStep'] = 'Searching';
            $this->questProgress['currentlyCollectingSearchingRemaining'] = 0;
            $this->questProgress['currentlyCollectingHarvestingRemaining'] = 0;
        }

        // @TODO: when searching, and gathering, a chance to gather other items incidentally, depending on level
        // ex: say the pets need to get 20 searching points to find the location of some Pyrestone. as part of their search,
        // the pets look around the countryside, scoring a 14 in their search. that's 14 closer, AND the pets should have a
        // chance to find any gathering item which is 14 difficulty or below

        switch($this->questProgress['currentlyCollectingType'])
        {
            case 'mine':
            case 'lumberjack':
            case 'fish':
            case 'hunt': // @TODO: move most monsters from "adventuring" to "hunting"
            case 'gather':
        }
    }

    protected function Build()
    {
        // total up skill of pets involved; take % based on number of pets involved
        //   1 pet: 100% total
        //   2 pets: 80% total
        //   3 pets: 64% total
        //   4 pets: 51.2%
        //   5 pets: 40.96%
        //   6 pets: 32.768%
        //   ...

        $fraction = 1.25;
        $skill = 0;

        foreach($this->pets as $pet)
        {
            $skill += $pet->SkillAtCraft($this->questProgress['type']);
            $fraction *= 0.8;
        }

        $skill = ceil($skill * $fraction);

        // @TODO: log & journal
        // $logId = $this->AddLog('');
        // $this->AddJournal('', $logId);

        if($skill > $this->questProgress['workToDo'])
            $this->questProgress['workToDo'] = 0;
        else
            $this->questProgress['workToDo'] -= $skill;
    }

    protected function Finish()
    {
        // @TODO: hand out yield, and finish project
    }

    /**
     * @return bool|string
     */
    protected function FindSkillBook()
    {
        switch($this->questProgress['type'])
        {
            case 'smith': return $this->FindItemNamed(Item::$SMITHING_BOOKS);
            default: return false;
        }
    }
}