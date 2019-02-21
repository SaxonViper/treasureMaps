<?php

namespace App\Components;

use App\TreasureFieldMap as Map;
use App\TreasureFieldMapCell as Cell;

class MapDrawer
{
    private $map;
    private $cellData;

    public function __construct(Map $map)
    {
        $this->map = $map;
    }

    public function build()
    {
        $this->map->fillCellTable();
        // $this->map->addCellBorders();
        return $this->map->getRenderableTable();
    }
}