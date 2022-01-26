<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Collection;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use Nette\Utils\Arrays;
use OutOfBoundsException;
use Utilitte\Asserts\TypeAssert;
use WebChemistry\Stocks\Result\ArrayResultInterface;

/**
 * @template T of object
 * @implements IteratorAggregate<string, T>
 */
final class SymbolCollection implements IteratorAggregate, Countable
{

	/**
	 * @param array<string, T> $collection
	 */
	public function __construct(
		private array $collection = [],
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

	/**
	 * @return array{ type: class-string<T>|null, cache: array<string, array<string|int, mixed>> }
	 */
	public function cache(): array
	{
		$first = Arrays::first($this->collection);

		if (!is_object($first)) {
			return [
				'type' => null,
				'cache' => [],
			];
		}

		return [
			'type' => $first::class,
			'cache' => $this->flatten(),
		];
	}

	/**
	 * @return array<string, array<string|int, mixed>>
	 */
	public function flatten(): array
	{
		$flatten = [];

		foreach ($this->collection as $key => $value) {
			if (!$value instanceof ArrayResultInterface) {
				throw new LogicException(
					sprintf(
						'Flatten method expects array of %s, %s given.',
						ArrayResultInterface::class,
						get_debug_type($value),
					)
				);
			}
			$flatten[$key] = $value->toArray();
		}

		return $flatten;
	}

	public function count(): int
	{
		return count($this->collection);
	}

	/**
	 * @template X of ArrayResultInterface
	 * @param mixed[] $cache
	 * @param class-string<X> $type
	 * @return SymbolCollection<X>
	 */
	public static function fromCache(string $type, array $cache): SymbolCollection
	{
		$collection = [];

		if (!array_key_exists('type', $cache)) {
			throw new LogicException('Given cache does not have type key.');
		}

		if (!isset($cache['cache'])) {
			throw new LogicException('Given cache does not have cache key.');
		}

		$factory = TypeAssert::stringOrNull($cache['type']);
		if ($factory === null) { // null is empty collection
			return new SymbolCollection();
		}

		if (!is_a($factory, $type, true)) {
			throw new LogicException(sprintf('Given type %s is not instance of %s.', $type, $factory));
		}

		foreach (TypeAssert::array($cache['cache']) as $key => $value) {
			$collection[$key] = new $factory($value);
		}

		return new SymbolCollection($collection);
	}

}
