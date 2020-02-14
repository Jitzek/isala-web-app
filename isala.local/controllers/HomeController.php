<?php

class HomeController
{
	private $model;

	public function __construct($model) {
		$this->model = $model;
	}
}
