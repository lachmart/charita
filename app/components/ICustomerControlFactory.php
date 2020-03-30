<?php
namespace App\Components;

interface ICustomerControlFactory
{

    /**
     * @return CustomerControl
     */
    function create();

}