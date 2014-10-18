<?php
class HouseChecker
{
    protected $user;
    protected $house;
    protected $pets;
    protected $quests;

    /**
     * @param User $user
     * @param House $house
     * @param array $pets
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->pets = Pet::SelectForUser($user);
        $this->house = House::SelectForUser($user);
        $this->quests = PetQuest::SelectForUser($this->user, $this->pets);

        // @TODO: load up the house inventory
    }

    /** @return House|null */ public function House() { return $this->house; }

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
