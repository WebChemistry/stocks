<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Utilitte\Asserts\ArrayTypeAssert;
use WebChemistry\Stocks\Result\QuoteInterface;

final class Quote implements QuoteInterface
{

	/**
	 * @param mixed[] $data
	 */
	public function __construct(
		private array $data,
	)
	{
	}

	public function getSymbol(): string
	{
		return ArrayTypeAssert::string($this->data, 'symbol');
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

	public function getPreviousClose(): float
	{
		return ArrayTypeAssert::floatish($this->data, 'previousClose');
	}

}
