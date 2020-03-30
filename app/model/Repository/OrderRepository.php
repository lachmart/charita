<?php

namespace App\Model\Repository;


class OrderRepository extends Repository
{

    private $table = "order";

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
    public function getAllInvoice($invoiceId)
    {
      return $this->db->table($this->table)->where("fk_invoice",$invoiceId)->fetchAll();
    }

    public function addOrder($data)
    {
        return $this->db->table($this->table)->insert($data);
    }
    public function getOrder($orderId)
    {
        return $this->db->table($this->table)->get($orderId);
    }
    public function updateOrder($orderData)
    {
      $order = $this->getOrder($orderData->orderId);
      if($this->canUpdate($order))
      {
        $order->update($orderData);
        return 1;
      }
      return 0;
    }
    public function deleteOrder($orderId)
    {
      $order = $this->getOrder($orderId);
      if($this->canUpdate($order))
      {
        return $order->delete();
      }
      return false;
    }
    public function updatePrice($orderId, $price, $userId)
    {
      $order = $this->getOrder($orderId);
      if($this->canUpdate($order))
      {
          $order->update([
          'price'=>$price,
          'price_timestamp'=>date('Y-m-d H:i:s',time()),
          'price_timestamp_created_by'=>$userId
        ]);
        return 1;
      }
      return 0;
    }
    private function canUpdate($order){
      if($order->fk_invoice)
        return false;
      return true;
    }

    public function getTotalInvoice($invoiceId)
    {
      return $this->db->table($this->table)->where("fk_invoice",$invoiceId)->sum("price");
    }
    public function getOrdersWithoutInvoice($customerId)
    {
        return $this->db->table($this->table)->where("fk_invoice",0)->where("fk_customer",$customerId)->where("price >",0);
    //  $this->db->table($this->table)->where("fk_customer",$customerId)->where("fk_invoice",0);
    }
    public function addInvoiceId($orderId, $invoiceId)
    {
      $order = $this->getOrder($orderId);
      $order->update([
        'fk_invoice'=>$invoiceId,
      ]);
    }



}
