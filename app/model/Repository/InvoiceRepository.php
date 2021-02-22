<?php

namespace App\Model\Repository;


class InvoiceRepository extends Repository
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
    public function addInvoice($data)
    {
        return $this->db->table($this->table)->insert($data);
    }
    public function getInvoice($mzdovyComId)
    {
        return $this->db->table($this->table)->get($mzdovyComId);
    }
    public function deleteInvoice($mzdovyComId)
    {
        $this->db->table('order')->where('fk_invoice', $mzdovyComId)->update(['fk_invoice'=>0]);
        return $this->db->table($this->table)->where('mzdovyComId', $mzdovyComId)->delete();
    }
    public function makeInvoice($zamestnanecId)
    {
        $data["zamestnanecId"] = $zamestnanecId;
        return $this->addInvoice($data);
      }




}
