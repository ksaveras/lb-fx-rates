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

use Ksaveras\LBFxRates\Exception\UnableToParseResponseException;
use Ksaveras\LBFxRates\Exception\UnexpectedResponseCodeException;
use Ksaveras\LBFxRates\Model\Currency;
use Ksaveras\LBFxRates\Model\CurrencyAmount;
use Ksaveras\LBFxRates\Model\ExchangeRateType;
use Ksaveras\LBFxRates\Model\FxRate;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class PsrClient implements Client
{
    private const CURRENT_FX_RATES_URL = 'https://www.lb.lt/webservices/fxrates/FxRates.asmx/getCurrentFxRates';

    private const FX_RATES_URL = 'https://www.lb.lt/webservices/fxrates/FxRates.asmx/getFxRates';

    private const FX_RATES_FOR_CURRENCY_URL = 'https://www.lb.lt/webservices/fxrates/FxRates.asmx/getFxRatesForCurrency';

    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public function currentFxRates(?ExchangeRateType $exchangeRateType = null): array
    {
        $response = $this->doSendRequest(
            self::CURRENT_FX_RATES_URL,
            [
                'tp' => $exchangeRateType->value ?? ExchangeRateType::EU->value,
            ],
        );

        if (200 !== $response->getStatusCode()) {
            throw UnexpectedResponseCodeException::withStatusCode($response->getStatusCode());
        }

        return $this->parseResponse($response);
    }

    public function fxRates(\DateTimeImmutable $date, ?ExchangeRateType $exchangeRateType = null): array
    {
        $response = $this->doSendRequest(
            self::FX_RATES_URL,
            [
                'tp' => $exchangeRateType->value ?? ExchangeRateType::EU->value,
                'dt' => $date->format('Y-m-d'),
            ],
        );

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to fetch data');
        }

        return $this->parseResponse($response);
    }

    /**
     * @return FxRate[]
     */
    public function fxRatesForCurrency(
        Currency $currency,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        ?ExchangeRateType $exchangeRateType = null,
    ): array {
        $response = $this->doSendRequest(
            self::FX_RATES_FOR_CURRENCY_URL,
            [
                'tp' => $exchangeRateType->value ?? ExchangeRateType::EU->value,
                'ccy' => $currency->value,
                'dtFrom' => $dateFrom->format('Y-m-d'),
                'dtTo' => $dateTo->format('Y-m-d'),
            ],
        );

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to fetch data');
        }

        return $this->parseResponse($response);
    }

    /**
     * @return FxRate[]
     */
    public function parseResponse(ResponseInterface $response): array
    {
        $result = [];

        $response->getBody()->rewind();
        $xml = simplexml_load_string($response->getBody()->getContents());

        if (false === $xml) {
            throw UnableToParseResponseException::failedToLoadXML();
        }

        foreach ($xml->FxRate as $fxRate) {
            if (false === $date = \DateTimeImmutable::createFromFormat('Y-m-d|', (string) $fxRate->Dt, new \DateTimeZone('UTC'))) {
                throw UnableToParseResponseException::invalidDateValueProvided((string) $fxRate->Dt);
            }

            $result[] = new FxRate(
                ExchangeRateType::from((string) $fxRate->Tp),
                $date,
                new CurrencyAmount(
                    Currency::from((string) $fxRate->CcyAmt[0]->Ccy),
                    (float) $fxRate->CcyAmt[0]->Amt,
                ),
                new CurrencyAmount(
                    Currency::from((string) $fxRate->CcyAmt[1]->Ccy),
                    (float) $fxRate->CcyAmt[1]->Amt,
                ),
            );
        }

        return $result;
    }

    /**
     * @param array<string, string> $formData
     */
    private function doSendRequest(string $url, array $formData): ResponseInterface
    {
        $request = $this->requestFactory->createRequest('POST', $url)
            ->withAddedHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(
                $this->streamFactory->createStream(
                    http_build_query(
                        $formData,
                        '',
                        '&',
                        \PHP_QUERY_RFC3986,
                    ),
                ),
            );

        return $this->client->sendRequest($request);
    }
}
