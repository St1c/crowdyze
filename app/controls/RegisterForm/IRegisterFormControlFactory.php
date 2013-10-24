<?php
namespace Controls;

interface IRegisterFormControlFactory
{
    /** @return RegisterFormControl */
    function create();
}