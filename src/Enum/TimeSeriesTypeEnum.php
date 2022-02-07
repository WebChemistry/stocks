<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static TimeSeriesTypeEnum DAY()
 * @method static TimeSeriesTypeEnum FIVE_DAYS()
 * @method static TimeSeriesTypeEnum MONTH()
 * @method static TimeSeriesTypeEnum SIX_MONTHS()
 * @method static TimeSeriesTypeEnum YEAR()
 * @method static TimeSeriesTypeEnum FIVE_YEARS()
 * @method static TimeSeriesTypeEnum MAX()
 * @extends Enum<string>
 */
final class TimeSeriesTypeEnum extends Enum
{

	private const DAY = 'day';
	private const FIVE_DAYS = 'five_days';
	private const MONTH = 'month';
	private const SIX_MONTHS = 'six_months';
	private const YEAR = 'year';
	private const FIVE_YEARS = 'five_years';
	private const MAX = 'max';

}
