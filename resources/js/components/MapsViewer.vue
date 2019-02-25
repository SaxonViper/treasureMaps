<template>
    <div class="container">
        <button class="btn btn-success" v-if="!viewingMap" @click.prevent="loadMaps">Список карт</button>
        <button class="btn btn-warning" v-else @click.prevent="backToList">Обратно к списку</button>

        <div id="mapSelectBlock" v-if="!viewingMap">
            <div id="mapsList">
                <div class="mapListItem" v-for="map in mapsList" v-bind:data-id="map.id" :key="map.id">
                    <a class="mapListItemLink" href="" @click.prevent="showMap(map.id)">{{map.title}}</a>
                </div>
            </div>

            <button class="btn btn-success createMap" @click.prevent="createNewMap">Новая карта</button>
        </div>

        <div id="mapDetails" v-else>
            <div class="mapViewHeader">
                <input v-model="mapData.title" type="text" class="mapEditTitle" v-if="isEditing">
                <span v-else>{{mapData.title}}</span>
            </div>
            <div class="mapViewBody">
                <table class="mapViewTable" ref="mapViewTable">
                    <tr v-for="(cellsRow, rowNumber) in mapData.cells">
                        <td v-for="(cell, colNumber) in cellsRow" v-bind:class="[getCellClasses(cell), {editable: editButtonChecked}]" @click="editTableCell(rowNumber, colNumber)">
                            <div v-bind:class="['mapViewObject']">
                                {{getCellContent(cell)}}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="editMapWrapper" v-if="isEditable">
                <button class="editMapStart btn btn-primary" v-if="!isEditing" @click="isEditing = true">Редактировать</button>

                <div classs="editMapPanel" v-else>
                    <div class="mapTypeButtons">
                        <div v-for="(editObj, code) in editObjects" :title="editObj.title" @click="clickEditButton(code)"
                             v-bind:class="[{
                                checked: editButtonChecked === code
                             }, 'mapTypeButton', 'type-' + code]"

                        >
                        </div>
                    </div>

                    <div class="addDeleteRowsButtons">
                        <div class="addDeleteRowTop">
                            <button class="addRow" @click.prevent="addRow('top')">+</button>
                            <button class="moveRow" @click.prevent="slipCells('top')"></button>
                            <button class="deleteRow" @click.prevent="deleteRow('top')">-</button>
                        </div>
                        <div class="addDeleteRowLeft">
                            <button class="addRow" @click.prevent="addRow('left')">+</button>
                            <button class="moveRow" @click.prevent="slipCells('left')"></button>
                            <button class="deleteRow" @click.prevent="deleteRow('left')">-</button>
                        </div>
                        <div class="addDeleteRowRight">
                            <button class="addRow" @click.prevent="addRow('right')">+</button>
                            <button class="moveRow" @click.prevent="slipCells('right')"></button>
                            <button class="deleteRow" @click.prevent="deleteRow('right')">-</button>
                        </div>
                        <div class="addDeleteRowBottom">
                            <button class="addRow" @click.prevent="addRow('bottom')">+</button>
                            <button class="moveRow" @click.prevent="slipCells('bottom')"></button>
                            <button class="deleteRow" @click.prevent="deleteRow('bottom')">-</button>
                        </div>
                    </div>

                    <div class="editMapSaveResetBtns">
                        <div class="editMapSave btn btn-success" @click="saveEditing">Сохранить</div>
                        <div class="editMapReset btn btn-danger" @click="cancelEditing">Отмена</div>
                    </div>
                </div>
            </div>

            <button class="btn btn-primary mapToImage" @click.prevent="mapToImage">Изображение</button>
            <div ref="mapImageContainer">
                <!-- <img class="mapGeneratedImage" :src="mapImageUrl" @click.prevent="downloadImage"/> -->
                <a :href="mapImageUrl" :download="mapImageFilename" style="display: none" ref="downloadMapImage"></a>
            </div>
        </div>
    </div>
</template>

