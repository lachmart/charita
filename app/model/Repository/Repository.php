<?php

namespace App\Model\Repository;

use Nette;
use Nette\SmartObject;

abstract class Repository
{
    /** @var Nette\Database\Context */
    protected $db;

    protected $table = "mzdovylist";

    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
    }
    public function getAll()
    {
        return $this->db->table($this->table)->fetchAll();
    }
    public function add($data)
    {
        return $this->db->table($this->table)->insert($data);
    }
    public function get($zamestnanecId)
    {
        return $this->db->table($this->table)->get($zamestnanecId);
    }
    public function delete($zamestnanecId)
    {
        return $this->db->table($this->table)->get($zamestnanecId)->delete();
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table)
    {
        $this->table = $table;
    }
}
