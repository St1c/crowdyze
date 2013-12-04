<?php
namespace Controls;

interface IPaypalControlFactory
{
    /** @return PaypalControl */
    function create();
}