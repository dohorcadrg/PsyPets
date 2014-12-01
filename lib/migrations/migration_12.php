<?php
class Migration_12
{
    public function Up()
    {
        // rename Shortening to Lard
        fetch_none("UPDATE `psypets`.`monster_items` SET `itemname` = 'Lard', `anagramname` = 'adlr' WHERE `monster_items`.`itename` = 'Shortening' LIMIT 1");

        $this->RenameInventory('Shortening', 'Lard');

        $this->RenameIngredient('monster_recipes', 'Shortening', 'Lard');

        // rename Scales to Scaly Fish
        fetch_none("
            UPDATE
                `psypets`.`monster_items`
            SET
                `itemname` = 'Scaly Fish',
                `itemtype` = 'food/meat/fish',
                `anagramname` = 'acfhilssy',
                `bulk` = '30', `weight` = '40',
                `recycle_for` = 'Fish,Fish',
                `value` = '124',
                `ediblefood` = '36', `ediblelove` = '8',
                `cancombine` = 'no'
            WHERE
                `monster_items`.`itename` = 'Scales'
            LIMIT 1
        ");

        $this->RenameInventory('Scales', 'Scaly Fish');

        $this->RenameIngredient('psypets_smiths', 'Scales', 'Scaly Fish');
        $this->RenameIngredient('psypets_tailors', 'Scales', 'Scaly Fish');
        $this->RenameIngredient('psypets_crafts', 'Scales', 'Scaly Fish');
        $this->RenameIngredient('psypets_jewelry', 'Scales', 'Scaly Fish');
    }

    public function Down()
    {
        throw new Exception('Can\'t migrate down.');
    }

    public function RenameIngredient($table, $oldName, $newName)
    {
        $recipes = fetch_multiple("SELECT * FROM " . $table . " WHERE ingredients LIKE " . quote_smart('%' . $oldName . '%'));

        foreach($recipes as $recipe)
        {
            $ingredients = explode(',', $recipe['ingredients']);

            for($i = 0; $i < count($ingredients); $i++)
            {
                if($ingredients[$i] == $oldName)
                    $ingredients[$i] = $newName;
            }

            fetch_none("UPDATE monster_recipes SET ingredients=" . quote_smart(implode(',', $ingredients)) . " WHERE idnum=" . $recipe['idnum'] . " LIMIT 1");
        }
    }

    public function RenameInventory($oldName, $newName)
    {
        fetch_none("UPDATE monster_inventory SET itemname=" . quote_smart($newName) . " WHERE itemname=" . quote_smart($oldName));
        fetch_none("UPDATE psypets_basement SET itemname=" . quote_smart($newName) . " WHERE itemname=" . quote_smart($oldName));
    }
}
