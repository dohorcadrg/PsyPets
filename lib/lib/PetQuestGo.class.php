<?php
// always a solo project

class PetQuestGo extends PetQuest
{
    // ranks 1-30 are amateur ranks (1 = 30kyu, 2 = 29kyu, 3 = 28kyu ... 30 = 1kyu)
    // ranks 31-37 are amatuer dan ranks (31 = 1d, 32 = 2d ... 37 = 7d)
    // ranks 38-47 are professional dan ranks (38 = 1p, 39 = 2p ... 47 = 9p)

    protected function Init($args)
    {
        return array(
            'training' => 0,
        );
    }

    public function Work()
    {
        $possibilities = array();

        /** @var Pet $thisPet */
        $thisPet = $this->pets[0];

        $petRank = $thisPet->GoRank();

        // @TODO: get goBook, goBoard, and computer in house, if any
        // @TODO: get a goPlayingFriend, if any

        $similarlyRankedPet = $this->findSimilarlyRankedPet($thisPet);
        $higherRankedPet = $this->findHigherRankedPet($thisPet);

        // study go book
        if($goBook && $petRank <= 30)
            $possibilities[] = 1;

        // study go online
        if($computer && $petRank <= 30)
            $possibilities[] = 2;

        // study games of a higher-ranked pet
        if($computer && $petRank > 10 && $higherRankedPet)
            $possibilities[] = 3;

        // practice go problems on own with board and book
        if($goBoard && $goBook)
            $possibilities[] = 4;

        // invite go-playing friend over to practice with
        if($goBoard && $goPlayingFriend)
            $possibilities[] = mt_rand(5, 6); // 5 = play  together; 6 = study together

        // play online
        if($computer)
            $possibilities[] = mt_rand(7, 8); // 7 = play; 8 = watch

        // watch games at The Park
        if($petRank <= 20)
            $possibilities[] = 9;

        // play at The Park
        if($petRank <= 30)
            $possibilities[] = 10;

        // study at go academy
        if($petRank > 28 && $petRank <= 37)
        {
            if($this->pets[0]->IsInGoAcademy())
                $possibilities[] = 11;
            else
                $possibilities[] = 12;
        }

        if($petRank >= 38) // pro and above
        {
            $possibilities[] = 13; // teach at Go Academy
        }

        if($training > 4 + $petRank && mt_rand(1, 97 + $petRank * 3) <= $training && $similarlyRankedPet)
            $possibilties = array(0); // DEFINITELY play a ranked game, attempting to improve rank

        switch($possibilities[array_rand($possibilities)])
        {
            case 0:
                // elo ratings and things would be fun, but we want ALL pets to be able to succeed
                // wins will grant advances in rank; losses will never set you back

                $petScore = success_roll($thisPet->SkillAtGo(), 10, 7);
                $otherPetScore = success_roll($similarlyRankedPet->SkillAtGo(), 10, 7);

                // apply handicap based on difference in skill
                if($petRank < $similarlyRankedPet->GoRank())
                    $petScore++;
                else if($petRank > $similarlyRankedPet->GoRank())
                    $otherPetScore++;

                $won = ($petScore > $otherPetScore || ($petScore == $otherPetScore && mt_rand(1, 2) == 1));

                $description = $thisPet->Name() . ' played a ranked game against ' . $similarlyRankedPet->Name() . ', and ' . ($won ? 'won' : 'lost') . '!';

                if($won && $petRank < 47)
                {
                    $thisPet->IncrementGoRank();
                    $description .= ' ' . $thisPet->Name() . ' is now a ' . $thisPet->GoRankDescription() . ' Go player!';
                }

                // @TODO: mark quest as being done

                if($petRank >= 38) // pro player
                {
                    // @TODO: get base money; get more money if($won)
                }

                if($similarlyRankedPet->GoRank() >= 38) // pro player
                {
                    // @TODO: get base money; get more money if(!$won)
                    // @TODO: add log entry for this pet
                }

                break;
            case 1:
                $description = $thisPet->Name() . ' studied ' . $goBook->Name() . '.';
                $this->questProgress['training']++;
                $this->TrainGo($thisPet, 3, 1, 1);
                break;
            case 2:
                $description = $this->pets[0]->Name() . ' studied Go on-line.';
                $this->questProgress['training']++;
                $this->TrainGo($thisPet, 3, 1, 1);
                break;
            case 3:
                $description = $this->pets[0]->Name() . ' got on-line and watched some videos of ' . $higherRankedPet->Name() . '\'s games.';
                $this->questProgress['training']++;
                $this->TrainGo($thisPet, 3, 1, 1);
                break;
            case 4:
                $description = $this->pets[0]->Name() . ' got out a Go board, and studied some Go problems from ' . $goBook->Name() . '.';
                $this->questProgress['training']++;
                $this->TrainGo($thisPet, 3, 1, 1);
                break;
            case 5:
                // @TODO: adjust relationship of pets
                // @TODO: pets that feel passionate for one another may get... distracted
                if(!$distracted)
                {
                    $description = $this->pets[0]->Name() . ' invited ' . $goPlayingFriend->Name() . ' over. They played Go together.';
                    $this->questProgress['training']++;
                    $this->TrainGo($thisPet, 1, 1, 1);
                }
                break;
            case 6:
                // @TODO: adjust relationship of pets
                // @TODO: pets that feel passionate for one another may get... distracted
                if(!$distracted)
                {
                    $description = $this->pets[0]->Name() . ' invited ' . $goPlayingFriend->Name() . ' over. They studied Go problems together.';
                    $this->questProgress['training']++;
                    $this->TrainGo($thisPet, 3, 1, 1);
                }
                break;
            case 7:
                $description = $this->pets[0]->Name() . ' watched a Go game on-line.';
                $this->questProgress['training']++;
                $this->TrainGo($thisPet, 2, 1, 1);
                break;
            case 8:
                $description = $this->pets[0]->Name() . ' played a Go game on-line, and ' . (mt_rand(1, 2) == 1 ? 'won' : 'lost') . '.';
                $this->questProgress['training'] += mt_rand(1, 3) == 1 ? 2 : 1;
                $this->TrainGo($thisPet, 1, 1, 1);
                break;
            case 9:
                $description = $this->pets[0]->Name() . ' went to The Park to watch people play Go.';
                $this->questProgress['training']++;
                $this->TrainGo($thisPet, 2, 1, 1);
                break;
            case 10:
                $goPlayingStranger = $this->findGoPlayingStrangerAtPark($this->pets[0]);

                if($goPlayingStranger && mt_rand(1, 2) == 1)
                {
                    $petScore = success_roll($this->pets[0]->SkillAtGo(), 10, 7);
                    $otherPetScore = success_roll($goPlayingStranger->SkillAtGo(), 10, 7);

                    $thisPetWon = ($petScore > $otherPetScore || ($petScore == $otherPetScore && mt_rand(1, 2) == 1));

                    $description = $this->pets[0]->Name() . ' went to The Park to play Go, and met ' . $goPlayingStranger->Name() . '.';
                    $description .= ' They played a game; ' .  ($thisPetWon ? $this->pets[0]->Name() : $goPlayingStranger->Name()) . ' won.';
                    // @TODO: describe win: was it an easy victory; a narrow victory; a victory against the odds?

                    // @TODO: add log entry for goPlayingStranger
                    // @TODO: add relationship entry for both pets
                }
                else
                {
                    $thisPetWinsChance = 40 + $this->pets[0]->GoRank();
                    $thisPetWon = mt_rand(1, 100) <= $thisPetWinsChance;

                    $description = $this->pets[0]->Name() . ' went to The Park to play Go, and ' . ($thisPetWon ? 'won' : 'lost') . '.';
                }

                $this->questProgress['training'] += mt_rand(1, 3) == 1 ? 2 : 1;
                $this->TrainGo($thisPet, 1, 1, 1);
                break;
            case 11:
                // @TODO: find an academy go-playing stranger
                $goPlayingStranger = $this->findGoPlayingStrangerInAcademy();

                if($goPlayingStranger && mt_rand(1, 2) == 1)
                {
                    $description = $this->pets[0]->Name() . ' went to study at the Go Academy, and met ' . $goPlayingStranger->Name() . '.';

                    // @TODO: add log entry for goPlayingStranger
                    // @TODO: add relationship entry for both pets
                }
                else
                    $description = $this->pets[0]->Name() . ' went to study at the Go Academy.';

                $this->questProgress['training']++;
                $this->TrainGo($thisPet, 2, 1, 1);
                break;
            case 12:
                $description = $this->pets[0]->Name() . ' received an invitation to join the Go Academy, and accepted!';
                $thisPet->JoinGoAcademy();
                break;
        }

        // @TODO: add log for journal and pet
    }

