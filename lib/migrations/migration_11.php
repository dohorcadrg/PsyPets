<?php
class Migration_11
{
    public function Up()
    {
        fetch_none("ALTER TABLE psypets_pet_quest_progress ADD COLUMN complete ENUM('no','yes') NOT NULL DEFAULT 'no'");
    }

    public function Down()
    {
        fetch_none('ALTER TABLE monster_pets DROP COLUMN psypets_pet_quest_progress');
    }
}
