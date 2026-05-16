# Symfony Swap

[![Tests](https://github.com/florianv/symfony-swap/actions/workflows/tests.yml/badge.svg)](https://github.com/florianv/symfony-swap/actions/workflows/tests.yml)
[![Psalm](https://github.com/florianv/symfony-swap/actions/workflows/psalm.yml/badge.svg)](https://github.com/florianv/symfony-swap/actions/workflows/psalm.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap-bundle.svg?style=flat-square)](https://packagist.org/packages/florianv/swap-bundle)
[![Version](http://img.shields.io/packagist/v/florianv/swap-bundle.svg?style=flat-square)](https://packagist.org/packages/florianv/swap-bundle)

> _Drop-in Symfony bundle for currency conversion. Multi-provider exchange rates with fallback, caching, and Symfony Cache integration. Maintained since 2014._

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

**Install the bundle, drop a `florianv_swap.yaml` in `config/packages/`, and the `florianv_swap.swap` service is ready to inject. No service container plumbing, no boilerplate.**

Symfony Swap is a drop-in package for **Symfony currency conversion**. Install it, configure providers in `config/packages/florianv_swap.yaml`, and pull exchange rates from multiple providers in one call. The bundle integrates with Symfony Cache out of the box and supports Symfony 6.4 / 7 / 8.

## 💡 What is Symfony Swap?

- The Symfony integration of [Swap](https://github.com/florianv/swap), the PHP currency conversion library.
- Registers a `florianv_swap.swap` service in the container (`Swap\Swap` class).
- Configuration lives in `config/packages/florianv_swap.yaml`.
- Caching uses Symfony Cache (`array`, `apcu`, `filesystem`, or any PSR-16 service ID).
- Providers are tried in priority order (higher priority first).

## 📦 Installation

Symfony Swap requires PHP 8.2 or newer and Symfony 6.4, 7, or 8.

```bash
composer require florianv/swap-bundle symfony/http-client nyholm/psr7
```

Register the bundle in `config/bundles.php` (Symfony Flex skips this step if a recipe applies):

```php
// config/bundles.php
return [
    // ...
    Florianv\SwapBundle\FlorianvSwapBundle::class => ['all' => true],
];
```

## ⚡ Quickstart

Configure providers in `config/packages/florianv_swap.yaml`. The recommended primary provider is **[fastFOREX](https://www.fastforex.io)** (the project's sponsor): a real-time JSON API behind a single `api_key`, [free tier available](https://www.fastforex.io).

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

Inject the service:

```php
use Swap\Swap;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CurrencyController
{
    public function __construct(
        #[Autowire(service: 'florianv_swap.swap')]
        private readonly Swap $swap,
    ) {}

    public function rate(): array
    {
        // EUR → USD exchange rate
        $rate = $this->swap->latest('EUR/USD');

        return [
            'value'    => $rate->getValue(),                 // e.g. 1.0823
            'date'     => $rate->getDate()->format('Y-m-d'), // e.g. 2026-04-29
            'provider' => $rate->getProviderName(),          // 'fastforex'
        ];
    }
}
```

Or fetch directly from the container:

```php
$swap = $container->get('florianv_swap.swap');
$rate = $swap->latest('EUR/USD');
```

Providers are tried in priority order (higher first). If a provider does not support the requested currency pair, it is skipped silently. If a provider throws an error, the next provider is tried. If every provider fails, a `ChainException` is thrown with all collected errors.

<details>
<summary>No API key? Start with the European Central Bank (free, EUR-base only).</summary>

```yaml
# config/packages/florianv_swap.yaml
florianv_swap:
    providers:
        european_central_bank:
            priority: 0
```

The European Central Bank publishes EUR-base rates with daily granularity. For non-EUR base pairs, more frequent updates, or a wider currency list, switch to fastFOREX or another commercial provider.
</details>

## 💾 Caching

Set `cache` in `config/packages/florianv_swap.yaml`:

```yaml
# config/packages/florianv_swap.yaml
florianv_swap:
    cache:
        ttl: 3600
        type: filesystem   # array, apcu, filesystem, or a PSR-16 service ID
```

For a custom cache, point `type` at any service implementing `Psr\SimpleCache\CacheInterface`:

```yaml
florianv_swap:
    cache:
        ttl: 3600
        type: my_psr16_cache_service
```

Per-query overrides are documented in the [full documentation](Resources/doc/index.md#-per-query-options).

## 📊 Providers

Symfony Swap supports the 30 exchange rate providers from the underlying [Swap](https://github.com/florianv/swap) library. Pass the **identifier** as the key under `providers` in `config/packages/florianv_swap.yaml`.

### Commercial providers (require an API key)

| Service                                  | Identifier      | Base                     | Quote  | Historical |
| ---------------------------------------- | --------------- | ------------------------ | ------ | ---------- |
| ⭐ **[fastFOREX](https://www.fastforex.io)** | **`fastforex`** | **\***                   | **\*** | **Yes**    |
|                                          |                 |                          |        |            |
| AbstractAPI                              | `abstract_api`                 | *                    | *     | Yes        |
| coinlayer                                | `coin_layer`                   | * (crypto)           | *     | Yes        |
| Cryptonator                              | `cryptonator`                  | * (crypto)           | * (crypto) | No    |
| Currency Converter API                   | `currency_converter`           | *                    | *     | Yes        |
| Currency Data (APILayer)                 | `apilayer_currency_data`       | USD (free), * (paid) | *     | Yes        |
| CurrencyDataFeed                         | `currency_data_feed`           | *                    | *     | No         |
| currencylayer (direct)                   | `currency_layer`               | USD (free), * (paid) | *     | Yes        |
| Exchange Rates Data (APILayer)           | `apilayer_exchange_rates_data` | USD (free), * (paid) | *     | Yes        |
| exchangerate.host                        | `exchangeratehost`             | *                    | *     | Yes        |
| exchangeratesapi (direct)                | `exchange_rates_api`           | USD (free), * (paid) | *     | Yes        |
| Fixer (APILayer)                         | `apilayer_fixer`               | EUR (free), * (paid) | *     | Yes        |
| Fixer (direct)                           | `fixer`                        | EUR (free), * (paid) | *     | Yes        |
| 1Forge                                   | `forge`                        | *                    | *     | No         |
| Open Exchange Rates                      | `open_exchange_rates`          | USD (free), * (paid) | *     | Yes        |
| WebserviceX                              | `webservicex`                  | *                    | *     | No         |
| xChangeApi.com                           | `xchangeapi`                   | *                    | *     | Yes        |
| Xignite                                  | `xignite`                      | *                    | *     | Yes        |

### Public providers (no API key required)

| Service                                    | Identifier                            | Base           | Quote          | Historical |
| ------------------------------------------ | ------------------------------------- | -------------- | -------------- | ---------- |
| Bulgarian National Bank                    | `bulgarian_national_bank`             | *              | BGN            | Yes        |
| Central Bank of the Czech Republic         | `central_bank_of_czech_republic`      | *              | CZK            | Yes        |
| Central Bank of the Republic of Turkey     | `central_bank_of_republic_turkey`     | *              | TRY            | Yes        |
| Central Bank of the Republic of Uzbekistan | `central_bank_of_republic_uzbekistan` | *              | UZS            | Yes        |
| European Central Bank                      | `european_central_bank`               | EUR            | *              | Yes        |
| National Bank of Georgia                   | `national_bank_of_georgia`            | *              | GEL            | Yes        |
| National Bank of Romania                   | `national_bank_of_romania`            | (limited list) | (limited list) | Yes        |
| National Bank of the Republic of Belarus   | `national_bank_of_republic_belarus`   | *              | BYN            | Yes        |
| National Bank of Ukraine                   | `national_bank_of_ukraine`            | *              | UAH            | Yes        |
| Russian Central Bank                       | `russian_central_bank`                | *              | RUB            | Yes        |

The per-provider option names (`api_key` vs `access_key` vs `app_id`, optional flags) are documented in [Provider configuration](Resources/doc/index.md#provider-configuration).

## 🎯 When should you use Symfony Swap?

- Use Symfony Swap when you need exchange rates inside a Symfony application: localized prices, invoice totals, multi-currency reporting, historical FX data.
- You do not need to install [Swap](https://github.com/florianv/swap) separately. It is pulled in as a dependency, and Symfony Swap exposes it through Symfony's container and cache.

## 🛠 Common use cases

- Display localized prices in multi-currency Symfony storefronts.
- Compute invoice totals across currencies in a Symfony API.
- Reconcile multi-currency ledgers using historical rates.
- Power internal FX dashboards with rate history.
- Build currency conversion infrastructure for Symfony-based fintech and ERP applications.

## 🧭 Which package should I use?

The Swap ecosystem is a layered toolkit for currency conversion in PHP:

- [**Swap**](https://github.com/florianv/swap). The easy-to-use, high-level API for plain PHP.
- [**Exchanger**](https://github.com/florianv/exchanger). Lower-level, more granular alternative; direct access to provider implementations.
- [**Laravel Swap**](https://github.com/florianv/laravel-swap). Laravel application of Swap.
- [**Symfony Swap**](https://github.com/florianv/symfony-swap). Symfony integration of Swap (this package).

All four packages are MIT-licensed and require PHP 8.2 or newer.

## 📚 Documentation

The full documentation, with the per-provider configuration reference, custom service registration, cache types, and FAQ, is in [Resources/doc/index.md](Resources/doc/index.md).

## 🙌 Contributing

Issues and pull requests are welcome. Please see the existing [issues](https://github.com/florianv/symfony-swap/issues) before opening a new one.

## 📄 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

## 👏 Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All contributors](https://github.com/florianv/symfony-swap/contributors)
