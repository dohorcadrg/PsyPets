<?php
class PetQuestCraft extends PetQuest
{
    protected $craftTable;
    protected $craftIdnum;
    protected $craftRecord;

    /*
        questProgressData = {
            table: 'psypets_smiths',
            idnum: 5,
            materialsCollected: [ 'Staff' ],
            workPerformed: 1
        }
    */

    public static $TYPE_TO_SKILL = array(
        'smith' => 'smi',
        'tailor' => 'tai',
    );

    public function __construct($progress, $pets)
    {
        parent::__construct($progress, $pets);

        $this->craftTable = $this->questProgressData['table'];
        $this->craftIdnum = $this->questProgressData['idnum'];
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

        $possibilities = array();

        // go to the library
        $possibilities[] = 1;

        if($computer)
        {
            $possibilities[] = 2; // look stuff up online
            $possibilities[] = 3; // flesh out ideas on computer
        }

        // read book
        if($skillBook)
            $possibilities[] = 4;

        // sketch stuff out on paper
        if($paper)
            $possibilities[] = 5;

        // group discussion
        if(count($this->pets) > 0)
            $possibilities[] = 6;

        switch($possibilities[array_rand($possibilities)])
        {
            case 1:
                $description = $this->ListParticipants() . ' went to The Library to look for references.';
                $this->Train(array('int' => 60, $stat => 40));
                break;

            case 2:
                $description = $this->ListParticipants() . ' looked online for information.';
                $this->Train(array('int' => 60, $stat => 40));
                break;

            case 3:
                $description = $this->ListParticipants() . ' got on the computer to flesh out ideas.';
                $this->Train(array('int' => 40, $stat => 40, 'wit' => 20));
                break;

            case 4:
                $description = $this->ListParticipants() . ' looked through ' . $skillBook->getName() . ' to look for ideas.';
                $this->Train(array('int' => 60, $stat => 40));
                break;

            case 5:
                $description = $this->ListParticipants() . ' sketched out some ideas on Paper.';
                $this->Train(array('int' => 40, $stat => 40, 'wit' => 20));
                // @TODO: turn paper into notes item, depending on skill (ex: Smithing Notes), which count as "books" that can be studied
                // greater pet skills = better book (Simple Smithing Notes, Smithing Notes, Advanced Smithing Notes, Masterful Smithing Notes?)
                break;

            case 6:
                // @TODO: choose adjective based on pet personalities (ex: serious vs. fun)
                $description = $this->ListParticipants() . ' talked excitedly about the project.';
                $this->Train(array('int' => 40, $stat => 40, 'wit' => 20));
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
}