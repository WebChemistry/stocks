<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Typertion\Php\ArrayTypeAssert;

final class HistoricalPriceWithSymbol extends HistoricalPrice
{

	public function getSymbol(): string
	{
		return ArrayTypeAssert::string($this->data, 'symbol');
	}

}
