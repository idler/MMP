<?php

class Output
{
    static function verbose($msg, $level = 1)
    {
        if (intval(Helper::get('verbose')) >= $level) {
            echo $msg, PHP_EOL;
        }
    }

    static function error($msg)
    {
        fwrite(STDERR, $msg.PHP_EOL);
    }
}