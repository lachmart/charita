<?php

namespace App\Model\Repository;

class UserRepository extends Repository
{
    private $table = "user";

    public function getUserByUsername($username)
    {
        return $this->db->table($this->table)
            ->where('userName = ', $username)
            ->fetch();
    }
    public function getUserNames()
    {
        $table = $this->db->table($this->table);
        return $table->fetchPairs("userId","userName");
    }
}
