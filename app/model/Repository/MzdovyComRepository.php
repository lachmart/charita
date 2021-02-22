<?php

namespace App\Model\Repository;


class MzdovyComRepository extends Repository
{

    protected $table = "mzdovylistCom";

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
    public function addMzdovyCom($data)
    {
        return $this->db->table($this->table)->insert($data);
    }
    public function getMzdovyCom($mzdovyComId)
    {
        return $this->db->table($this->table)->get($mzdovyComId);
    }
    public function deleteMzdovyCom($mzdovyComId)
    {
        $this->db->table('order')->where('fk_mzdovyCom', $mzdovyComId)->update(['fk_mzdovyCom'=>0]);
        return $this->db->table($this->table)->where('mzdovyComId', $mzdovyComId)->delete();
    }
    public function makeMzdovyCom($zamestnanecId)
    {
        $data["zamestnanecId"] = $zamestnanecId;
        return $this->addMzdovyCom($data);
      }




}
