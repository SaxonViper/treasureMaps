<?php

namespace App;


class Helper
{
    public static function weightedRandom($array)
    {
        $totalWeight = 0;
        foreach ($array as $item => $weight) {
            $totalWeight += $weight;
        }

        $random = $totalWeight * rand(1, 1000000) / 1000000;
        $result = null;

        foreach ($array as $item => $weight) {
            $random = $random - $weight;
            if ($random <= 0) {
                $result = $item;
                break;
            }
        }

        return $result;
    }
}