<?php
namespace Controls;

interface ILoginFormControlFactory
{
    /** @return LoginFormControl */
    function create();
}