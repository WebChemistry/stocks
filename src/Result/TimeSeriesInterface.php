<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result;

use DateTimeInterface;

interface TimeSeriesInterface
{

	public function getDate(): DateTimeInterface;

	public function getPrice(): float;

}
