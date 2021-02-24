<?php

namespace App\Model\Repository;


class HlaseniRepository extends Repository
{

    protected $table = "hlaseni";



    public function getName($hlaseniId)
    {
        return $this->db->table($this->table)->get($hlaseniId)->jmeno;
    }
    public function getHlaseniNames()
    {
        $table = $this->db->table($this->table);
        return $table->fetchPairs("hlaseniId","jmeno");
    }
    public function addHlaseni($data)
    {
        return $this->db->table($this->table)->insert($data);
    }

}
