<?php
namespace Controls;

interface IAddTaskControlFactory
{
	/** @return AddTaskControl */
	function create();
}