<?php
class Pet
{
    /** @var array $_data */ private $_data;
    /** @var bool $performedAction */ private $performedAction = false;
    /** @var User $user */ private $user;

    protected function __construct(&$petData, $owner)
    {
        $this->_data = &$petData;
        $this->user = $owner;
    }

    /**
     * @param array $petData
     * @param User $owner
     * @return Pet
     */
    public static function Load(&$petData, $owner)
    {
        return new Pet($petData, $owner);
    }

    /**
     * @param int $id
     * @param User $owner
     * @return null|Pet
     */
    public static function Select($id, $owner)
    {
        $petData = fetch_single('SELECT * FROM monster_pets WHERE idnum=' . (int)$id . ' AND user=' . quote_smart($owner->Username()) . ' LIMIT 1');
        if($petData)
            return new Pet($petData, $owner);
        else
            return null;
    }

    public static function SelectForUser($owner, $wheres = array())
    {
        $pets = array();

        $wheres[] = 'user=' . quote_smart($owner->Username());

        $petData = fetch_multiple('SELECT * FROM monster_pets WHERE ' . implode(' AND ', $wheres));

        foreach($petData as $data)
            $pets[] = new Pet($data, $owner);

        return $pets;
    }

    public function Name() { return $this->_data['petname']; }

    public function ReadyAction() { $this->performedAction = false; }
    public function MayAct() { return !$this->performedAction && !$this->IsDead(); }

    public function IsDead() { return($this->_data['dead'] != 'no'); }
    public function IsZombie() { return($this->_data['zombie'] == 'yes'); }
    public function IsSleeping() { return($this->_data['sleeping'] == 'yes'); }
    public function WakeUp() { $this->_data['sleeping'] = 'no'; }

    /*
    public function Size()
    {
        return ceil(1 + $this->_data['str'] * 7.5 + $this->_data['sta'] * 10 + $this->_data['athletics'] * 5);
    }
    */

    /**
     * @param House $house
     */
    public function FallAsleep($house)
    {
        $this->_data['sleeping'] = 'yes';

        // @TODO: consider fireplace; log sleep event
        /*if($house->HasAddOn('Fireplace'))
        {
            gain_love($newpet, 2);
            gain_safety($newpet, 2);

            add_logged_event_cached($myuser['idnum'], $mypet['idnum'], $hour, 'hourly', 'sleep', $mypet['petname'] . ' fell asleep by the fire.', array('love' => 2, 'safety' => 2));
        }
        else*/
            add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', 'sleep', $this->Name() . ' fell asleep.');
    }

    public function IsSleepWalking()
    {
        return($this->_data['merit_sleep_walker'] == 'yes' && $this->_data['energy'] > 0 && mt_rand(1, 20) == 1);
    }

    public function ProcessNeeds()
    {
        if($this->IsSleeping())
        {
            // negative loss is not counted; this is a hacky way to randomize loss while sleeping
            $this->DrainNeed('food', mt_rand(-2, ($this->_data['pregnant_asof'] >= 14 ? 2 : 1)));

            $this->GainEnergy(rand(2 + floor($this->_data['sta'] / 2), 3 + $this->_data['sta']));
        }
        else
        {
            $this->DrainNeed('food', ($this->_data['pregnant_asof'] >= 14 ? 2 : 1));
            $this->DrainNeed('energy', rand(0, ($this->_data['pregnant_asof'] >= 14 ? rand(1, 2) : 1)));
        }

        $this->DrainNeed('caffeinated', 1);
        $this->DrainNeed('inspired', 1);
        $this->DrainNeed('safety', 1);
        $this->DrainNeed('love', 1);
        $this->DrainNeed('esteem', 1);

        if($this->_data['energy'] <= -12)
        {
            $this->_data['sleeping'] = 'yes';
            add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', 'sleep', $this->Name() . ' passed out! -_-');

            $this->performedAction = true;
            return true;
        }

        if($this->_data['food'] <= -12)
        {
            $this->_data['dead'] = 'starved';
            $this->_data['pregnant_asof'] = 0;

            add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', 'sleep', $this->Name() . ' has died of starvation!! T_T');

            $this->performedAction = true;
            return true;
        }

        return false;
    }

