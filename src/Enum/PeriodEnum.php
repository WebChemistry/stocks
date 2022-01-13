<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static PeriodEnum ANNUAL()
 * @method static PeriodEnum QUARTER()
 * @extends Enum<string>
 */
final class PeriodEnum extends Enum
{

	private const ANNUAL = 'annual';
	private const QUARTER = 'quarter';

}
