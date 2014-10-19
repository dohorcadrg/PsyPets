<?php
class HouseChecker
{
    protected $user;
    protected $house;
    protected $pets;
    protected $quests;
    protected $fireplace;

    /**
     * @param User $user
     * @param House $house
     * @param array $pets
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->pets = Pet::SelectForUser($user, array('location=\'home\'', 'dead=\'no\''));
        $this->house = House::SelectForUser($user);
        $this->fireplace = FireplaceAddOn::SelectForUser($user);
        $this->quests = PetQuest::SelectForUser($this->user, $this->pets);

        $this->house->LoadMaterials();
    }

    /** @return House|null */ public function House() { return $this->house; }

    /** @return bool */
    public function CanRun()
    {
        return ($this->house->Hours() > 0 && count($this->pets) <= $this->user->MaxActivePets() && !$this->house->IsFull());
    }

    // performs one hour of activities
    public function Run()
    {
        $this->house->PassHours(1);

        // check if any pets need/want to perform activities to meet needs: eat, sleep, etc
        foreach($this->pets as $pet)
        {
            /** @var Pet $pet */
            $pet->ReadyAction();

            $pet->ProcessNeeds();

            if($pet->IsSleeping() && !$pet->IsSleepWalking())
                $pet->DoSleep($this->house);
            else
                $pet->DoAttendNeeds($this->house);
        }

        // try to perform all quests
        foreach($this->quests as &$quest)
            $quest->Work($this->house);

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

        $this->house->RecalculateInventorySize();
        process_pet_log_cache();
    }
}