    /**
     * @param House $house
     */
    public function DoSleep($house)
    {
        if(!$this->MayAct()) return;

        $light_sleeper_modifier = ($this->_data['merit_light_sleeper'] == 'yes' ? 10 : 0);

        $this->_data['asleep_time']++;

        $energy_percent = $this->_data['energy'] / $this->MaxEnergy();

        $energy_percent *= $energy_percent * $energy_percent * $energy_percent * 100;

        if($this->_data['food'] <= 3)
        {
            $desire = -($this->_data['food'] - 4);
            $hungry = ceil($desire * $desire);
        }
        else
            $hungry = 0;

        if(rand(1, 100) <= $energy_percent + $hungry + $light_sleeper_modifier)
        {
            $this->_data['sleeping'] = 'no';
            $this->_data['asleep_time'] = 0;
            add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', 'sleep', $this->_data['petname'] . ' woke up.');

            if(mt_rand(1, $this->_data['dream_rate']) == 1)
            {
                add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', 'sleep', dream_description($this->_data));
                record_pet_stat($this->_data['idnum'], 'Remembered a Dream', 1);
            }
        }
        else
            add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', 'sleep');

        $this->performedAction = true;
    }

    /**
     * @param House $house
     */
    public function DoAttendNeeds($house)
    {
        !$this->MayAct() or
        $this->IsZombie() or

        $this->ConsiderFood($house) or
        $this->ConsiderEnergy($house) or
        $this->ConsiderSafety($house) or
        $this->ConsiderLove($house) or
        $this->ConsiderEsteem($house);
    }

    /**
     * @param House $house
     * @return bool
     */
    private function ConsiderFood($house)
    {
        // at 0 food, with neutral conscientiousness, we have a 74% chance
        // at 5 food, 19%
        // at 10 food, 6%
        $desire = 8 - $this->Food();
        $desire *= abs($desire);
        $desire += 10 + $this->Conscientiousness();

        if (mt_rand(1, 100) <= $desire)
            return $this->FindFood();
        else
            return false;
    }

    /**
     * @param House $house
     * @return bool
     */
    private function ConsiderSafety($house)
    {
        if ($this->Food() > 0 && $this->Energy() > 0)
        {
            $desire = 8 - $this->Safety();
            $desire *= abs($desire);
            $desire += 10 + $this->Conscientiousness();

            if(mt_rand(1, 100) <= $desire)
            {
                if(mt_rand(-5, 15) <= $this->Extroversion())
                    return $this->Hangout('safety');
                else
                    return $this->ReassureSelf('safety', $house);
            }
        }

        return false;
    }

    /**
     * @param House $house
     * @return bool
     */
    private function ConsiderLove($house)
    {
        if($this->Food() > 0 && $this->Energy() > 0 && $this->Safety() > 0)
        {
            $desire = 8 - $this->Love();
            $desire *= abs($desire);
            $desire += 10 + $this->Conscientiousness();

            if(mt_rand(1, 100) <= $desire)
            {
                if(mt_rand(-5, 15) <= $this->Extroversion())
                    return $this->Hangout('love');
                else
                    return $this->ReassureSelf('love', $house);
            }
        }

        return false;
    }

    /**
     * @param House $house
     * @return bool
     */
    private function ConsiderEsteem($house)
    {
        if($this->Food() > 0 && $this->Energy() > 0 && $this->Safety() > 0 && $this->Love() > 0)
        {
            $desire = 8 - $this->Esteem();
            $desire *= abs($desire);
            $desire += 10 + $this->Conscientiousness();

            if(mt_rand(1, 100) <= $desire)
            {
                if(mt_rand(-5, 15) <= $this->Extroversion())
                    return $this->Hangout('esteem');
                else
                    return $this->ReassureSelf('esteem', $house);
            }
        }

        return false;
    }

