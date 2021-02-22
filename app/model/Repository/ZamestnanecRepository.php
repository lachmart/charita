<?php

namespace App\Model\Repository;


class ZamestnanecRepository extends Repository
{

    protected $table = "zamestnanec";



    public function getName($zamestnanecId)
    {
        return $this->db->table($this->table)->get($zamestnanecId)->jmeno;
    }
    public function getZamestnanecNames()
    {
        $table = $this->db->table($this->table);
        return $table->fetchPairs("zamestnanecId","jmeno");
    }

}
