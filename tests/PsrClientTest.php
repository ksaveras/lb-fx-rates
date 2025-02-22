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
namespace Tests\Ksaveras\LBFxRates;

use Ksaveras\LBFxRates\Exception\UnexpectedResponseCodeException;
use Ksaveras\LBFxRates\Model\Currency;
use Ksaveras\LBFxRates\Model\ExchangeRateType;
use Ksaveras\LBFxRates\PsrClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\TraceableHttpClient;

/**
 * @phpstan-type TracedRequestShape array{
 *     method: string,
 *     url: string,
 *     options: array{headers: array{Content-Type: array{string}}},
 * }
 */
#[CoversClass(PsrClient::class)]
final class PsrClientTest extends TestCase
{
    private PsrClient $sut;

    private MockHttpClient $mockClient;

    private TraceableHttpClient $traceableClient;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = new MockHttpClient();
        $this->traceableClient = new TraceableHttpClient($this->mockClient);

        $psrClient = new Psr18Client($this->traceableClient);

        $this->sut = new PsrClient(
            $psrClient,
            $psrClient,
            $psrClient,
        );
    }

    #[Test]
    public function itReturnsCurrentFxRates(): void
    {
        if (false === $responseContent = file_get_contents(__DIR__.'/data/FxRates.xml')) {
            self::fail('Failed to load response content.');
        }

        $this->mockClient->setResponseFactory([
            static function (string $method, string $url, array $options) use ($responseContent): MockResponse {
                /** @var array{body: string|callable(int):string} $options */
                $requestBody = \is_callable($options['body']) ? $options['body'](1024) : $options['body'];

                self::assertSame('tp=EU', $requestBody);

                return new MockResponse($responseContent);
            },
        ]);

        $result = $this->sut->currentFxRates();

        self::assertSame(1, $this->mockClient->getRequestsCount());

        /** @var TracedRequestShape $tracedRequest */
        $tracedRequest = $this->traceableClient->getTracedRequests()[0];

        self::assertSame('POST', $tracedRequest['method']);
        self::assertSame('https://www.lb.lt/webservices/fxrates/FxRates.asmx/getCurrentFxRates', $tracedRequest['url']);
        self::assertSame(['application/x-www-form-urlencoded'], $tracedRequest['options']['headers']['Content-Type']);

        self::assertCount(86, $result);

        foreach ($result as $fxRate) {
            self::assertSame(ExchangeRateType::EU, $fxRate->exchangeRateType());
            self::assertSame(Currency::EUR, $fxRate->currencyAmount()->currency());
            self::assertSame(1.0, $fxRate->currencyAmount()->amount());
        }
    }

    public function testItReturnsFxRates(): void
    {
        if (false === $responseContent = file_get_contents(__DIR__.'/data/FxRates.xml')) {
            self::fail('Failed to load response content.');
        }

        $this->mockClient->setResponseFactory([
            static function (string $method, string $url, array $options) use ($responseContent): MockResponse {
                /** @var array{body: string|callable(int):string} $options */
                $requestBody = \is_callable($options['body']) ? $options['body'](1024) : $options['body'];

                self::assertSame('tp=EU&dt=2024-08-31', $requestBody);

                return new MockResponse($responseContent);
            },
        ]);

        $result = $this->sut->fxRates(new \DateTimeImmutable('2024-08-31'));

        self::assertSame(1, $this->mockClient->getRequestsCount());

        /** @var TracedRequestShape $tracedRequest */
        $tracedRequest = $this->traceableClient->getTracedRequests()[0];

        self::assertSame('POST', $tracedRequest['method']);
        self::assertSame('https://www.lb.lt/webservices/fxrates/FxRates.asmx/getFxRates', $tracedRequest['url']);
        self::assertSame(['application/x-www-form-urlencoded'], $tracedRequest['options']['headers']['Content-Type']);

        self::assertCount(86, $result);

        foreach ($result as $fxRate) {
            self::assertSame(ExchangeRateType::EU, $fxRate->exchangeRateType());
            self::assertSame(Currency::EUR, $fxRate->currencyAmount()->currency());
            self::assertSame(1.0, $fxRate->currencyAmount()->amount());
        }
    }

    public function testItReturnsRatesForCurrency(): void
    {
        if (false === $responseContent = file_get_contents(__DIR__.'/data/CurrencyFxRates.xml')) {
            self::fail('Failed to load response content.');
        }

        $this->mockClient->setResponseFactory([
            static function (string $method, string $url, array $options) use ($responseContent): MockResponse {
                /** @var array{body: string|callable(int):string} $options */
                $requestBody = \is_callable($options['body']) ? $options['body'](1024) : $options['body'];

                self::assertSame('tp=EU&ccy=AUD&dtFrom=2024-08-31&dtTo=2024-08-31', $requestBody);

                return new MockResponse($responseContent);
            },
        ]);

        $result = $this->sut->fxRatesForCurrency(
            Currency::AUD,
            new \DateTimeImmutable('2024-08-31'),
            new \DateTimeImmutable('2024-08-31'),
        );

        self::assertSame(1, $this->mockClient->getRequestsCount());

        /** @var TracedRequestShape $tracedRequest */
        $tracedRequest = $this->traceableClient->getTracedRequests()[0];

        self::assertSame('POST', $tracedRequest['method']);
        self::assertSame('https://www.lb.lt/webservices/fxrates/FxRates.asmx/getFxRatesForCurrency', $tracedRequest['url']);
        self::assertSame(['application/x-www-form-urlencoded'], $tracedRequest['options']['headers']['Content-Type']);

        self::assertCount(1, $result);

        foreach ($result as $fxRate) {
            self::assertSame(ExchangeRateType::EU, $fxRate->exchangeRateType());
            self::assertSame(Currency::EUR, $fxRate->currencyAmount()->currency());
            self::assertSame(1.0, $fxRate->currencyAmount()->amount());
        }
    }

    public function testItThrowsExceptionWhenResponseCodeIsNot200(): void
    {
        $this->mockClient->setResponseFactory([
            new MockResponse('', ['http_code' => 500]),
        ]);

        $this->expectExceptionObject(UnexpectedResponseCodeException::withStatusCode(500));

        $this->sut->currentFxRates();
    }
}
