<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TreasureFieldMap;
use App\TreasureFieldMapCell;
use App\Components\MapDrawer;
use App\Helper;

class TreasureMapController extends Controller
{
    //
    public function listAll()
    {
        $mapsQuery = TreasureFieldMap::query();
        $mapsQuery->where('is_deleted', false);
        $result = $mapsQuery->get();

        $maps = [];
        foreach ($result as $map) {
            $maps[] = [
                'id' => $map->id,
                'title' => $map->getTitle(),
                'width' => $map->getWidth(),
                'height' => $map->getHeight(),
            ];
        }

        return $maps;
    }

    public function createOneTest()
    {
        $map = new TreasureFieldMap();
        $map->fill([
            'width' => 5,
            'height' => 5,
            'title' => 'Test dumb map',
            'cell_barriers' => [
                [
                    'cells' => [
                        [1, 3], [1, 4]
                    ],
                    'type' => 'river'
                ],
                [
                    'cells' => [
                        [2, 1], [3, 1]
                    ],
                    'type' => 'river'
                ]
            ]
        ]);

        $map->save();

        return $map->toArray();
    }

    public function createCell()
    {
        $cell = new TreasureFieldMapCell();
        $cell->fill([
            'map_id' => 1,
            'posx' => 1,
            'posy' => 1,
            'type' => TreasureFieldMapCell::TYPE_JUNGLE,
        ]);
        $cell->save();

        $cell = new TreasureFieldMapCell();
        $cell->fill([
            'map_id' => 1,
            'posx' => 1,
            'posy' => 2,
            'type' => TreasureFieldMapCell::TYPE_FLAG,
            'object_param' => 1
        ]);
        $cell->save();

        return TreasureFieldMapCell::all();
    }

    public function showCells($map_id) {
        $map = TreasureFieldMap::find($map_id);
        /** @var TreasureFieldMap $map */

        if (!$map) {
            echo 'Неудачно получилось';
        } else {
            // Вывод фигни
            $cells = $map->cells()->get();
            foreach ($cells as $cell) {
                echo $cell->posx, $cell->posy, $cell->type;
            }
        }
        echo 'FUCK!';
    }

    /**
     * @param $map_id
     * @return array
     * @throws \Exception
     */
    public function getMapData($map_id) {
        $map = TreasureFieldMap::find($map_id);
        /** @var TreasureFieldMap $map */

        if (!$map) {
            throw new \Exception('Не найдена карта с указанным ID');
        }

        $drawer = new MapDrawer($map);
        return $drawer->build();

    }

