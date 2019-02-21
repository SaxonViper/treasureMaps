<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    const STATUS_NOT_FINISHED = 0;
    const STATUS_FINISHED = 1;

    //
    protected $table = 'matches';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'date',
        'status',
        'stats_json'
    ];

    /**
     * @param $stats
     */
    public function setStats($stats)
    {
        if (is_array($stats)) {
            $stats = json_encode($stats);
        }

        $this->stats_json = $stats;
    }
}
