<?php

if (!function_exists('truncate2')) {
    function truncate2($num) {
        return floor($num * 100) / 100;
    }
}