    /**
     * @param House $house
     * @return bool
     */
    private function ConsiderEnergy($house)
    {
        $desire = 8 - $this->Caffeine() * 2 - $this->Energy();
        $desire *= abs($desire);
        $desire += $this->Conscientiousness() * 2;

        if (mt_rand(1, 100) <= $desire)
        {
            $this->FallAsleep($house);
            $this->performedAction = true;
            return true;
        }

        return false;
    }

    private function FindFood()
    {
        $this->GainFood(mt_rand(2, 8));
        $this->performedAction = true;

        return true;
    }

    private function Hangout($stat)
    {
        return false;
    }

    /**
     * @param string $stat
     * @param House $house
     * @return bool
     */
    private function ReassureSelf($stat, $house)
    {
        $items = $house->FindComfortItems($stat);

        if(count($items) == 0)
        {
            if($stat == 'food')
                add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', $stat . '_unable', '<span class="obstacle">' . $this->Name() . ' is hungry, but couldn\'t find anything in the house to be fed by.</span>');
            else if($stat == 'safety')
                add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', $stat . '_unable', '<span class="obstacle">' . $this->Name() . ' doesn\'t feel safe, but couldn\'t find anything in the house to be calmed by.</span>');
            else if($stat == 'love')
                add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', $stat . '_unable', '<span class="obstacle">' . $this->Name() . ' is lonely, but couldn\'t find anything in the house to be comforted by.</span>');
            else if($stat == 'esteem')
                add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', $stat . '_unable', '<span class="obstacle">' . $this->Name() . ' is depressed, but couldn\'t find anything in the house to be comforted by.</span>');

            return false;
        }
        else
        {
            $food_gain = 0;
            $safety_gain = 0;
            $love_gain = 0;
            $esteem_gain = 0;
            $itemNamesUsed = array();

            foreach($items as $item)
            {
                $food_gain += $this->GainFood($item['hourlyfood'] * ($stat == 'food' ? 2 : 1));
                $safety_gain += $this->GainSafety($item['hourlysafety'] * ($stat == 'safety' ? 2 : 1));
                $love_gain += $this->GainLove($item['hourlylove'] * ($stat == 'love' ? 2 : 1));
                $esteem_gain += $this->GainEsteem($item['hourlyesteem'] * ($stat == 'esteem' ? 2 : 1));

                if($item['hourlystat'] != '')
                {
                    $this->Train($item['hourystat'], 2, $hour);
                }

                $itemNamesUsed[] = $item['itemname'];

                if($this->Love() >= $this->MaxLove())
                    break;
            }

            add_logged_event_cached($this->user->ID(), $this->_data['idnum'], $hour, 'hourly', $stat, '<span class="eating">' . $this->Name() . ' was comforted by ' . implode(', ', $itemNamesUsed) . '.</span>', array('food' => $food_gain, 'safety' => $safety_gain, 'love' => $love_gain, 'esteem' => $esteem_gain));

            return true;
        }
    }

    /**
     * @param string $stat
     * @param int $experience
     * @param int $hour
     * @param bool $force
     * @return int
     */
    public function Train($stat, $experience, $hour, $force = false)
    {
        $increase = 0;

        if(
            $force === true ||
            (
                $this->EnergyNeedMet() &&
                $this->FoodNeedMet() &&
                $this->SafetyNeedMet() &&
                $this->LoveNeedMet() &&
                $this->EsteemNeedMet()
            )
        )
        {
            if($this->Alcohol() > 0 && $force !== true)
                $experience = floor($experience / 5);

            if($experience <= 0)
                return 0;

            $new_training = $this->_data[$stat . '_count'] + $experience;
            while($new_training >= level_stat_exp($this->_data[$stat]))
            {
                $new_training -= level_stat_exp($this->_data[$stat]);
                $this->_data[$stat]++;
                $increase++;
            }

            $this->_data[$stat . '_count'] = $new_training;

            $this->Update(array($stat, $stat . '_count'));

            if($increase > 0)
                log_level_up($this->_data, $stat, $increase, $hour);
        }

        return $increase;
    }

    public function DrainNeed($stat, $amount)
    {
        if($amount <= 0)
            return 0;

        $this->_data[$stat] -= $amount;

        return $amount;
    }

    public function GainEnergy($amount)
    {
        if($amount + $this->_data['energy'] > $this->MaxEnergy())
            $amount = $this->MaxEnergy() - $this->_data['energy'];

        $this->_data['energy'] += $amount;

        return $amount;
    }

    public function GainFood($amount)
    {
        // excess food is wasted
        if($amount + $this->_data['food'] > $this->MaxFood())
            $amount = $this->MaxFood() - $this->_data['food'];

        $this->_data['food'] += $amount;

        return $amount;
    }

    public function GainSafety($amount)
    {
        if($this->FoodNeedMet() && $this->EnergyNeedMet())
        {
            if($amount + $this->_data['safety'] > $this->MaxSafety())
                $amount = $this->MaxSafety() - $this->_data['safety'];

            $this->_data['safety'] += $amount;

            return $amount;
        }
        else
            return 0;
    }

    public function GainLove($amount)
    {
        if($this->FoodNeedMet() && $this->EnergyNeedMet() && $this->SafetyNeedMet())
        {
            if($amount + $this->_data['love'] > $this->MaxLove())
                $amount = $this->MaxLove() - $this->_data['love'];

            $this->_data['love'] += $amount;

            return $amount;
        }
        else
            return 0;
    }

    public function GainEsteem($amount)
    {
        if($this->FoodNeedMet() && $this->EnergyNeedMet() && $this->SafetyNeedMet() && $this->LoveNeedMet())
        {
            if($amount + $this->_data['esteem'] > $this->MaxEsteem())
                $amount = $this->MaxEsteem() - $this->_data['esteem'];

            $this->_data['esteem'] += $amount;

            return $amount;
        }
        else
            return 0;
    }

    public function Alcohol() { return $this->_data['alcohol']; }
    public function Caffeine() { return $this->_data['caffeinated']; }
    public function Inspiration() { return $this->_data['inspired']; }

    public function Energy() { return $this->_data['energy']; }
    public function MaxEnergy() { return 12 + ($this->_data['sta'] * 2) + $this->_data['athletics'] + $this->_data['str']; }
    public function EnergyNeedMet() { return $this->_data['energy'] > 0 || $this->_data['caffeinated'] > 0; }

    public function Food() { return $this->_data['food']; }
    public function MaxFood() { return ($this->_data['merit_ravenous'] == 'yes' ? 24 : 12) + ($this->_data['sta'] + $this->_data['sur']) * 2; }
    public function FoodNeedMet() { return $this->_data['food'] > 0; }

    public function Safety() { return $this->_data['safety']; }
    public function MaxSafety() { return 24; }
    public function SafetyNeedMet() { return $this->_data['safety'] > 0 || $this->_data['alcohol'] > 0; }

    public function Love() { return $this->_data['love']; }
    public function MaxLove() { return 48 + $this->Extroversion() * 2; }
    public function LoveNeedMet() { return $this->_data['love'] > 0; }

    public function Esteem() { return $this->_data['esteem']; }
    public function MaxEsteem() { return 48 + $this->Conscientiousness() * 2; }
    public function EsteemNeedMet() { return $this->_data['esteem'] > 0 || $this->_data['alcohol'] > 0; }

    public function Extroversion() { return $this->_data['extraverted']; }
    public function Openness() { return $this->_data['open']; }
    public function Conscientiousness() { return $this->_data['conscientious']; }
    public function Playfulness() { return $this->_data['playful']; }
    public function Independence() { return $this->_data['independent']; }


    public function HisHer() { return $this->_data['gender'] == 'female' ? 'her' : 'his'; }
    public function HimHer() { return $this->_data['gender'] == 'female' ? 'her' : 'him'; }
    public function HeShe() { return $this->_data['gender'] == 'female' ? 'he' : 'she'; }

    /**
     * @param array $fields
     */
    public function Update($fields)
    {
        $updates = array();

        foreach ($fields as $field)
            $updates[] = '`' . $field . '`=' . quote_smart($this->_data[$field]);

        if(count($updates) > 0)
            fetch_none('UPDATE monster_pets SET ' . implode(', ', $updates) . ' WHERE idnum=' . (int)$this->_data['idnum'] . ' LIMIT 1');
    }
}