    /**
     * @param Pet $pet
     * @param int $goChance
     * @param int $intChance
     * @param int $witChance
     *
     * Train a random stat by 1 experience point
     * ex: goChance = 3, intChance = 1, witChance = 1
     *     chance to train 'go' is 3:5, chance to train 'int' is 1:5, chance to train 'wit' is 1:5
     */
    private function TrainGo($pet, $goChance, $intChance, $witChance)
    {
        $r = mt_rand(1, $goChance + $intChance + $witChance);

        if($r <= $goChance)
            $pet->Train('go', 1, $hour);
        else if($r <= $goChance + $intChance)
            $pet->Train('int', 1, $hour);
        else
            $pet->Train('wit', 1, $hour);
    }

    /**
     * @var Pet $thisPet
     * @return Pet|null
     */
    private function findGoPlayingStrangerAtPark($thisPet)
    {
        // @TODO: there's a better way than ORDER BY RAND(), but I forget; look it up
        $petData = fetch_single('
            SELECT *
            FROM monster_pets
            WHERE
                idnum!=' . quote_smart($thisPet->ID()) . ' AND
                go_rank<=30
            ORDER BY RAND()
            LIMIT 1
        ');

        if(!$petData)
            return null;
        else
        {
            $owner = User::GetByLogin($petData['owner']);

            return Pet::Load($petData, $owner);
        }
    }

    /**
     * @var Pet $thisPet
     * @return Pet|null
     */
    private function findHigherRankedPet($thisPet)
    {
        // @TODO: there's a better way than ORDER BY RAND(), but I forget; look it up
        $petData = fetch_single('
            SELECT *
            FROM monster_pets
            WHERE
                idnum!=' . quote_smart($thisPet->ID()) . ' AND
                go_rank>' . $thisPet->GoRank() . '
            ORDER BY RAND()
            LIMIT 1
        ');

        if(!$petData)
            return null;
        else
        {
            $owner = User::GetByLogin($petData['owner']);

            return Pet::Load($petData, $owner);
        }
    }

    /**
     * @param Pet $thisPet
     * @return Pet|null
     */
    private function findSimilarlyRankedPet($thisPet)
    {
        //        for 1-30, up to 3 levels difference
        //        for 31-37, up to 2 levels difference
        //        for 38+, up to 1 level difference
        if($thisPet->GoRank() <= 30)
            $delta = 3;
        else if($thisPet->GoRank() <= 37)
            $delta = 2;
        else
            $delta = 1;

        // @TODO: there's a better way than ORDER BY RAND(), but I forget; look it up
        $petData = fetch_single('
            SELECT *
            FROM monster_pets
            WHERE
                idnum!=' . quote_smart($thisPet->ID()) . ' AND
                go_rank>=' . ($thisPet->GoRank() - $delta) . ' AND
                go_rank<=' . ($thisPet->GoRank() + $delta) . '
            ORDER BY RAND()
            LIMIT 1
        ');

        if(!$petData)
            return null;
        else
        {
            $owner = User::GetByLogin($petData['owner']);

            return Pet::Load($petData, $owner);
        }
    }
}
