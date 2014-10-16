<?php
class Migration_6
{
    public function Up()
    {
        $stats_to_fix = array(
            'extraverted',
            'open',
            'conscientious',
        );

        foreach($stats_to_fix as $stat)
        {
            fetch_none('
                UPDATE monster_pets
                SET ' . $stat . '=10
                WHERE ' . $stat . '>10
            ');
            echo 'Fixed "' . $stat . '" trait for ' . affected_rows() . ' pets', "\n";
        }
    }

    public function Down()
    {
    }
}
