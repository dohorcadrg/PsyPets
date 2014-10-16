<?php
class HouseChecker
{
    protected $user;
    protected $pets;
    protected $quests;

    /**
     * @param User $user
     * @param array $pets
     */
    public function __construct($user, $pets)
    {
        $this->user = $user;
        $this->pets = $pets;
        $this->quests = PetQuest::SelectForUser($this->user, $this->pets);

        // @TODO: load up the house inventory
    }

    // performs one hour of activities
    public function Step()
    {
        // check if any pets need/want to perform activities to meet needs: eat, sleep, etc
        foreach($this->pets as $pet)
        {
            /** @var Pet $pet */
            $pet->ReadyAction();

            $pet->ProcessNeeds();

            if($pet->IsSleeping() && !$pet->IsSleepWalking())
                $pet->DoSleep();
            else
                $pet->DoAttendNeeds();
        }

        // try to perform all quests
        foreach($this->quests as &$quest)
            $quest->Work();

        // are there any pets that STILL didn't do anything?  if so, find something for them now:
        foreach($this->pets as $pet)
        {
            /** @var Pet $pet */
            if($pet->MayAct())
            {

            }

            $pet->Update(array(
                'sleeping', 'asleep_time',
                'energy', 'food', 'safety', 'love', 'esteem',
                'caffeinated', 'inspired',
                'dead',
                'pregnant_asof',
            ));
        }
    }
}
