<?php

namespace App\Model\Repository;


class InvoiceRepository extends Repository
{

    private $table = "invoice";

    public function getAll()
    {
        return $this->db->table($this->table)->fetchAll();
    }
    public function getAllCustomer($customerId)
    {
      if(!$customerId)
        return $this->getAll();
      else {
        return $this->db->table($this->table)->where("fk_customer",$customerId)->fetchAll();
      }
    }
    public function addInvoice($data)
    {
        return $this->db->table($this->table)->insert($data);
    }
    public function getInvoice($invoiceId)
    {
        return $this->db->table($this->table)->get($invoiceId);
    }
    public function deleteInvoice($invoiceId)
    {
        $this->db->table('order')->where('fk_invoice', $invoiceId)->update(['fk_invoice'=>0]);
        return $this->db->table($this->table)->where('invoiceId', $invoiceId)->delete();
    }
    public function makeInvoice($customerId)
    {
        $data["fk_customer"] = $customerId;
        return $this->addInvoice($data);
      }




}
