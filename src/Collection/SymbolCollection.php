<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Collection;

use ArrayIterator;
use IteratorAggregate;
use OutOfBoundsException;

/**
 * @template T of object
 * @implements IteratorAggregate<string, T>
 */
final class SymbolCollection implements IteratorAggregate
{

	/**
	 * @param array<string, T> $collection
	 */
	public function __construct(
		private array $collection,
	)
	{
	}

	public function has(string $symbol): bool
	{
		return isset($this->collection[$symbol]);
	}

	/**
	 * @return T
	 */
	public function get(string $symbol): object
	{
		return $this->collection[$symbol] ?? throw new OutOfBoundsException(sprintf(
			'Symbol %s not exists in collection.',
			$symbol
		));
	}

	/**
	 * @return T|null
	 */
	public function getNullable(string $symbol): ?object
	{
		return $this->collection[$symbol] ?? null;
	}

	/**
	 * @return ArrayIterator<string, T>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->collection);
	}

	/**
	 * @return array<string, T>
	 */
	public function getAll(): array
	{
		return $this->collection;
	}

}
