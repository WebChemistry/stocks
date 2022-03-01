<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Utilitte\Asserts\ArrayTypeAssert;
use WebChemistry\Stocks\Result\ArrayResult;

final class Rating extends ArrayResult
{

	public function getSymbol(): string
	{
		return ArrayTypeAssert::string($this->data, 'symbol');
	}

	public function getRatingScore(): int
	{
		return ArrayTypeAssert::int($this->data, 'ratingScore');
	}

}
