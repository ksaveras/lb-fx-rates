# LB FxRates Client

More information: https://www.lb.lt/webservices/fxrates/

## Installation
```
composer require ksaveras/lb-fx-rates
```

## Use cases

### Basic example
Fetch FX rates from LB API. Using Symfony HttpClient to fetch data.

```php
use Ksaveras\LBFxRates\PsrClient;
use Symfony\Component\HttpClient\Psr18Client;

// Psr18Client implements all three required interfaces
$psr18Client = new Psr18Client();

$fxRatesClient = new PsrClient(
    $psr18Client,
    $psr18Client,
    $psr18Client,
);

$fxRates = $fxRatesClient->currentFxRates();

foreach ($fxRates as $fxRate) {
    echo sprintf(
        "%s: %.4f %s == %.4f %s\n",
        $fxRate->date()->format('Y-m-d'),
        $fxRate->currencyAmount()->amount(),
        $fxRate->currencyAmount()->currency()->value,
        $fxRate->targetCurrencyAmount()->amount(),
        $fxRate->targetCurrencyAmount()->currency()->value,
    );
}
```

This will output something like:

```
2024-08-31: 1.0000 EUR == 1.6542 AUD
2024-08-31: 1.0000 EUR == 1.9558 BGN
2024-08-31: 1.0000 EUR == 6.2147 BRL
2024-08-31: 1.0000 EUR == 1.5061 CAD
2024-08-31: 1.0000 EUR == 0.9387 CHF
2024-08-31: 1.0000 EUR == 7.8634 CNY
2024-08-31: 1.0000 EUR == 25.1470 CZK
```

## Tests
```
composer test
```

## Code Quality
```
composer static-analysis
```
