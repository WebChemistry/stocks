<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Typertion\Php\ArrayTypeAssert;
use WebChemistry\Stocks\Result\ArrayResult;
use WebChemistry\Stocks\Result\QuoteInterface;

final class Quote extends ArrayResult implements QuoteInterface
{

	public function getSymbol(): string
	{
		return ArrayTypeAssert::string($this->data, 'symbol');
	}

	public function getExchange(): string
	{
		return ArrayTypeAssert::string($this->data, 'exchange');
	}

	public function getName(): string
	{
		return ArrayTypeAssert::string($this->data, 'name');
	}

	public function getChange(): float
	{
		return ArrayTypeAssert::floatish($this->data, 'change');
	}

	public function getChangePercentage(): float
	{
		return ArrayTypeAssert::floatish($this->data, 'changesPercentage');
	}

	public function getPrice(): float
	{
		return ArrayTypeAssert::floatish($this->data, 'price');
	}

	public function getOpen(): float
	{
		return ArrayTypeAssert::floatish($this->data, 'open');
	}

	public function getPe(): ?float
	{
		return ArrayTypeAssert::floatishOrNull($this->data, 'pe');
	}

	public function getEps(): ?float
	{
		return ArrayTypeAssert::floatishOrNull($this->data, 'eps');
	}

	public function getMarketCap(): float
	{
		return ArrayTypeAssert::floatish($this->data, 'marketCap');
	}

	public function getPreviousClose(): float
	{
		return ArrayTypeAssert::floatish($this->data, 'previousClose');
	}

}
