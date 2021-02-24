<?php

namespace App\Model\Repository;


class MzdovyParRepository extends Repository
{

    protected $table = "mzdovylistPar";

    public function getAll()
    {
        return $this->db->table($this->table)->fetchAll();
    }
    public function getAllZamestnanec($zamestnanecId)
    {
      if(!$zamestnanecId)
        return $this->getAll();
      else {
          return $this->db->table($this->table)->where("zamestnanecId",$zamestnanecId)->fetchAll();
      }
    }
    public function getAllPomer($zamestnanecId)
    {
        return $this->db->table($this->table)->where("zamestnanecId",$zamestnanecId)->fetchAll();
    }
    public function getAllMzdovy($mzdovyComId)
    {
        return $this->db->table($this->table)->where("mzdovylistComId",$mzdovyComId)->fetchAll();
    }
    public function addMzdovyPar($data)
    {
        return $this->db->table($this->table)->insert($data);
    }
    public function getMzdovyPar($mzdovyParId)
    {
        return $this->db->table($this->table)->get($mzdovyParId);
    }
    public function deleteMzdovyPar($mzdovyParId)
    {
        $this->db->table('order')->where('fk_mzdovyPar', $mzdovyParId)->update(['fk_mzdovyPar'=>0]);
        return $this->db->table($this->table)->where('mzdovyParId', $mzdovyParId)->delete();
    }
    public function makeMzdovyPar($zamestnanecId)
    {
        $data["zamestnanecId"] = $zamestnanecId;
        return $this->addMzdovyPar($data);
      }




}
