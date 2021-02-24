<?php

namespace App\Model\Repository;


class PohledRepository extends Repository
{

    protected $table = "mzdovylist";

    public function getAll()
    {
        return $this->db->fetchAll("Select * from mzdovylistPar INNER JOIN pracovnipomer ON mzdovylistPar.pracovniPomerId=pracovnipomer.pomerId LEFT JOIN rozpadnastredisko ON mzdovylistPar.mzdovylistComId=rozpadnastredisko.FK_MzdovyList AND rozpadnastredisko.verze = 0");
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

    public function addPohled($data)
    {
        return $this->db->table($this->table)->insert($data);
    }
    public function getPohled($pomerId)
    {
        return $this->db->table($this->table)->get($pomerId);
    }
    public function updatePohled($pohledData)
    {
      $pohled = $this->getPohled($pohledData->pomerId);
      if($this->canUpdate($pohled))
      {
        $pohled->update($pohledData);
        return 1;
      }
      return 0;
    }
    public function deletePohled($pomerId)
    {
      $pohled = $this->getPohled($pomerId);
      if($this->canUpdate($pohled))
      {
        return $pohled->delete();
      }
      return false;
    }
    public function updatePrice($pomerId, $price, $userId)
    {
      $pohled = $this->getPohled($pomerId);
      if($this->canUpdate($pohled))
      {
          $pohled->update([
          'price'=>$price,
          'price_timestamp'=>date('Y-m-d H:i:s',time()),
          'price_timestamp_created_by'=>$userId
        ]);
        return 1;
      }
      return 0;
    }
    private function canUpdate($pohled){
      if($pohled->fk_mzdovyCom)
        return false;
      return true;
    }

    public function getTotalMzdovyCom($mzdovyComId)
    {
      return $this->db->table($this->table)->where("fk_mzdovyCom",$mzdovyComId)->sum("price");
    }
    public function getPohledsWithoutMzdovyCom($zamestnanecId)
    {
        return $this->db->table($this->table)->where("fk_mzdovyCom",0)->where("zamestnanecId",$zamestnanecId)->where("price >",0);
    //  $this->db->table($this->table)->where("zamestnanecId",$zamestnanecId)->where("fk_mzdovyCom",0);
    }
    public function addMzdovyComId($pomerId, $mzdovyComId)
    {
      $pohled = $this->getPohled($pomerId);
      $pohled->update([
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
