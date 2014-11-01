<?php
class Migration_10
{
    public function Up()
    {
        fetch_none('ALTER TABLE monster_pets ADD COLUMN go SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER music');
        fetch_none('ALTER TABLE monster_pets ADD COLUMN go_count INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER music_count');
        fetch_none('ALTER TABLE monster_pets ADD COLUMN go_rank TINYINT(3) UNSIGNED NOT NULL DEFAULT 1 AFTER love_level');
        fetch_none("ALTER TABLE monster_pets ADD COLUMN in_go_academy ENUM('no','yes') NOT NULL DEFAULT 'no' AFTER go_count");
    }

    public function Down()
    {
        fetch_none('ALTER TABLE monster_pets DROP COLUMN go');
        fetch_none('ALTER TABLE monster_pets DROP COLUMN go_count');
        fetch_none('ALTER TABLE monster_pets DROP COLUMN go_rank');
        fetch_none('ALTER TABLE monster_pets DROP COLUMN in_go_academy');
    }
}
