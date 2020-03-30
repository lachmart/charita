<?php

namespace App\Model\Repository;

use Nette;
use Nette\SmartObject;

abstract class Repository
{
    /** @var Nette\Database\Context */
    protected $db;

    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
    }
}
