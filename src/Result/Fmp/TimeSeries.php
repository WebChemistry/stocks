<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use DateTime;
use DateTimeInterface;
use Typertion\Php\ArrayTypeAssert;
use Typertion\Php\TypeAssert;
use WebChemistry\Stocks\Result\ArrayResult;
use WebChemistry\Stocks\Result\TimeSeriesInterface;

final class TimeSeries extends ArrayResult implements TimeSeriesInterface
{

	public function getDate(): DateTimeInterface
	{
		return new DateTime(ArrayTypeAssert::string($this->data, 'date'));
	}

	public function getPrice(): float
	{
		if (isset($this->data['close'])) {
			return ArrayTypeAssert::floatish($this->data, 'close');
		}

		return ArrayTypeAssert::floatish($this->data, 'price');
	}

}
