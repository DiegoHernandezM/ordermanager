<?php

namespace App\Helpers;


class StringHelpers
{
    public function after_last ($separator, $inthat)
    {
        if (!is_bool($this->strrevpos($inthat, $separator)))
            return substr($inthat, $this->strrevpos($inthat, $separator)+strlen($separator));
    }

    public function strrevpos($instr, $needle)
    {
        $rev_pos = strpos (strrev($instr), strrev($needle));
        if ($rev_pos===false) return false;
        else return strlen($instr) - $rev_pos - strlen($needle);
    }

    public function between ($first, $that, $inthat)
    {
        return $this->before ($that, $this->after($first, $inthat));
    }

    public function before ($separator, $inthat)
    {
        return substr($inthat, 0, strpos($inthat, $separator));
    }

    public function after ($firts, $inthat)
    {
        if (!is_bool(strpos($inthat, $firts)))
            return substr($inthat, strpos($inthat,$firts)+strlen($firts));
    }
}