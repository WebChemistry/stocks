<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Period;

use LogicException;
use Nette\Utils\Strings;

final class StockPeriod
{

	private const MAP = ['minute', 'minutes', 'hour', 'hours', 'day', 'days', 'week', 'weeks', 'month', 'months', 'year', 'years'];

	private int $number;

	private string $period;

	public function __construct(string $period)
	{
		[$this->number, $this->period] = $this->explode(
			strtolower(Strings::replace($period, '#\s#'))
		);

		$this->checkPeriod($this->period);
	}

	public function isMinutely(): bool
	{
		return str_starts_with($this->period, 'minute');
	}

	public function isMonthly(): bool
	{
		return str_starts_with($this->period, 'month');
	}

	public function isWeekly(): bool
	{
		return str_starts_with($this->period, 'week');
	}

	public function isDaily(): bool
	{
		return str_starts_with($this->period, 'day');
	}

	public function isHourly(): bool
	{
		return str_starts_with($this->period, 'hour');
	}

	public function isYearly(): bool
	{
		return str_starts_with($this->period, 'year');
	}

	public function getNumber(): int
	{
		return $this->number;
	}

	public function getPeriod(bool $preservePlural = false): string
	{
		return $preservePlural ?
			$this->period :
			(str_ends_with($this->period, 's') ? substr($this->period, 0, -1) : $this->period);
	}

	/**
	 * @param string $period
	 * @return array{int, string}
	 */
	private function explode(string $period): array
	{
		$pos = 0;
		$len = strlen($period);
		$number = '';

		while ($pos < $len && is_numeric($period[$pos])) {
			$number .= $period[$pos];

			$pos++;
		}

		return [max((int) $number, 1), substr($period, $pos)];
	}

	private function checkPeriod(string $period): void
	{
		if (!in_array($period, self::MAP, true)) {
			throw new LogicException(sprintf('Period %s is not valid.', $period));
		}
	}

}
