<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result;

use DateTimeImmutable;

interface HistoricalPriceInterface
{

	public function getPrice(): float;

	public function getDate(): DateTimeImmutable;

}
