<?php

namespace App\Http\Controllers;

abstract class Controller
{
	protected $event_repo;

	public function ifEventNull($event_id = null): bool
	{
		return !$event_id;
	}
}
