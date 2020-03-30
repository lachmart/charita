<?php
namespace App\Components;

interface IOrderControlFactory
{

    /**
     * @return OrderControl
     */
    function create();

}
