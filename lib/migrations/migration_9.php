<?php
class Migration_9
{
    public function Up()
    {
        fetch_none("ALTER TABLE `monster_profiles` CHANGE `gender` `gender` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");
        fetch_none("UPDATE monster_profiles SET gender=''");
    }

    public function Down()
    {
        fetch_none("ALTER TABLE `monster_profiles` CHANGE `gender` `gender` ENUM('none','female','male') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'none'");
    }
}
