<?php

namespace App\Model\Repository;


class AWRRepository extends Repository
{

    protected $table = "pracovnipomer";

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
    // public function getAllMzdovyCom($mzdovyComId)
    // {
    //   return $this->db->table($this->table)->where("fk_mzdovyCom",$mzdovyComId)->fetchAll();
    // }


    public function updateWR($wRData)
    {
      $wR = $this->getWR($wRData->pomerId);
      if($this->canUpdate($wR))
      {
        $wR->update($wRData);
        return 1;
      }
      return 0;
    }
    public function updatePrice($pomerId, $price, $userId)
    {
      $wR = $this->getWR($pomerId);
      if($this->canUpdate($wR))
      {
          $wR->update([
          'price'=>$price,
          'price_timestamp'=>date('Y-m-d H:i:s',time()),
          'price_timestamp_created_by'=>$userId
        ]);
        return 1;
      }
      return 0;
    }
    private function canUpdate($wR){
      if($wR->fk_mzdovyCom)
        return false;
      return true;
    }

    public function getTotalMzdovyCom($mzdovyComId)
    {
      return $this->db->table($this->table)->where("fk_mzdovyCom",$mzdovyComId)->sum("price");
    }
    public function getWRsWithoutMzdovyCom($zamestnanecId)
    {
        return $this->db->table($this->table)->where("fk_mzdovyCom",0)->where("zamestnanecId",$zamestnanecId)->where("price >",0);
    //  $this->db->table($this->table)->where("zamestnanecId",$zamestnanecId)->where("fk_mzdovyCom",0);
    }
    public function addMzdovyComId($pomerId, $mzdovyComId)
    {
      $wR = $this->getWR($pomerId);
      $wR->update([
        'fk_mzdovyCom'=>$mzdovyComId,
      ]);
    }
    public function getAllRozpad($mzdovyComId)
    {
      if(!$mzdovyComId)
        return $this->getAll();
      else {
        return $this->db->table("rozpadnastredisko")->where("FK_MzdovyList",$mzdovyComId)->fetchAll();
      }
    }



}
