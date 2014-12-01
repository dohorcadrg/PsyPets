<?php
class Migration_13
{
    public function Up()
    {
        // new gathering table; encompasses all gathering-related activities
        fetch_none("
            CREATE TABLE IF NOT EXISTS `psypets_gathering` (
                `idnum` int(10) unsigned NOT NULL,
                `itemname` varchar(64) NOT NULL,
                `activity` varchar(16) NOT NULL,
                `incidental_only` enum('no','yes') NOT NULL DEFAULT 'no',
                `incidental_find_rarity` tinyint(3) unsigned NOT NULL,
                `min_searching_required` smallint(5) unsigned NOT NULL,
                `max_searching_required` smallint(5) unsigned NOT NULL,
                `min_harvesting_required` smallint(5) unsigned NOT NULL,
                `max_harvesting_required` smallint(5) unsigned NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf32;
        ");

        fetch_none("
            ALTER TABLE `psypets_gathering`
            ADD PRIMARY KEY (`idnum`)
        ");

        fetch_none("
            ALTER TABLE `psypets_gathering`
            MODIFY `idnum` int(10) unsigned NOT NULL AUTO_INCREMENT;
        ");

        // things to take special care of in code:
        //   Bird Nest (chance to get as bonus whenever lumberjacking anything)
        //   5 Bamboo, 7 Bamboo, 9 Bamboo
        //   Book of Minerals
        //   Autumn Leaf, LMAOnade
        //   Wax. make sure there's prey for this
        //   "the Copper Tower", "the Silver Tower" and "the Gold Tower"
        //   "the fairies' mustard garden"
        //   "the elves' forest"
        //   "a bee mine"
        //   "strange and mysterious ruins"
        //   shrimp
        //   Tartan, Cellular Peptide Cake with Mint Frosting
        //   what to do about Dye, Tartan, and other intermediate crafts? (Simple Circuit, CPU...)

        // searching + harvesting = 1
        $this->AddGatheringRecord('Fish', 'fish', 100, 0, 2, 1, 1);

        $this->AddGatheringRecord('Fluff', 'hunt', 80, 0, 0, 1, 1);

        $this->AddGatheringRecord('3-Leaf Clover', 'gather', 100, 0, 0, 1, 1);
        $this->AddGatheringRecord('Blueberries', 'gather', 100, 0, 0, 1, 1);
        $this->AddGatheringRecord('Redsberries', 'gather', 90, 0, 0, 1, 1);
        $this->AddGatheringRecord('Maple Leaf', 'gather', 80, 0, 0, 1, 1);
        $this->AddGatheringRecord('Greenish Leaf', 'gather', 80, 0, 0, 1, 1);
        $this->AddGatheringRecord('Tea Leaves', 'gather', 50, 0, 0, 1, 1);
        $this->AddGatheringRecord('Sunflower Seeds', 'gather', 20, 0, 0, 1, 2);
        $this->AddGatheringRecord('Fluff', 'gather', 15, 0, 0, 1, 1, true);
        $this->AddGatheringRecord('Feather', 'gather', 10, 0, 0, 1, 1, true);
        $this->AddGatheringRecord('Rubble', 'gather', 2, 0, 0, 1, 1);

        $this->AddGatheringRecord('Small Rock', 'mine', 100, 0, 0, 1, 1);
        $this->AddGatheringRecord('Small Rock', 'gather', 80, 0, 0, 1, 1);

        $this->AddGatheringRecord('Chalk', 'mine', 80, 0, 0, 1, 1);
        $this->AddGatheringRecord('Clay', 'mine', 80, 0, 0, 1, 1);

        $this->AddGatheringRecord('Wood', 'lumberjack', 0, 0, 1, 1, 1);

        // searching + harvesting = 2
        $this->AddGatheringRecord('Feather', 'hunt', 80, 1, 1, 1, 1);
        $this->AddGatheringRecord('Blood', 'hunt', 60, 1, 1, 1, 1);

        $this->AddGatheringRecord('Gypsum', 'mine', 50, 1, 1, 1, 2);
        $this->AddGatheringRecord('Baking Soda', 'mine', 10, 1, 2, 1, 1);

        $this->AddGatheringRecord('Sour Lime', 'gather', 100, 1, 1, 1, 1);
        $this->AddGatheringRecord('Delicious', 'gather', 100, 1, 1, 1, 1);
        $this->AddGatheringRecord('Peanuts', 'gather', 90, 1, 1, 1, 1);
        $this->AddGatheringRecord('Sugar Beet', 'gather', 90, 1, 1, 1, 1);
        $this->AddGatheringRecord('Arugula', 'gather', 80, 1, 1, 1, 1);
        $this->AddGatheringRecord('Reed', 'gather', 75, 1, 1, 1, 1);
        $this->AddGatheringRecord('Apricot', 'gather', 70, 1, 2, 1, 1);
        $this->AddGatheringRecord('White Radish', 'gather', 60, 1, 1, 1, 1);
        $this->AddGatheringRecord('Peas', 'gather', 50, 1, 1, 1, 1);
        $this->AddGatheringRecord('Mint Leaves', 'gather', 40, 1, 1, 1, 1);
        $this->AddGatheringRecord('Pinecone', 'gather', 40, 1, 1, 1, 1);
        $this->AddGatheringRecord('Pecans', 'gather', 30, 1, 2, 1, 1);
        $this->AddGatheringRecord('Poppy Seeds', 'gather', 20, 1, 2, 1, 2);
        $this->AddGatheringRecord('Wheat', 'gather', 20, 1, 1, 1, 2);

        $this->AddGatheringRecord('Log', 'lumberjack', 0, 1, 2, 1, 2);

        // searching + harvesting = 3
        $this->AddGatheringRecord('Egg', 'hunt', 80, 2, 3, 1, 1);
        $this->AddGatheringRecord('Leather', 'hunt', 70, 2, 3, 1, 1);
        $this->AddGatheringRecord('Raw Milk', 'hunt', 60, 2, 3, 1, 1);

        $this->AddGatheringRecord('Scaly Fish', 'fish', 100, 2, 4, 1, 1);

        $this->AddGatheringRecord('Short Bone', 'gather', 5, 2, 3, 1, 1, true);
        $this->AddGatheringRecord('Short Bone', 'hunt', 5, 2, 3, 1, 1);
        $this->AddGatheringRecord('Short Bone', 'mine', 5, 2, 3, 1, 1, true);

        $this->AddGatheringRecord('Skull', 'hunt', 4, 2, 4, 1, 1);

        $this->AddGatheringRecord('Long Bone', 'gather', 3, 2, 4, 1, 1, true);
        $this->AddGatheringRecord('Long Bone', 'hunt', 3, 2, 4, 1, 1);
        $this->AddGatheringRecord('Long Bone', 'mine', 3, 2, 4, 1, 1, true);

        $this->AddGatheringRecord('Shallot', 'gather', 90, 2, 2, 1, 1);
        $this->AddGatheringRecord('Beet', 'gather', 70, 2, 2, 1, 1);
        $this->AddGatheringRecord('Peppers', 'gather', 60, 2, 2, 1, 2);
        $this->AddGatheringRecord('Ginger', 'gather', 50, 2, 2, 1, 2);
        $this->AddGatheringRecord('Wild Oats', 'gather', 40, 2, 2, 1, 2);
        $this->AddGatheringRecord('Soy Bean', 'gather', 40, 2, 2, 1, 2);
        $this->AddGatheringRecord('Rye', 'gather', 20, 2, 2, 1, 2);

        $this->AddGatheringRecord('Coal', 'mine', 90, 2, 2, 1, 2);
        $this->AddGatheringRecord('Tin', 'mine', 80, 2, 2, 1, 2);
        $this->AddGatheringRecord('Iron', 'mine', 70, 2, 2, 1, 2);
        $this->AddGatheringRecord('Copper', 'mine', 70, 2, 2, 1, 2);

        // searching + harvesting = 4
        $this->AddGatheringRecord('Tentacle', 'fish', 80, 4, 6, 1, 1);

        $this->AddGatheringRecord('Chicken', 'hunt', 90, 3, 3, 1, 1);
        $this->AddGatheringRecord('Steak', 'hunt', 80, 4, 4, 1, 1);
        $this->AddGatheringRecord('Turkey', 'hunt', 60, 3, 3, 1, 1);
        $this->AddGatheringRecord('Talon', 'hunt', 50, 3, 4, 1, 1);

        $this->AddGatheringRecord('Clover Leaf', 'gather', 100, 3, 5, 1, 1);
        $this->AddGatheringRecord('Orange', 'gather', 100, 3, 3, 1, 1);
        $this->AddGatheringRecord('Eggplant', 'gather', 90, 3, 4, 1, 1);
        $this->AddGatheringRecord('Leafy Cabbage', 'gather', 70, 3, 4, 1, 1);
        $this->AddGatheringRecord('Prickly Green', 'gather', 60, 3, 3, 1, 2);
        $this->AddGatheringRecord('Carrot', 'gather', 40, 3, 4, 1, 1);
        $this->AddGatheringRecord('Egg', 'gather', 30, 2, 2, 1, 2, true);
        $this->AddGatheringRecord('Rice', 'gather', 20, 3, 3, 1, 2);
        $this->AddGatheringRecord('Rubber', 'gather', 0, 2, 3, 2, 3); // cannot find incidentally

        // searching + harvesting = 5
        $this->AddGatheringRecord('Black Dye', 'fish', 40, 4, 7, 1, 1);

        $this->AddGatheringRecord('Lard', 'hunt', 80, 4, 5, 1, 1);
        $this->AddGatheringRecord('Speckled Egg', 'hunt', 40, 4, 4, 1, 1);

        $this->AddGatheringRecord('Banana', 'gather', 100, 4, 4, 1, 1);
        $this->AddGatheringRecord('Pamplemousse', 'gather', 80, 4, 4, 1, 1);
        $this->AddGatheringRecord('Tomato', 'gather', 75, 4, 4, 1, 1);
        $this->AddGatheringRecord('Pomegranate', 'gather', 10, 4, 5, 1, 1);
        $this->AddGatheringRecord('Tomato', 'gather', 75, 4, 4, 1, 1);
        $this->AddGatheringRecord('Onion', 'gather', 70, 4, 4, 1, 1);
        $this->AddGatheringRecord('Garlic', 'gather', 70, 4, 4, 1, 1);
        $this->AddGatheringRecord('Mango', 'gather', 40, 4, 4, 1, 1);
        $this->AddGatheringRecord('Papaya', 'gather', 35, 4, 4, 1, 1);
        $this->AddGatheringRecord('Aging Root', 'gather', 30, 4, 6, 1, 2);
        $this->AddGatheringRecord('Wishing Well Blueprint', 'gather', 1, 4, 8, 1, 1, true);

        // searching + harvesting = 6
        $this->AddGatheringRecord('Seaweed', 'fish', 40, 5, 5, 1, 1, true);

        $this->AddGatheringRecord('Pea Pods', 'gather', 100, 5, 5, 1, 1);
        $this->AddGatheringRecord('Coconut', 'gather', 90, 5, 5, 1, 2);
        $this->AddGatheringRecord('Avocado', 'gather', 80, 5, 5, 1, 1);
        $this->AddGatheringRecord('Seaweed', 'gather', 60, 5, 5, 1, 1);
        $this->AddGatheringRecord('Fire Spice', 'gather', 50, 4, 5, 2, 3);
        $this->AddGatheringRecord('Lychee', 'gather', 30, 5, 5, 1, 1);
        $this->AddGatheringRecord('Amethyst Rose', 'gather', 30, 5, 6, 1, 1);
        $this->AddGatheringRecord('Conch Shell', 'gather', 5, 5, 9, 1, 1);
        $this->AddGatheringRecord('Spade', 'gather', 5, 5, 5, 1, 1, true);

        $this->AddGatheringRecord('Silver', 'mine', 60, 5, 5, 1, 2);
        $this->AddGatheringRecord('Azurite', 'mine', 60, 4, 4, 2, 3);

        // searching + harvesting = 7
        $this->AddGatheringRecord('Azuki Bean', 'gather', 90, 6, 6, 1, 1);
        $this->AddGatheringRecord('Olives', 'gather', 60, 6, 7, 1, 1);
        $this->AddGatheringRecord('Coffee Beans', 'gather', 60, 6, 7, 1, 2);
        $this->AddGatheringRecord('Artificial Grapes', 'gather', 20, 6, 6, 1, 1);

        // searching + harvesting = 8
        $this->AddGatheringRecord('Pearl', 'fish', 20, 7, 10, 1, 1);

        $this->AddGatheringRecord('Pork', 'hunt', 100, 7, 7, 1, 1);

        $this->AddGatheringRecord('Potato', 'gather', 100, 7, 7, 1, 1);
        $this->AddGatheringRecord('Corn', 'gather', 80, 7, 7, 1, 1);
        $this->AddGatheringRecord('Yam', 'gather', 70, 7, 7, 1, 1);
        $this->AddGatheringRecord('Spaghetti Squash', 'gather', 60, 7, 7, 1, 2);
        $this->AddGatheringRecord('Pineapple', 'gather', 50, 7, 7, 1, 1);
        $this->AddGatheringRecord('Coriander', 'gather', 40, 7, 7, 1, 1);
        $this->AddGatheringRecord('4-Leaf Clover', 'gather', 20, 7, 7, 1, 1);
        $this->AddGatheringRecord('Silkworm Egg', 'gather', 10, 7, 7, 1, 1, true);

        $this->AddGatheringRecord('Large Rock', 'mine', 80, 6, 6, 2, 2);
        $this->AddGatheringRecord('Large Rock', 'gather', 60,  6, 6, 2, 2);

        // searching + harvesting = 9
        $this->AddGatheringRecord('Eye', 'hunt', 5, 8, 9, 1, 1);
        $this->AddGatheringRecord('Saliva', 'hunt', 5, 8, 8, 1, 1);

        $this->AddGatheringRecord('Zinc', 'mine', 100, 8, 9, 1, 2);

        $this->AddGatheringRecord('Sugardew', 'gather', 80, 8, 8, 1, 1);
        $this->AddGatheringRecord('Speckled Egg', 'gather', 20, 8, 8, 1, 2, true);

        $this->AddGatheringRecord('Skull', 'mine', 10, 8, 8, 1, 1, true);
        $this->AddGatheringRecord('Skull', 'gather', 5, 8, 8, 1, 1, true);

        // searching + harvesting = 10
        $this->AddGatheringRecord('Black Leather', 'hunt', 70, 9, 10, 1, 1);

        $this->AddGatheringRecord('Gold', 'mine', 50, 9, 9, 1, 2);
        $this->AddGatheringRecord('Small Giamond', 'mine', 40, 9, 10, 1, 2);

        $this->AddGatheringRecord('Artichoke', 'gather', 90, 9, 10, 1, 1);
        $this->AddGatheringRecord('Watermelon', 'gather', 70, 9, 9, 1, 1);
        $this->AddGatheringRecord('Date', 'gather', 50, 9, 9, 1, 1);
        $this->AddGatheringRecord('2-Leaf Clover', 'gather', 20, 9, 9, 1, 1);

        $this->AddGatheringRecord('Red Wood', 'lumberjack', 0, 9, 10, 1, 1);
        $this->AddGatheringRecord('Red Log', 'lumberjack', 0, 9, 11, 1, 2);

        // searching + harvesting = 12
        $this->AddGatheringRecord('Gargantuan Egg', 'hunt', 60, 11, 12, 1, 1);
        $this->AddGatheringRecord('Venom', 'hunt', 10, 11, 14, 1, 1);

        $this->AddGatheringRecord('Slime', 'fish', 20, 11, 13, 1, 1);

        $this->AddGatheringRecord('Poison Ivy', 'gather', 50, 11, 11, 1, 2);
        $this->AddGatheringRecord('Chanterelle', 'gather', 30, 11, 11, 1, 1);
        $this->AddGatheringRecord('Moldavite', 'gather', 20, 11, 13, 1, 3);

        // searching + harvesting = 14
        $this->AddGatheringRecord('Gossamer', 'hunt', 10, 13, 15, 1, 1);

        $this->AddGatheringRecord('Eye', 'fish', 5, 13, 16, 1, 1);

        $this->AddGatheringRecord('Minipalm', 'gather', 20, 13, 13, 1, 1, true);
        $this->AddGatheringRecord('Potted Orange Tree', 'gather', 20, 13, 13, 1, 1, true);
        $this->AddGatheringRecord('Cilantro', 'gather', 1, 13, 13, 1, 1);

        // searching + harvesting = 16
        $this->AddGatheringRecord('Unicorn Horn', 'hunt', 10, 15, 17, 1, 1);
        $this->AddGatheringRecord('Phoenix Down', 'hunt', 10, 15, 16, 1, 1);

        $this->AddGatheringRecord('Whole Pumpkin', 'gather', 80, 15, 15, 1, 1);
        $this->AddGatheringRecord('Misshapen Tangelo', 'gather', 60, 15, 16, 1, 1);
        $this->AddGatheringRecord('Peppeño', 'gather', 60, 15, 15, 1, 2);
        $this->AddGatheringRecord('Evilberries', 'gather', 30, 14, 14, 2, 3);
        $this->AddGatheringRecord('Blue Egg', 'gather', 10, 15, 15, 1, 2, true);
        $this->AddGatheringRecord('5-Leaf Clover', 'gather', 10, 15, 15, 1, 1);

        $this->AddGatheringRecord('Dark Wood', 'lumberjack', 0, 15, 16, 1, 2);

        // searching + harvesting = 18
        $this->AddGatheringRecord('Heart', 'hunt', 15, 17, 18, 1, 1);

        $this->AddGatheringRecord('Conch Shell', 'fish', 10, 17, 21, 1, 1);

        $this->AddGatheringRecord('Cinnamon', 'gather', 70, 18, 18, 1, 2);
        $this->AddGatheringRecord('Sesame Seeds', 'gather', 60, 17, 17, 1, 1);
        $this->AddGatheringRecord('Tamarind', 'gather', 40, 17, 18, 1, 1);
        $this->AddGatheringRecord('Sesame Flower', 'gather', 1, 17, 17, 1, 1, true);

        $this->AddGatheringRecord('Jade', 'mine', 40, 17, 17, 1, 3);
        $this->AddGatheringRecord('Bixbite', 'mine', 1, 16, 16, 2, 5);

        // searching + harvesting = 20
        $this->AddGatheringRecord('Black Pearl', 'fish', 3, 19, 22, 1, 1);

        $this->AddGatheringRecord('Dark Gossamer', 'hunt', 5, 19, 20, 1, 1);

        $this->AddGatheringRecord('Really Enormously Tremendous Rock', 'mine', 60, 15, 17, 5, 9);
        $this->AddGatheringRecord('Really Enormously Tremendous Rock', 'gather', 40, 15, 17, 5, 9);

        $this->AddGatheringRecord('Pyrestone', 'mine', 40, 17, 17, 2, 3);
        $this->AddGatheringRecord('Cinnabar', 'mine', 40, 18, 18, 1, 2);

        $this->AddGatheringRecord('Acorn', 'gather', 40, 19, 19, 1, 2);
        $this->AddGatheringRecord('Mushroom', 'gather', 20, 19, 20, 1, 1);
        $this->AddGatheringRecord('Wooden Cross', 'gather', 10, 18, 18, 1, 2, true);

        // searching + harvesting = 25
        $this->AddGatheringRecord('White Leather', 'fish', 80, 24, 27, 1, 1);

        $this->AddGatheringRecord('White Leather', 'hunt', 80, 24, 27, 1, 1);

        $this->AddGatheringRecord('Lotus Fruit', 'gather', 80, 23, 24, 2, 2);
        $this->AddGatheringRecord('White Lotus', 'gather', 40, 24, 24, 1, 1);
        $this->AddGatheringRecord('Black Lotus', 'gather', 10, 24, 28, 1, 1);

        $this->AddGatheringRecord('Yggdrasil Branch', 'lumberjack', 10, 24, 26, 1, 3);

        $this->AddGatheringRecord('Mithryl', 'mine', 10, 24, 26, 1, 3);

        // searching + harvesting = 30
        $this->AddGatheringRecord('Radioactive Material', 'mine', 10, 28, 29, 2, 6);

        // drop old gathering-related tables
        fetch_none('DROP TABLE monster_prey');
        fetch_none('DROP TABLE psypets_locations');
    }

    public function Down()
    {
        fetch_none('DROP TABLE psypets_gathering');
    }

    /**
     * @param string $itemName
     * @param string $type
     * @param int $incidentalRate
     * @param int $minSearch
     * @param int $maxSearch
     * @param int $minHarvest
     * @param int $maxHarvest
     * @param bool $incidentalOnly
     */
    public function AddGatheringRecord($itemName, $type, $incidentalRate, $minSearch, $maxSearch, $minHarvest, $maxHarvest, $incidentalOnly = false)
    {
        fetch_none('
            INSERT INTO psypets_gathering (itemname, activity, incidental_only, incidental_find_rarity, min_searching_required, max_searching_required, min_harvesting_required, max_harvesting_required)
            VALUES (
                ' . quote_smart($itemName) . ',
                ' . quote_smart($type) . ',
                ' . quote_smart($incidentalOnly ? 'yes' : 'no') . ',
                ' . (int)$incidentalRate . ',
                ' . (int)$minSearch . ', ' . (int)$maxSearch . ',
                ' . (int)$minHarvest . ', ' . (int)$maxHarvest . '
            )
        ');
    }
}
