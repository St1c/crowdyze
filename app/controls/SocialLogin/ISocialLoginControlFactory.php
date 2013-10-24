<?php
namespace Controls;

interface ISocialLoginControlFactory
{
    /** @return SocialLoginControl */
    function create();
}