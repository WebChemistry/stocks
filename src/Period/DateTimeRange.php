<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Period;

use DateTime;
use DateTimeInterface;

final class DateTimeRange
{

	public function __construct(
		private DateTimeInterface $start,
		private DateTimeInterface $end,
	)
	{
	}

	public function getStart(): DateTimeInterface
	{
		return $this->start;
	}

	public function getEnd(): DateTimeInterface
	{
		return $this->end;
	}

	public static function createFromPeriod(StockPeriod $period, int $count): self
	{
		$start = new DateTime();
		$end = new DateTime(sprintf('- %d %s', $period->getNumber() * $count, $period->getPeriod(true)));
		
		return new self($start, $start->format('Y-m-d') === $end->format('Y-m-d') ? new DateTime('- 1 day') : $end);
	}

}
