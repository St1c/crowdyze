<?php
namespace Controls;

interface ISingleTaskControlFactory
{
	/** @return SingleTaskControl */
	function create();
}