    /**
     * @return string
     */
    public function generateFakeMap() {
        $width = rand(5, 8);
        $height = rand(5, 8);
        $title = 'Random map #' . rand(10000, 99999);

        $map = new TreasureFieldMap();
        $map->fill([
            'width' => $width,
            'height' => $height,
            'title' => $title,
            'cell_barriers' => []
        ]);

        $map->save();
        $mapId = $map->id;

        $type_weights = [
            'none' => 40,
            'desert' => 15,
            'jungle' => 15,
            'mountain' => 5,
            'chest' => 8,
            'village' => 3,
            'watering' => 9,
            'flag' => 8,
            'guard' => 7
        ];

        $flagNum = 1;

        $cells = [];
        for ($row = 1; $row <= $height; $row++) {
            for ($col = 1; $col <= $width; $col++) {

                if ($col == 1 || $row == 1 || $col == $width || $row == $height) {
                    $isExisting = Helper::weightedRandom([true => 80, false => 20]);
                } else {
                    $isExisting = true;
                }

                $objectParam = null;
                if ($isExisting) {
                    $type = Helper::weightedRandom($type_weights);
                    if ($type == 'chest') {
                        $objectParam = rand(2, 8) * 5;
                    } elseif ($type == 'flag') {
                        $objectParam = $flagNum++;
                    }
                } else {
                    $type = 'none';
                }

                $cell = new TreasureFieldMapCell();
                $cell->fill([
                    'posx' => $col,
                    'posy' => $row,
                    'type' => $type,
                    'object_param' => $objectParam,
                    'is_existing' => $isExisting,
                    'map_id' => $mapId
                ]);
                $cell->save();
            }
        }

        $riverLength = rand(3, 6);
        $rivers = [];

        // Стартовая позиция
        // Здесь и далее - позиция не клетка, а "узел" между ними, где [1,1] - это снизу справа от первой клетки первого ряда
        do {
            $riverStart = [rand(0, $height), rand(0, $width)];
        } while (($riverStart[0] == 0 || $riverStart[0] == $height) && ($riverStart[1] == 0 || $riverStart[1] == $width));

        for ($i = 0; $i < $riverLength; $i++) {
            // Следующий узел находим рандомно по соседству
            $direction = rand(1, 4);
            $inverse = false;
            if ($direction == 1 && $riverStart[0] > 0) {
                $riverEnd = [$riverStart[0] - 1, $riverStart[1]];
                $riverBelongsToCell = $riverStart;
                $isHorizontal = false;
            } elseif ($direction == 2 && $riverStart[0] < $height) {
                $riverEnd = [$riverStart[0] + 1, $riverStart[1]];
                $riverBelongsToCell = $riverEnd;
                $isHorizontal = false;
            } elseif ($direction == 3 && $riverStart[1] > 0) {
                $riverEnd = [$riverStart[0], $riverStart[1] - 1];
                $isHorizontal = true;
                $riverBelongsToCell = $riverStart;
            } elseif ($direction == 4 && $riverStart[1] < $width) {
                $riverEnd = [$riverStart[0], $riverStart[1] + 1];
                $riverBelongsToCell = $riverEnd;
                $isHorizontal = true;
            } else {
                continue;
            }

            // Теперь из двух узлов мы должны получить ячейку, которой "принадлежит" река
            $rivers[] = [
                'row' => $riverBelongsToCell[0],
                'col' => $riverBelongsToCell[1],
                'type' => 'river',
                'side' => $isHorizontal ? 'bottom' : 'right'
            ];

            $riverStart = $riverEnd;
        }

        $map->cell_barriers = $rivers;
        $map->save();

        return "Fake map {$title} created. ID = {$mapId}";

    }

    public function saveMapData($mapId) {
        $request = Request::capture();
        $cellData = $request->get('cell_data');
        $width = $request->get('width', 0);
        $height = $request->get('height', 0);
        $rivers = $request->get('rivers', []);
        $title = $request->get('title', 'Noname map #' . rand(10000, 99999));
        if ($mapId > 0) {
            // Старую карту "резервируем", т.е. удаляем
            $oldMap = TreasureFieldMap::find($mapId);
            if ($oldMap) {
                $oldMap->is_deleted = true;
                $oldMap->save();
            }
        } else {
            $oldMap = null;
        }

        $newMap = new TreasureFieldMap();
        $newMap->fill([
            'width' => $width,
            'height' => $height,
            'title' => $title,
            'cell_barriers' => $rivers
        ]);
        $newMap->save();

        // Теперь ячейки карты
        $actualWidth = 0;
        $actualHeight = count($cellData);

        foreach ($cellData as $rowNumber => $row) {
            $actualWidth = max($actualWidth, count($row));
            foreach ($row as $colNumber => $cell) {
                // todo - часть ячеек может быть пустая, если есть с краёв есть пустые ряды или колонки, то надо будет удалять
                $mapCell = new TreasureFieldMapCell();
                $mapCell->fill([
                    'posx' => $colNumber,
                    'posy' => $rowNumber,
                    'is_existing' => $cell['is_existing'] ?? (bool)$cell['type'],
                    'object_param' => $cell['object_param'] ?? null,
                    'type' => $cell['type'] ?? TreasureFieldMapCell::TYPE_NONE ,
                    'map_id' => $newMap->id
                ]);
                $mapCell->save();
            }
        }

        if ($height != $actualHeight || $width != $actualWidth) {
            $newMap->width = $actualWidth;
            $newMap->height = $actualHeight;
            $newMap->save();
        }

        return ([
            'success' => true,
            'map_id'  => $newMap->id
        ]);
    }

    public function deleteMap($mapId)
    {
        $map = TreasureFieldMap::query()->find($mapId);
        if (!$map) {
            throw new \Exception('Не найдена карта с указанным ID');
        }

        $map->is_deleted = true;
        $map->save();

        return [
            'success' => true
        ];
    }

}
