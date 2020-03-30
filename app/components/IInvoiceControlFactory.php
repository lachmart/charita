<?php
namespace App\Components;

interface IInvoiceControlFactory
{

    /**
     * @return InvoiceControl
     */
    function create();

}