<script>
    /*import VueHtml2Canvas from 'vue-html2canvas';
    Vue.use(VueHtml2Canvas);*/
    import html2canvas from 'html2canvas';

    export default {
        data() {
            return {
                mapsList: {},
                viewingMap: null,
                mapData: {},
                isEditable: true,
                isEditing: false,
                editObjects: {
                    empty: {
                        type: 'empty',
                        title: 'Нет клетки',
                        code: 'empty'
                    },
                    none: {
                        type: 'object',
                        title: 'Пустая клетка',
                        code: 'none'
                    },
                    jungle: {
                        type: 'object',
                        title: 'Джунгли',
                        code: 'jungle'
                    },
                    desert: {
                        type: 'object',
                        title: 'Пустыня',
                        code: 'desert'
                    },
                    mountain: {
                        type: 'object',
                        title: 'Горы',
                        code: 'mountain'
                    },
                    flag: {
                        type: 'object',
                        title: 'Флаг старта',
                        code: 'flag'
                    },
                    chest: {
                        type: 'object',
                        title: 'Сундук',
                        code: 'chest'
                    },
                    watering: {
                        type: 'object',
                        title: 'Водопой',
                        code: 'watering'
                    },
                    village: {
                        type: 'object',
                        title: 'Деревня',
                        code: 'village'
                    },
                    guard: {
                        type: 'object',
                        title: 'Охрана',
                        code: 'guard'
                    },
                    river_bottom: {
                        type: 'river',
                        title: 'Река (снизу от клетки)',
                    },
                    river_right: {
                        type: 'river',
                        title: 'Река (справа от клетки)',
                    }

                },
                editButtonChecked: null,
                mapImageUrl: null
            }
        },
        computed: {
            cellBorders() {
                // let borders = this.mapData.rivers;
                let borders = JSON.parse(JSON.stringify(this.mapData.rivers));

                // Тут рассчитываем границы ячеек - карты и рек
                for (let rowNumber in this.mapData.cells) {
                    for (let colNumber in this.mapData.cells[rowNumber]) {
                        let cell = this.mapData.cells[rowNumber][colNumber];

                        if (cell && cell.is_existing) {
                            let cellNeighbours = {
                                left: this.getCellNeighbour(cell, 'left'),
                                right: this.getCellNeighbour(cell, 'right'),
                                top: this.getCellNeighbour(cell, 'up'),
                                bottom: this.getCellNeighbour(cell, 'down')
                            };

                            // Если нет соседа с какой-либо из сторон, то добавляем границу карты
                            for (let side in cellNeighbours) {
                                if (!cellNeighbours[side] || !cellNeighbours[side].is_existing) {
                                    borders.push({
                                        row: rowNumber,
                                        col: colNumber,
                                        type: 'edge',
                                        side: side
                                    });
                                }
                            }
                        }
                    }
                }

                return borders;
            },
            mapImageFilename() {
                let filename = (this.mapData.title ? this.mapData.title: 'Без имени') + '.jpg';
                if (this.viewingMap && this.viewingMap !== 'new') {
                    filename = (this.viewingMap.toString() + '-') + filename;
                }
                return filename;
            }

        },
        methods: {
            loadMaps() {
                axios.get('/treasure_maps').then(response => {
                    if (response.data) {
                       this.mapsList = response.data;
                    }
                })
            },
            backToList() {
                this.viewingMap = null;
                this.loadMaps();
            },
            showMap(mapId) {
                axios.get('/treasure_maps/' + mapId).then(response => {
                    if (response.data) {
                        this.mapData = response.data;
                    }
                    this.viewingMap = mapId;
                });
            },
            getCellClasses(cell) {
                let cellClass = [];
                // Логика отрисовки ячейки в зависимости от данных по ней
                if (!cell.is_existing) {
                    cellClass.push('empty');
                } else {
                    // Тип поверхности
                    if (cell.type) {
                        cellClass.push('type-' + cell.type);
                    }

                    // Типы границ
                    this.cellBorders.forEach(borderData => {
                        if (borderData.row == cell.posy && borderData.col == cell.posx) {
                            cellClass.push('border-' + borderData.side + '-' + borderData.type);
                        }
                    });

                    /*let borders = cell.borders;
                    if (borders) {
                        // Собираем классы: для записи ['left' => 'river] добавим класс border-left-river
                        for (let borderSide in borders) {
                            cellClass.push('border-' + borderSide + '-' + borders[borderSide]);
                        }
                    }*/


                }
                return cellClass.join(' ');
            },
            getCellContent(cell) {
                return cell.type === 'chest' ? cell.object_param : '';
            },
            clickEditButton(code) {
                // Если кнопка уже выбрана, то отключаем, иначе включаем
                this.editButtonChecked = (this.editButtonChecked === code) ? null : code;
            },
            editTableCell(rowNumber, colNumber) {
                if (this.editButtonChecked) {

                    if (this.editObjects[this.editButtonChecked].type === 'object') {
                        if (this.editButtonChecked === 'chest') {
                            let chestAmount = parseInt(prompt('Введите сумму в сундуке'));
                            if (!chestAmount || chestAmount < 5 || chestAmount > 100) {
                                return false;
                            } else {
                                this.mapData.cells[rowNumber][colNumber].object_param = chestAmount;
                            }
                        }
                        // Возможно, ячейку только создали, запишем туда всё
                        this.mapData.cells[rowNumber][colNumber].type = this.editButtonChecked;
                        this.mapData.cells[rowNumber][colNumber].is_existing = true;
                        this.mapData.cells[rowNumber][colNumber].posx = parseInt(colNumber);
                        this.mapData.cells[rowNumber][colNumber].posy = parseInt(rowNumber);
                    } else if (this.editButtonChecked === 'empty') {
                        this.mapData.cells[rowNumber][colNumber].is_existing = false;
                        this.mapData.cells[rowNumber][colNumber].type = 'none';
                    } else if (this.editButtonChecked === 'river_right') {
                        this.addOrRemoveRiver(rowNumber, colNumber, 'right');
                    } else if (this.editButtonChecked === 'river_bottom') {
                        this.addOrRemoveRiver(rowNumber, colNumber, 'bottom');
                    }
                }
            },
            cancelEditing() {
                if (confirm('Это сбросит все изменения. Отменить редактирование?')) {
                    // Отменяем редактирование, возвращаем всё как было
                    this.editButtonChecked = null;
                    this.isEditing = false;

                    if (this.viewingMap !== 'new') {
                        this.showMap(this.viewingMap);
                    } else {
                        this.backToList();
                    }
                }
            },
            saveEditing() {
                // Хардкор, сохраняем наш результат редактирования
                if (confirm('Сохранить изменения в карте?')) {
                    let mapId = (this.viewingMap !== 'new') ? this.viewingMap : 0;
                    axios.post('/treasure_maps/' + mapId, {
                        cell_data: this.mapData.cells,
                        title: this.mapData.title,
                        width: this.mapData.width,
                        height: this.mapData.height,
                        rivers: this.mapData.rivers
                    }).then(response => {
                        if (response.data && response.data.success) {
                            this.viewingMap = response.data.map_id;
                            this.editButtonChecked = null;
                            this.isEditing = false;
                        }
                    });
                }
            },
            getCellByPos(row, col) {
                if (this.mapData.cells[row] === undefined) {
                    return null;
                } else {
                    if (this.mapData.cells[row][col] === undefined) {
                        return null;
                    } else {
                        return this.mapData.cells[row][col];
                    }
                }
            },
            getCellNeighbour(cell, direction) {
                let row = cell.posy;
                let col = cell.posx;
                let result = null;

                if (row && col) {
                    switch (direction) {
                        case 'up':
                            result = this.getCellByPos(row - 1, col);
                            break;

                        case 'down':
                            result = this.getCellByPos(row + 1, col);
                            break;

                        case 'left':
                            result = this.getCellByPos(row, col - 1);
                            break;

                        case 'right':
                            result = this.getCellByPos(row, col + 1);
                            break;
                    }
                }

                return result;
            },
            addOrRemoveRiver(row, col, side) {
                let riverExists = false;
                this.mapData.rivers.forEach((riverData, index) => {
                    if (riverData.row == row && riverData.col == col && riverData.side === side) {
                        // Такая река есть, удаляем
                        riverExists = true;
                        this.mapData.rivers.splice(index, 1);
                    }
                });

                if (!riverExists) {
                    // Реки нет, добавляем
                    this.mapData.rivers.push({
                        row: row,
                        col: col,
                        type: 'river',
                        side: side
                    });
                }
            },
            createNewMap() {
                // Создаём новую карту, пусть её размер будет 5х5
                this.viewingMap = 'new';
                let newMapWidth = 5;
                let newMapHeight = 5;
                let cells = {};

                for (let row = 1; row <= newMapHeight; row++) {
                    let cellsRow = {};
                    for (let col = 1; col <= newMapWidth; col++) {
                        cellsRow[col] = ({
                           posx: col,
                           posy: row,
                           type: 'none',
                           is_existing: true
                        });
                    }
                    cells[row] = cellsRow;
                }

                this.mapData = {
                    title: 'Новая карта',
                    width: newMapWidth,
                    height: newMapHeight,
                    cells: cells,
                    rivers: []
                };
            },
            reindexCells(dx, dy) {
                // Сложная логика - мы меняем у всех ячеек номера, заменяя при этом все отсылки на них (это posx, posy внутри ячеек, а также реки)
                let newCells = {};
                for (let rowNumber in this.mapData.cells) {
                    let newRowNumber = parseInt(rowNumber) + dy;
                    newCells[newRowNumber] = {};
                    for (let colNumber in this.mapData.cells[rowNumber]) {
                        let cell = this.mapData.cells[rowNumber][colNumber];
                        cell.posx = cell.posx + dx;
                        cell.posy = cell.posy + dy;

                        // Вставляем в новый объект по новому адресу
                        newCells[newRowNumber][parseInt(colNumber) + dx] = cell;
                    }
                }

                console.log(newCells);
                this.mapData.cells = newCells;

                this.mapData.rivers.forEach(riverData => {
                    riverData.row = parseInt(riverData.row) + dy;
                    riverData.col = parseInt(riverData.col) + dx;
                });

            },
            addRow(direction) {
                // Добавляем ряд с одной из сторон
                // Если добавляем слева или сверху - нужно переиндексировать ячейки
                switch (direction) {
                    case 'top':
                        this.reindexCells(0, 1);
                        this.addEmptyRow(1);
                        break;

                    case 'bottom':
                        this.addEmptyRow(this.mapData.height + 1);
                        break;

                    case 'left':
                        this.reindexCells(1, 0);
                        this.addEmptyCol(1);
                        break;

                    case 'right':
                        this.addEmptyCol(this.mapData.width + 1);
                }

                console.log(this.mapData.cells);
                console.log(this.mapData.width);
                console.log(this.mapData.height);
            },
            emptyCell(row, col) {
                // Пустая ячейка для вставки в массив
                return {
                    posx: col,
                    posy: row,
                    is_existing: false,
                    type: 'none'
                };
            },
            addEmptyRow(rowNumber) {
                // todo - Проверить надо ли, пустая ли строка?!
                Vue.set(this.mapData.cells, rowNumber, {});
                for (let colNumber = 1; colNumber <= this.mapData.width; colNumber++) {
                    Vue.set(this.mapData.cells[rowNumber], colNumber, this.emptyCell(rowNumber, colNumber));
                }

                this.mapData.height++;
            },
            addEmptyCol(colNumber) {
                for (let rowNumber = 1; rowNumber <= this.mapData.height; rowNumber++) {
                    Vue.set(this.mapData.cells[rowNumber], colNumber, this.emptyCell(rowNumber, colNumber));
                }

                this.mapData.width++;
            },
            deleteRow(direction) {
                // Удаляем ряд с одной из сторон
                // Если удаялем слева или сверху - нужно переиндексировать ячейки
                switch (direction) {
                    case 'top':
                        this.removeCellRow(1);
                        this.reindexCells(0, -1);
                        break;

                    case 'bottom':
                        this.removeCellRow(this.mapData.height);
                        break;

                    case 'left':
                        this.removeCellCol(1);
                        this.reindexCells(-1, 0);
                        break;

                    case 'right':
                        this.removeCellCol(this.mapData.width);
                }

                console.log(this.mapData.cells);
                console.log(this.mapData.width);
                console.log(this.mapData.height);
            },
            removeCellRow(rowNumber) {
                Vue.delete(this.mapData.cells, rowNumber);
                this.mapData.height--;
            },
            removeCellCol(colNumber) {
                for (let rowNumber = 1; rowNumber <= this.mapData.height; rowNumber++) {
                    Vue.delete(this.mapData.cells[rowNumber], colNumber);
                }
                this.mapData.width--;
            },
            slipCells(direction) {
                // "Смещение" вниз - это то же самое, что создать пустую строчку вверху и удалить нижнюю
                this.addRow(this.getOppositeDirection(direction));
                this.deleteRow(direction);
            },
            getOppositeDirection(direction) {
                return {
                    left: 'right',
                    right: 'left',
                    top: 'bottom',
                    bottom: 'top'
                }[direction];
            },
            mapToImage() {
                const el = this.$refs.mapViewTable;
                const options = {
                    type: 'dataURL'
                };
                const imgContainer = this.$refs.mapImageContainer;
                let that = this;
                html2canvas(el, options).then(result => {
                    // this.mapImageUrl = result;
                    // imgContainer.appendChild(result);
                    this.mapImageUrl = result.toDataURL('image/jpg');

                    // Открываем ссылку на скачивание
                    this.$nextTick(function () {
                        // DOM updated
                        const downloadButton = this.$refs.downloadMapImage;
                        downloadButton.click();
                    });

                });
                // this.mapImageUrl = await this.$html2canvas(el, options);
            },
            downloadImage() {
                const downloadButton = this.$refs.downloadMapImage;
                downloadButton.click();
                /*let win = window.open();
                let url = this.mapImageUrl.replace("image/png", "image/octet-stream")
                win.document.write('<iframe src="' + url  + '" frameborder="0" style="border:0; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%;" allowfullscreen></iframe>');*/
            }
        }
    }
</script>