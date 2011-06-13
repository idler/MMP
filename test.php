#!/usr/bin/env php
<?php

if (PHP_OS == 'WINNT')
{
  system('php.exe -f '.dirname(__FILE__).'\\tr\\bin\\limb_unit.php '.dirname(__FILE__).'\\cases\\*.php');
}
else
{
  system('/usr/bin/env php '.dirname(__FILE__).'/tr/bin/limb_unit.php '.dirname(__FILE__).'/cases/');
}

