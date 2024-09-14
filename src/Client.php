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
namespace Ksaveras\LBFxRates;

use Ksaveras\LBFxRates\Model\Currency;
use Ksaveras\LBFxRates\Model\ExchangeRateType;
use Ksaveras\LBFxRates\Model\FxRate;

interface Client
{
    /**
     * @return FxRate[]
     */
    public function currentFxRates(?ExchangeRateType $exchangeRateType = null): array;

    /**
     * @return FxRate[]
     */
    public function fxRates(\DateTimeImmutable $date, ?ExchangeRateType $exchangeRateType = null): array;

    /**
     * @return FxRate[]
     */
    public function fxRatesForCurrency(Currency $currency, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo, ?ExchangeRateType $exchangeRateType = null): array;
}
