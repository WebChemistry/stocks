<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static TickerTypeEnum ETF()
 * @method static TickerTypeEnum STOCK()
 * @method static TickerTypeEnum CRYPTO()
 * @method static TickerTypeEnum FUND()
 * @method static TickerTypeEnum INDEX()
 * @method static TickerTypeEnum COMMODITY()
 * @extends Enum<string>
 */
final class TickerTypeEnum extends Enum
{

	private const ETF = 'etf';
	private const STOCK = 'stock';
	private const CRYPTO = 'crypto';
	private const FUND = 'fund';
	private const INDEX = 'index';
	private const COMMODITY = 'commodity';

}
