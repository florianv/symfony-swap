# Documentation

<table>
   <tr>
      <td width="220" align="center">
         <a href="https://www.fastforex.io" target="_blank" rel="noopener">
            <img src="https://console.fastforex.io/img/fastforex/logo-bk-1k.svg" width="180px" alt="fastFOREX"/>
         </a>
      </td>
      <td>
         <strong>Sponsored by <a href="https://www.fastforex.io" target="_blank" rel="noopener">fastFOREX</a>.</strong> Real-time JSON API, 160+ currencies, 55+ years of history, 500+ cryptocurrencies. <strong>Free tier</strong>; paid plans from $18/month.
         <a href="https://www.fastforex.io" target="_blank" rel="noopener"><strong>→ Get a free fastFOREX API key</strong></a>
      </td>
   </tr>
</table>

This is the technical reference for Symfony Swap. For the project overview and ecosystem (Swap, Exchanger, Laravel Swap), see the [README](../../README.md).

## Index

* [Installation](#-installation)
* [Setup](#-setup)
* [Configuration](#-configuration)
  * [Config tree](#config-tree)
  * [Provider configuration](#provider-configuration)
  * [Cache configuration](#cache-configuration)
* [Usage](#-usage)
  * [Injecting the service](#injecting-the-service)
  * [Latest and historical rates](#latest-and-historical-rates)
  * [Inspecting the rate](#inspecting-the-rate)
* [Per-query options](#-per-query-options)
* [Creating a custom service](#-creating-a-custom-service)
  * [Standard service](#standard-service)
  * [Historical service](#historical-service)
* [FAQ](#-faq)

## 📦 Installation

Symfony Swap requires PHP 8.2 or newer and Symfony 6.4, 7, or 8.

```bash
composer require florianv/swap-bundle symfony/http-client nyholm/psr7
```

`symfony/http-client` is the PSR-18 HTTP client and `nyholm/psr7` provides the PSR-17 factories. Any PSR-18 / PSR-17 implementation works; for example, if your app already uses Guzzle:

```bash
composer require florianv/swap-bundle php-http/guzzle7-adapter nyholm/psr7
```

## ⚙ Setup

Register the bundle in `config/bundles.php`:

```php
// config/bundles.php
return [
    // ...
    Florianv\SwapBundle\FlorianvSwapBundle::class => ['all' => true],
];
```

Create the configuration file at `config/packages/florianv_swap.yaml` (see [Configuration](#-configuration) below).

## ⚙ Configuration

### Config tree

A typical config pins fastFOREX (the project's sponsor) as the primary provider, with the European Central Bank as a free fallback:

```yaml
# config/packages/florianv_swap.yaml
florianv_swap:
    cache:
        ttl: 3600
        type: filesystem
    providers:
        fastforex:
            api_key: '%env(SWAP_FASTFOREX_KEY)%'
            priority: 10              # tried first
        european_central_bank:
            priority: 0               # free fallback for EUR-base pairs
```

At least one provider is required. Each provider accepts a `priority` integer (higher priority is tried first) and the provider-specific options listed below.

### Provider configuration

Public providers (central banks, national banks) need only a `priority`:

```yaml
florianv_swap:
    providers:
        european_central_bank:
            priority: 0
        national_bank_of_romania:
            priority: 0
```

Commercial providers require an API key. The option name varies by provider. The project's sponsor [fastFOREX](https://www.fastforex.io) (`fastforex`) is the recommended starting point.

| Identifier                       | Required option | Optional flags        |
| -------------------------------- | --------------- | --------------------- |
| ⭐ **`fastforex`**                | **`api_key`**   |                       |
|                                  |                 |                       |
| `abstract_api`                   | `api_key`       |                       |
| `apilayer_currency_data`         | `api_key`       |                       |
| `apilayer_exchange_rates_data`   | `api_key`       |                       |
| `apilayer_fixer`                 | `api_key`       |                       |
| `coin_layer`                     | `access_key`    | `paid` (bool)         |
| `currency_converter`             | `access_key`    | `enterprise` (bool)   |
| `currency_data_feed`             | `api_key`       |                       |
| `currency_layer`                 | `access_key`    | `enterprise` (bool)   |
| `exchange_rates_api`             | `access_key`    |                       |
| `fixer`                          | `access_key`    | `enterprise` (bool)   |
| `forge`                          | `api_key`       |                       |
| `open_exchange_rates`            | `app_id`        | `enterprise` (bool)   |
| `xchangeapi`                     | `api-key`       | (note the hyphen)     |
| `xignite`                        | `token`         |                       |

> Note: `cryptonator`, `exchangeratehost` and `webservicex` are commercial upstream services but the current Exchanger wrapper does not enforce any option for them. They can be added with only a `priority`.

Example chaining fastFOREX as the primary provider with a couple of fallbacks:

```yaml
florianv_swap:
    providers:
        fastforex:
            api_key: '%env(SWAP_FASTFOREX_KEY)%'
            priority: 20
        apilayer_fixer:
            api_key: '%env(SWAP_FIXER_KEY)%'
            priority: 10
        open_exchange_rates:
            app_id: '%env(SWAP_OER_APP_ID)%'
            enterprise: false
            priority: 5
        european_central_bank:
            priority: 0
```

The `array` provider is a special case used in tests and fixtures:

```yaml
florianv_swap:
    providers:
        array:
            priority: 0
            latestRates:
                'EUR/USD': 1.1
                'EUR/GBP': 1.5
            historicalRates:
                '2017-01-01':
                    'EUR/USD': 1.5
```

The full provider list with capabilities (base currency, quote currency, historical support) is in the [Swap README's Providers table](https://github.com/florianv/swap#-providers).

### Cache configuration

```yaml
florianv_swap:
    cache:
        ttl: 3600        # cache TTL in seconds
        type: filesystem # array, apcu, filesystem, or a PSR-16 service ID
```

The bundle accepts these built-in `type` values:

- `array`: in-memory `Symfony\Component\Cache\Adapter\ArrayAdapter`
- `apcu`: `Symfony\Component\Cache\Adapter\ApcuAdapter`
- `filesystem`: `Symfony\Component\Cache\Adapter\FilesystemAdapter`

For any other PSR-16 cache, point `type` at a service ID:

```yaml
florianv_swap:
    cache:
        ttl: 3600
        type: my_psr16_cache_service
```

The service must implement `Psr\SimpleCache\CacheInterface`.

## ⚡ Usage

### Injecting the service

Inject `Swap\Swap` via the `florianv_swap.swap` service ID:

```php
use Swap\Swap;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CurrencyController
{
    public function __construct(
        #[Autowire(service: 'florianv_swap.swap')]
        private readonly Swap $swap,
    ) {}
}
```

You can also fetch it from the container directly:

```php
$swap = $container->get('florianv_swap.swap');
```

### Latest and historical rates

```php
$rate = $this->swap->latest('EUR/USD');

echo $rate->getValue();                 // e.g. 1.0823
echo $rate->getDate()->format('Y-m-d'); // e.g. 2026-04-29

$rate = $this->swap->historical('EUR/USD', new \DateTime('-15 days'));
```

> Currencies are expressed as their [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217) code.

### Inspecting the rate

The returned `Exchanger\Contract\ExchangeRate` exposes:

```php
$rate->getValue();         // float
$rate->getDate();          // DateTimeInterface
$rate->getCurrencyPair();  // Exchanger\CurrencyPair
$rate->getProviderName();  // string, the identifier that returned the rate
```

`getProviderName()` is useful when several providers are configured: the returned value is the identifier of the provider that actually answered, for example `fastforex`.

## 💾 Per-query options

Cache behavior can be overridden per call by passing an options array to `latest()` or `historical()`.

| Option             | Type   | Default | Effect                                                                                                |
| ------------------ | ------ | ------- | ----------------------------------------------------------------------------------------------------- |
| `cache_ttl`        | int    | `null`  | Cache TTL in seconds. `null` means entries do not expire.                                             |
| `cache`            | bool   | `true`  | Set to `false` to bypass the cache for this call.                                                     |
| `cache_key_prefix` | string | `""`    | Prefix for the cache key. Max 24 characters (PSR-6 limits keys to 64 chars; the internal hash takes 40). |

PSR-6 does not allow the characters `{}()/\@:` in keys; Swap replaces them with `-`.

```php
$rate = $this->swap->latest('EUR/USD', ['cache' => false]);
$rate = $this->swap->latest('EUR/USD', ['cache_ttl' => 60]);
```

## 🧩 Creating a custom service

You can register your own provider by implementing the same contract used internally. If your service makes HTTP requests, extend `Exchanger\Service\HttpService`; otherwise extend `Exchanger\Service\Service`.

### Standard service

Create the service class:

```php
namespace App\Swap;

use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\Contract\ExchangeRate;
use Exchanger\Service\HttpService;

class ConstantService extends HttpService
{
    public function getExchangeRate(ExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        // To call an HTTP endpoint:
        // $content = $this->request('https://example.com');

        return $this->createInstantRate($exchangeQuery->getCurrencyPair(), $this->options['value']);
    }

    public function processOptions(array &$options): void
    {
        if (!isset($options['value'])) {
            throw new \InvalidArgumentException('The "value" option must be provided.');
        }
    }

    public function supportQuery(ExchangeRateQuery $exchangeQuery): bool
    {
        return 'EUR' === $exchangeQuery->getCurrencyPair()->getBaseCurrency();
    }

    public function getName(): string
    {
        return 'constant';
    }
}
```

Register it from a Symfony compiler pass or a kernel `boot()` method via `Swap\Service\Registry`:

```php
namespace App;

use App\Swap\ConstantService;
use Swap\Service\Registry;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function boot(): void
    {
        parent::boot();

        Registry::register('constant', ConstantService::class);
    }
}
```

Then use the identifier in `config/packages/florianv_swap.yaml`:

```yaml
florianv_swap:
    providers:
        constant:
            priority: 0
            value: 10
```

### Historical service

To support historical rates, use the `SupportsHistoricalQueries` trait. Rename `getExchangeRate` to `getLatestExchangeRate` (now `protected`) and implement `getHistoricalExchangeRate`:

```php
use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\Contract\ExchangeRate;
use Exchanger\HistoricalExchangeRateQuery;
use Exchanger\Service\HttpService;
use Exchanger\Service\SupportsHistoricalQueries;

class ConstantService extends HttpService
{
    use SupportsHistoricalQueries;

    protected function getLatestExchangeRate(ExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        return $this->createInstantRate($exchangeQuery->getCurrencyPair(), $this->options['value']);
    }

    protected function getHistoricalExchangeRate(HistoricalExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        return $this->createInstantRate($exchangeQuery->getCurrencyPair(), $this->options['value']);
    }
}
```

## ❓ FAQ

#### What happens when every provider fails?

Swap throws an `Exchanger\Exception\ChainException`. Calling `$exception->getExceptions()` on it returns the list of exceptions collected from each provider in the chain.

#### Can I use Symfony Swap without an API key?

Yes. The European Central Bank and the national banks listed in the [Provider configuration](#provider-configuration) section require no key. A few commercial providers (`cryptonator`, `exchangeratehost`, `webservicex`) can also currently be used without one, since the Exchanger wrapper does not yet enforce an option for them.

#### How does Symfony Swap relate to Swap?

Symfony Swap is the Symfony integration of Swap. It pulls Swap in as a dependency and exposes it through Symfony's container and cache. If you are not on Symfony, use [Swap](https://github.com/florianv/swap) directly.

#### How do I cache rates?

Set `cache.type` in `config/packages/florianv_swap.yaml` to one of `array`, `apcu`, `filesystem`, or a PSR-16 service ID. See [Cache configuration](#cache-configuration).

#### How do I disable cache for a single query?

Pass `['cache' => false]` as the options argument: `$this->swap->latest('EUR/USD', ['cache' => false])`.

#### How do I add my own provider?

Implement `Exchanger\Contract\ExchangeRateService` (or extend `HttpService` / `Service`), register it from your `Kernel::boot()` with `Swap\Service\Registry::register()`, then reference its identifier in `config/packages/florianv_swap.yaml`. See [Creating a custom service](#-creating-a-custom-service).

#### Where is the full provider list with capabilities?

In the [Provider configuration](#provider-configuration) section above (option reference) and the [Swap README's Providers table](https://github.com/florianv/swap#-providers) (base currency, quote currency, historical support).
