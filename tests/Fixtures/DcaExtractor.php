<?php

class DcaExtractor
{
    public static function getInstance()
    {
        return new static();
    }

    public function getRelations()
    {
        return [];
    }
}
