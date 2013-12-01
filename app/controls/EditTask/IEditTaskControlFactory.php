<?php
namespace Controls;

interface IEditTaskControlFactory
{
	/** @return EditTaskControl */
	function create();
}