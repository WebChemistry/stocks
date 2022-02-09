<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use DateTimeImmutable;
use Utilitte\Asserts\ArrayTypeAssert;
use WebChemistry\Stocks\Result\ArrayResult;
use WebChemistry\Stocks\Result\HistoricalPriceInterface;

class HistoricalPrice extends ArrayResult implements HistoricalPriceInterface
{

	public function getPrice(): float
	{
		return ArrayTypeAssert::floatish($this->data, 'c');
	}

	public function getDate(): DateTimeImmutable
	{
		if (isset($this->data['formated'])) { // their typo in v4
			return new DateTimeImmutable(ArrayTypeAssert::string($this->data, 'formated'));
		}

		return new DateTimeImmutable(ArrayTypeAssert::string($this->data, 'formatted'));
	}

}
