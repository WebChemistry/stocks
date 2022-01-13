<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Helper;

use Utilitte\Asserts\ArrayTypeAssert;
use Utilitte\Asserts\TypeAssert;
use WebChemistry\Stocks\Collection\SymbolCollection;
use WebChemistry\Stocks\Exception\StockClientNoDataException;

final class MapperHelper
{
	/**
	 * @template T of object
	 * @param class-string<T> $class
	 * @param array<int|string, mixed> $values
	 * @return T[]
	 */
	public static function mapToObjects(string $class, array $values): array
	{
		return array_map(
			fn (mixed $value) => new $class(TypeAssert::array($value)),
			$values
		);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $class
	 * @param array<int|string, mixed> $values
	 * @return SymbolCollection<T>
	 */
	public static function mapToCollection(string $class, array $values, string $key = 'symbol'): SymbolCollection
	{
		$symbols = self::mapWithSymbolKey(
			fn (array $data) => new $class($data),
			$values,
			$key
		);

		return new SymbolCollection($symbols);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $class
	 * @param array<int|string, mixed> $values
	 * @return T
	 */
	public static function mapToObject(string $class, array $values): object
	{
		if (!isset($values[0])) {
			throw new StockClientNoDataException();
		}

		return new $class(TypeAssert::array($values[0]));
	}

	/**
	 * @template T
	 * @param callable(array<int|string, mixed>): T $callback
	 * @param mixed[] $values
	 * @return T[]
	 */
	public static function mapWithSymbolKey(callable $callback, array $values, string $key = 'symbol'): array
	{
		$return = [];
		foreach ($values as $value) {
			$value = TypeAssert::array($value);

			$return[ArrayTypeAssert::string($value, $key)] = $callback($value);
		}

		return $return;
	}

}
