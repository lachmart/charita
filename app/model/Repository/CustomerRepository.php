<?php

namespace App\Model\Repository;


class CustomerRepository extends Repository
{

    private $table = "customer";


    public function getAll()
    {
        return $this->db->table($this->table)->fetchAll();
    }
    public function addCustomer($data)
    {
        return $this->db->table($this->table)->insert($data);
    }
    public function getCustomer($customerId)
    {
        return $this->db->table($this->table)->get($customerId);
    }
    public function deleteCustomer($customerId)
    {
        return $this->db->table($this->table)->get($customerId)->delete();
    }
    public function getName($customerId)
    {
        return $this->db->table($this->table)->get($customerId)->billingFullName;
    }
    public function getCustomerNames()
    {
        $table = $this->db->table($this->table);
        return $table->fetchPairs("customerId","billingFullName");
    }

}
