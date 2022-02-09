<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Utilitte\Asserts\ArrayTypeAssert;

final class HistoricalPriceWithSymbol extends HistoricalPrice
{

	public function getSymbol(): string
	{
		return ArrayTypeAssert::string($this->data, 'symbol');
	}

}
