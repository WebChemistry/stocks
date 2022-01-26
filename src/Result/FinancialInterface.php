<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result;

interface FinancialInterface
{

	public function getEbitda(): ?int;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
