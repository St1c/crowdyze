<?php
namespace Controls;

interface IUserDetailsControlFactory
{
	/** @return UserDetailsControl */
	function create();
}