<?php

declare(strict_types=1);
/*
 * This file is part of ksaveras/lb-fx-rates.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\LBFxRates\Model;

final readonly class CurrencyAmount
{
    public function __construct(
        private Currency $currency,
        private float $amount,
    ) {
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function amount(): float
    {
        return $this->amount;
    }
}
