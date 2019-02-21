<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TreasureFieldMapCell as Cell;

class TreasureFieldMap extends Model
{
    //
    protected $fillable = [
        'height', 'width', 'title', 'cell_barriers', 'is_deleted'
    ];

    protected $casts = [
        'cell_barriers' => 'array'
    ];

    /*protected $attributes = [
        'cell_barriers' => []
    ];*/

    // Ячейки в виде массива
    protected $cellTable = [];

    public function cells()
    {
        return $this->hasMany('App\TreasureFieldMapCell', 'map_id');
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setCellTable($table) {
        $this->cellTable = $table;
    }

    public function getCellTable()
    {
        // todo - если нет таблицы ячеек, то находить?
        return $this->cellTable;
    }

    /**
     * @param $row
     * @param $col
     * @return Cell || null
     */
    public function getCellByIndex($row, $col)
    {
        return $this->cellTable[$row][$col] ?? null;
    }

    /**
     * Заполняем данными массив ячеек
     * @return array
     */
    public function fillCellTable()
    {
        $width  = $this->getWidth();
        $height = $this->getHeight();
        $cells = $this->cells()->get();

        // Нам нужно перегнать ячейки в двумерный массив
        $cellData = [];
        foreach ($cells as $cell) {
            /** @var Cell $cell */
            $pos_x = $cell->posx;
            $pos_y = $cell->posy;
            $cell->setBelongedMap($this);

            $cellData[$pos_y][$pos_x] = $cell;
        }

        // Несуществующие ячейки дополним
        for ($row = 1; $row <= $height; $row++) {
            for ($col = 1; $col <= $width; $col++) {
                if (!isset($cellData[$row][$col])) {
                    $fakeCell = new Cell();
                    $fakeCell->fill([
                        'posx' => $col,
                        'posy' => $row,
                        'is_existing' => false
                    ]);
                    $fakeCell->setBelongedMap($this);
                    $cellData[$row][$col] = $fakeCell;
                }
            }
        }

        $this->cellTable = $cellData;
        return $this->cellTable;
    }

    /**
     * Возвращает ячейки в виде, пригодном для рендера
     * @return array
     */
    public function getRenderableTable()
    {
        $width  = $this->getWidth();
        $height = $this->getHeight();

        $cellsTable = [];
        for ($row = 1; $row <= $height; $row++) {
            for ($col = 1; $col <= $width; $col++) {
                $cell = $this->getCellByIndex($row, $col);
                if (!$cell || !$cell->is_existing) {
                    $cellsTable[$row][$col] = [
                        'is_existing' => false
                    ];
                } else {
                    $cellsTable[$row][$col] = $cell->toRenderableArray();
                }
            }
        }

        return [
            'title'  => $this->getTitle(),
            'width'  => $width,
            'height' => $height,
            'cells'  => $cellsTable,
            'rivers' => $this->cell_barriers
        ];
    }

    /*public function addCellBorders()
    {
        for ($row = 1; $row <= $this->height; $row++) {
            for ($col = 1; $col <= $this->width; $col++) {
                $cell = $this->getCellByIndex($row, $col);

                if ($cell->is_existing) {
                    // Соседние ячейки
                    $cellToLeft = $cell->toLeft();
                    $cellToRight = $cell->toRight();
                    $cellToTop = $cell->toTop();
                    $cellToBottom = $cell->toBottom();

                    // Границы карты
                    if (!$cellToLeft || !$cellToLeft->is_existing) {
                        $cell->addBorder(Cell::BORDER_LEFT, Cell::BORDER_TYPE_EDGE);
                    }

                    if (!$cellToRight || !$cellToRight->is_existing) {
                        $cell->addBorder(Cell::BORDER_RIGHT, Cell::BORDER_TYPE_EDGE);
                    }

                    if (!$cellToTop || !$cellToTop->is_existing) {
                        $cell->addBorder(Cell::BORDER_TOP, Cell::BORDER_TYPE_EDGE);
                    }

                    if (!$cellToBottom || !$cellToBottom->is_existing) {
                        $cell->addBorder(Cell::BORDER_BOTTOM, Cell::BORDER_TYPE_EDGE);
                    }

                    // Границы между ячейками. Можно проверить лишь с двух сторон (справа и снизу), если границы описаны корректно. Но по хорошему нужно проверять все 4 стороны
                    if ($barrier = $this->checkBarrierBetweenCells($cell, $cellToRight)) {
                        $cell->addBorder(Cell::BORDER_RIGHT, $barrier);
                    }

                    if ($barrier = $this->checkBarrierBetweenCells($cell, $cellToBottom)) {
                        $cell->addBorder(Cell::BORDER_BOTTOM, $barrier);
                    }
                }
            }
        }
    }*/

    /**
     * Проверяет, есть ли граница между двумя клетками карты
     * @param Cell $cell1  Наша клетка
     * @param Cell|null $cell2  Соседняя клетка
     * @return null|string
     */
    /*public function checkBarrierBetweenCells($cell1, $cell2) {
        $barrierType = null;
        if ($cell1 && $cell2) {
            foreach ($this->cell_barriers as $barrier) {
                $cells = $barrier['cells'];

                if ($cells[0][0] == $cell1->posx && $cells[0][1] == $cell1->posy && $cells[1][0] == $cell2->posx && $cells[1][1] == $cell2->posy) {
                    $barrierType = $barriers['type'] ?? Cell::BORDER_TYPE_RIVER;
                }
            }
        }

        return $barrierType;
    }*/
}
