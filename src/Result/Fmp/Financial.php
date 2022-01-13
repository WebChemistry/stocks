<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result\Fmp;

use Utilitte\Asserts\TypeAssert;
use WebChemistry\Stocks\Result\FinancialInterface;

final class Financial implements FinancialInterface
{

	/**
	 * @param mixed[] $data
	 */
	public function __construct(
		private array $data,
	)
	{
	}

	public function getEbitda(): ?int
	{
		return TypeAssert::intOrNull($this->data['ebitda'] ?? null);
	}

}
