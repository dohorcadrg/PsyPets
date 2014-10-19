<?php
class Migration_8
{
    public function Up()
    {
        fetch_none('ALTER TABLE monster_pets ADD COLUMN alcohol TINYINT(3) NOT NULL AFTER caffeinated');
    }

    public function Down()
    {
        fetch_none('ALTER TABLE monster_pets DROP COLUMN alcohol');
    }
}
