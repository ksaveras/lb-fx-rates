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

final readonly class FxRate
{
    public function __construct(
        private ExchangeRateType $exchangeRateType,
        private \DateTimeImmutable $date,
        private CurrencyAmount $currencyAmount,
        private CurrencyAmount $targetCurrencyAmount,
    ) {
    }

    public function exchangeRateType(): ExchangeRateType
    {
        return $this->exchangeRateType;
    }

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function currencyAmount(): CurrencyAmount
    {
        return $this->currencyAmount;
    }

    public function targetCurrencyAmount(): CurrencyAmount
    {
        return $this->targetCurrencyAmount;
    }
}
