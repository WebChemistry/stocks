<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Utilitte\Asserts\ArrayTypeAssert;
use WebChemistry\Stocks\Result\ArrayResult;
use WebChemistry\Stocks\Result\RealtimePriceInterface;

final class RealtimePrice extends ArrayResult implements RealtimePriceInterface
{

	public function getSymbol(): string
	{
		return ArrayTypeAssert::string($this->data, 'symbol');
	}

	public function getPrice(): float
	{
		return ArrayTypeAssert::floatish($this->data, 'price');
	}

	public function getVolume(): int
	{
		return ArrayTypeAssert::int($this->data, 'volume');
	}

}
