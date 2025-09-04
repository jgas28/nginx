<?php
// app/Services/UtilityService.php

namespace App\Services;

class UtilityService
{
    public function truncate2($num)
    {
        return floor($num * 100) / 100;
    }
}
