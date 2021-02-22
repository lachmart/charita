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
    // public function getAllInvoice($mzdovyComId)
    // {
    //   return $this->db->table($this->table)->where("fk_invoice",$mzdovyComId)->fetchAll();
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
      if($wR->fk_invoice)
        return false;
      return true;
    }

    public function getTotalInvoice($mzdovyComId)
    {
      return $this->db->table($this->table)->where("fk_invoice",$mzdovyComId)->sum("price");
    }
    public function getWRsWithoutInvoice($zamestnanecId)
    {
        return $this->db->table($this->table)->where("fk_invoice",0)->where("zamestnanecId",$zamestnanecId)->where("price >",0);
    //  $this->db->table($this->table)->where("zamestnanecId",$zamestnanecId)->where("fk_invoice",0);
    }
    public function addInvoiceId($pomerId, $mzdovyComId)
    {
      $wR = $this->getWR($pomerId);
      $wR->update([
        'fk_invoice'=>$mzdovyComId,
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
