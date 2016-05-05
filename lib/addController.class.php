<?php

/**
 * Adds existing database to existing version system
 * And keep alive existing data
 *
 * @author  Arbuzov <info@whitediver.com>
 *
 */
class addController extends AbstractController
{
    public function runStrategy()
    {
        Helper::initVersionTable();
    }
}