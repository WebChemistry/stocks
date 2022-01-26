<?php declare(strict_types = 1);

namespace WebChemistry\Stocks\Result;

interface FinancialInterface extends ArrayResultInterface
{

	public function getEbitda(): ?int;

}
