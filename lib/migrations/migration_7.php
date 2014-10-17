<?php
class Migration_7
{
    public function Up()
    {
        fetch_none('
            UPDATE monster_recipes
            SET machine_only=\'yes\'
            WHERE makes LIKE \'%Ice Cream\'
        ');
    }

    public function Down()
    {
        fetch_none('
            UPDATE monster_recipes
            SET machine_only=\'no\'
            WHERE makes LIKE \'%Ice Cream\'
        ');
    }
}
