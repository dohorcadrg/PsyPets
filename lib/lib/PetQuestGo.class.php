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

        // @TODO: get goBook, goBoard, and computer in house, if any
        // @TODO: get a higherRankedPet from the game, if any
        // @TODO: get a goPlayingFriend, if any
        // @TODO: get similarlyRankedPet; any pet that is close in rank to this pet
        //        for 1-30, up to 3 levels difference
        //        for 31-37, up to 2 levels difference
        //        for 38+, up to 1 level difference

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

        if($training > 5 && mt_rand(1, 100) <= $training && $similarlyRankedPet)
            $possibilties[] = 0; // play a game to improve rank

        switch($possibilities[array_rand($possibilities)])
        {
            case 0:
                // elo ratings and things would be fun, but we want ALL pets to be able to succeed
                // wins will grant advances in rank; losses will never set you back

                $won = mt_rand(1, 2) == 1;
                $description = $this->pets[0]->Name() . ' played a ranked game against ' . $similarlyRankedPet . ', and ' . ($won ? 'won' : 'lost') . '!';

                if($won && $this->pets[0]->GoRank() < 47)
                {
                    $this->pets[0]->IncrementGoRank();
                    $description .= ' ' . $this->pets[0]->Name() . ' is now a ' . $this->pets[0]->GoRankDescription() . ' Go player!';
                }

                // @TODO: mark quest as being done

                if($this->pets[0]->GoRank() >= 38) // pro player
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
                $description = $this->pets[0]->Name() . ' studied ' . $goBook->Name() . '.';
                $this->questProgress['training']++;
                break;
            case 2:
                $description = $this->pets[0]->Name() . ' studied Go on-line.';
                $this->questProgress['training']++;
                break;
            case 3:
                $description = $this->pets[0]->Name() . ' got on-line and watched some videos of ' . $higherRankedPet->Name() . '\'s games.';
                $this->questProgress['training']++;
                break;
            case 4:
                $description = $this->pets[0]->Name() . ' got out a Go board, and studied some Go problems from ' . $goBook->Name() . '.';
                $this->questProgress['training']++;
                break;
            case 5:
                // @TODO: adjust relationship of pets
                // @TODO: pets that feel passionate for one another may get... distracted
                if(!$distracted)
                {
                    $description = $this->pets[0]->Name() . ' invited ' . $goPlayingFriend->Name() . ' over. They played Go together.';
                    $this->questProgress['training']++;
                }
                break;
            case 6:
                // @TODO: adjust relationship of pets
                // @TODO: pets that feel passionate for one another may get... distracted
                if(!$distracted)
                {
                    $description = $this->pets[0]->Name() . ' invited ' . $goPlayingFriend->Name() . ' over. They studied Go problems together.';
                    $this->questProgress['training']++;
                }
                break;
            case 7:
                $description = $this->pets[0]->Name() . ' watched a Go game on-line.';
                $this->questProgress['training']++;
                break;
            case 8:
                $description = $this->pets[0]->Name() . ' played a Go game on-line, and ' . (mt_rand(1, 2) == 1 ? 'won' : 'lost') . '.';
                $this->questProgress['training'] += mt_rand(1, 3) == 1 ? 2 : 1;
                break;
            case 9:
                $description = $this->pets[0]->Name() . ' went to The Park to watch people play Go.';
                $this->questProgress['training']++;
                break;
            case 10:
                // @TODO: find an amateur go-playing stranger
                $goPlayingStranger = $this->findGoPlayingStrangerAtPark();

                if($goPlayingStranger && mt_rand(1, 2) == 1)
                {
                    $thisPetWinsChance = 50 + ($this->pets[0]->GoRank() - $goPlayingStranger->GoRank()) * 3; // possible to get <0% or >100% chance of victory
                    $thisPetWon = mt_rand(1, 100) <= $thisPetWinsChance;

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
                break;
            case 12:
                $description = $this->pets[0]->Name() . ' received an invitation to join the Go Academy, and accepted!';
                $this->pets[0]->JoinGoAcademy();
                break;
        }

        // @TODO: add log for journal and pet
    }
}
