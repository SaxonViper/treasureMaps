<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TreasureFieldMap as Map;

class TreasureFieldMapCell extends Model
{
    const TYPE_NONE      = 'none';
    const TYPE_JUNGLE    = 'jungle';
    const TYPE_DESERT    = 'desert';
    const TYPE_MOUNTAIN  = 'mountain';

    const TYPE_FLAG       = 'flag';
    const TYPE_CHEST      = 'chest';
    const TYPE_WATERING   = 'watering';
    const TYPE_GUARD      = 'guard';
    const TYPE_VILLAGE    = 'village';

    const BORDER_BOTTOM = 'bottom';
    const BORDER_LEFT   = 'left';
    const BORDER_TOP    = 'top';
    const BORDER_RIGHT  = 'right';

    const BORDER_TYPE_RIVER = 'river';
    const BORDER_TYPE_EDGE  = 'edge';

    /** @var Map Объект карты, к которому принадлежит яейка, чтобы постоянно не искать */
    protected $belongedMap = null;

    //
    protected $fillable = [
        'map_id', 'posx', 'posy', 'type', 'object_param', 'is_existing'
    ];

    // Границы ячейки - карта или вода
    protected $borders = [];

    public function map()
    {
        return $this->hasOne('App\TreasureFieldMap', 'map_id');
    }

    /**
     * @return TreasureFieldMap
     */
    protected function getMap()
    {
        return $this->belongedMap ?: $this->map()->get()->first();
    }

    public function setBelongedMap(Map $map)
    {
        $this->belongedMap = $map;
    }

    /**
     * @return TreasureFieldMapCell  Находим ячейку слева
     */
    public function toLeft()
    {
        return $this->getMap()->getCellByIndex($this->posy, $this->posx - 1);
    }

    /**
     * @return TreasureFieldMapCell  Находим ячейку справа
     */
    public function toRight()
    {
        return $this->getMap()->getCellByIndex($this->posy, $this->posx + 1);
    }

    /**
     * @return TreasureFieldMapCell  Находим ячейку сверху
     */
    public function toTop()
    {
        return $this->getMap()->getCellByIndex($this->posy - 1, $this->posx);
    }

    /**
     * @return TreasureFieldMapCell  Находим ячейку снизу
     */
    public function toBottom()
    {
        return $this->getMap()->getCellByIndex($this->posy + 1, $this->posx);
    }

    /**
     * Добавляем границу ячейке
     * @param string $side
     * @param string $type
     */
    public function addBorder($side, $type) {
        $this->borders[$side] = $type;
    }

    /**
     * @return array
     */
    /*public function getBorders()
    {
        return $this->borders;
    }*/

    /**
     * Возвращает данные клитке в виде для вывода
     * @return array
     */
    public function toRenderableArray()
    {
        return [
            'posx' => $this->posx,
            'posy' => $this->posy,
            'is_existing' => $this->is_existing,
            'type' => $this->type,
            // 'object' => $this->object,
            // 'surface' => $this->surface,
            'object_param' => $this->object_param,
            // 'borders' => $this->borders
        ];
    }

}
