<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Typertion\Php\ArrayTypeAssert;
use Typertion\Php\TypeAssert;
use WebChemistry\Stocks\Enum\TickerTypeEnum;
use WebChemistry\Stocks\Exception\StockClientException;
use WebChemistry\Stocks\Result\ArrayResult;
use WebChemistry\Stocks\Result\SymbolInterface;

final class Symbol extends ArrayResult implements SymbolInterface
{

	public function getSymbol(): string
	{
		return ArrayTypeAssert::string($this->data, 'symbol');
	}

	public function getName(): string
	{
		return ArrayTypeAssert::string($this->data, 'name');
	}

	public function getExchange(): ?string
	{
		return TypeAssert::stringOrNull($this->data['exchange'] ?? null);
	}

	public function getExchangeShortName(): ?string
	{
		return TypeAssert::stringOrNull($this->data['exchangeShortName'] ?? null);
	}

	public function getType(): TickerTypeEnum
	{
		$type = strtolower(ArrayTypeAssert::string($this->data, 'type'));

		return match ($type) {
			'etf' => TickerTypeEnum::ETF(),
			'stock', 'trust' => TickerTypeEnum::STOCK(),
			'fund' => TickerTypeEnum::FUND(),
			'index' => TickerTypeEnum::INDEX(),
			'crypto' => TickerTypeEnum::CRYPTO(),
			'commodity' => TickerTypeEnum::COMMODITY(),
			default => throw new StockClientException(sprintf('Unknown type of stock "%s"', $type)),
		};
	}

	/**
	 * @param mixed[] $data
	 */
	public static function createFromEtf(array $data): self
	{
		$data['type'] = 'etf';

		return new self($data);
	}

	/**
	 * @param mixed[] $data
	 */
	public static function createFromFund(array $data): self
	{
		$data['type'] = 'fund';

		return new self($data);
	}

	/**
	 * @param mixed[] $data
	 */
	public static function createFromIndex(array $data): self
	{
		$data['type'] = 'index';

		return new self($data);
	}

	/**
	 * @param mixed[] $data
	 */
	public static function createFromCrypto(array $data): self
	{
		$data['type'] = 'crypto';

		return new self($data);
	}

	/**
	 * @param mixed[] $data
	 */
	public static function createFromCommodity(array $data): self
	{
		$data['type'] = 'commodity';

		return new self($data);
	}

}
