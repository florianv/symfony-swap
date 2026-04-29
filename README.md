# Symfony Swap

[![Tests](https://github.com/florianv/symfony-swap/actions/workflows/tests.yml/badge.svg)](https://github.com/florianv/symfony-swap/actions/workflows/tests.yml)
[![Psalm](https://github.com/florianv/symfony-swap/actions/workflows/psalm.yml/badge.svg)](https://github.com/florianv/symfony-swap/actions/workflows/psalm.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap-bundle.svg?style=flat-square)](https://packagist.org/packages/florianv/swap-bundle)
[![Version](http://img.shields.io/packagist/v/florianv/swap-bundle.svg?style=flat-square)](https://packagist.org/packages/florianv/swap-bundle)

> _Drop-in Symfony bundle for currency conversion. Multi-provider exchange rates with fallback, caching, and Symfony Cache integration. Maintained since 2014._

**Install the bundle, drop a `florianv_swap.yaml` in `config/packages/`, and the `florianv_swap.swap` service is ready to inject. No service container plumbing, no boilerplate.**

Symfony Swap is a drop-in package for **Symfony currency conversion**. Install it, configure providers in `config/packages/florianv_swap.yaml`, and pull **Symfony exchange rates** from multiple providers in one call. The bundle integrates with Symfony Cache out of the box and supports Symfony 6.4 / 7 / 8. Used in real-world Symfony applications since 2014.

## 💡 What is Symfony Swap?

- Symfony Swap is the Symfony integration of [Swap](https://github.com/florianv/swap), the PHP currency conversion library.
- It registers a `florianv_swap.swap` service in the container (`Swap\Swap` class).
- Configuration lives in `config/packages/florianv_swap.yaml`.
- Caching uses Symfony Cache (`array`, `apcu`, `filesystem`, or any PSR-16 service ID).
- Providers are tried in priority order (higher priority first).

## 🎯 When should you use Symfony Swap?

- Use Symfony Swap when you need exchange rates inside a Symfony application: localized prices, invoice totals, multi-currency reporting, historical FX data.
- You do not need to install [Swap](https://github.com/florianv/swap) separately. It is pulled in as a dependency, and Symfony Swap exposes it through Symfony's container and cache.

## 🧠 Why Symfony Swap and not raw Swap?

Using [Swap](https://github.com/florianv/swap) directly inside a Symfony app means three pieces of plumbing on every project: registering the builder and the Swap service yourself, wiring Symfony Cache to the PSR-16 contract, and configuring providers in PHP rather than in the container. Doable, but boilerplate every project pays for.

Symfony Swap does this for you:

- **Drop-in.** Add the bundle to `config/bundles.php` and you are set.
- **Symfony Cache integration.** Choose `array`, `apcu`, `filesystem`, or any PSR-16 service ID under `cache.type`.
- **Container service.** `florianv_swap.swap` is ready to inject from any controller, service, or command.
- **Configurable.** `config/packages/florianv_swap.yaml` exposes providers, options, and the cache.
- **Priority-ordered providers.** Each provider has a `priority`; the bundle sorts them (higher priority tried first).

If you are not on Symfony, use [Swap](https://github.com/florianv/swap) directly.

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

Skip to [Quickstart](#-quickstart).

---

_Optional: any PSR-18 HTTP client paired with a PSR-17 factory works. If your app already uses Guzzle, swap `symfony/http-client` for `php-http/guzzle7-adapter`. See the [documentation](Resources/doc/index.md) for alternatives._

## ⚡ Quickstart

Configure at least one provider in `config/packages/florianv_swap.yaml`. The European Central Bank works without an API key:

```yaml
# config/packages/florianv_swap.yaml
florianv_swap:
    providers:
        european_central_bank:
            priority: 0
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
            'provider' => $rate->getProviderName(),          // 'european_central_bank'
        ];
    }
}
```

Or fetch directly from the container:

```php
$swap = $container->get('florianv_swap.swap');
$rate = $swap->latest('EUR/USD');
```

Add commercial providers under `providers:` and they will be chained with the configured priority order:

```yaml
# config/packages/florianv_swap.yaml
florianv_swap:
    cache:
        ttl: 3600
        type: filesystem
    providers:
        apilayer_fixer:
            api_key: '%env(SWAP_FIXER_KEY)%'
            priority: 10                  # tried first
        open_exchange_rates:
            app_id: '%env(SWAP_OER_APP_ID)%'
            priority: 5                   # tried second
        european_central_bank:
            priority: 0                   # free fallback for EUR-base pairs
```

Providers are tried in priority order (higher first). If a provider does not support the requested currency pair, it is skipped silently. If a provider throws an error, the next provider is tried. If every provider fails, a `ChainException` is thrown with all collected errors.

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

Per-query overrides are documented in the [full documentation](Resources/doc/index.md#-caching).

## 🛠 Common use cases

- Display localized prices in multi-currency Symfony storefronts.
- Compute invoice totals across currencies in a Symfony API.
- Reconcile multi-currency ledgers using historical rates.
- Power internal FX dashboards with rate history.
- Build currency conversion infrastructure for Symfony-based fintech and ERP applications.

## 🧭 Which package should I use?

The Swap ecosystem is a layered toolkit for currency conversion in PHP:

- **Swap.** The easy-to-use, high-level API for plain PHP.
- **Exchanger.** Lower-level, more granular alternative; direct access to provider implementations.
- **Laravel Swap.** Laravel application of Swap.
- **Symfony Swap.** Symfony integration of Swap (this package).

All four packages are MIT-licensed and require PHP 8.2 or newer.

## 📚 Documentation

The full documentation, with the per-provider configuration reference, custom service registration, cache types, and FAQ, is in [Resources/doc/index.md](Resources/doc/index.md). The full provider list with capabilities is in the [Swap README](https://github.com/florianv/swap#-providers).

## 🧩 Related packages

The Swap ecosystem:

- [**Swap**](https://github.com/florianv/swap): easy-to-use PHP currency conversion library.
- [**Exchanger**](https://github.com/florianv/exchanger): exchange rate provider layer.
- [**Laravel Swap**](https://github.com/florianv/laravel-swap): Laravel application of Swap.
- [**Symfony Swap**](https://github.com/florianv/symfony-swap): Symfony integration of Swap (this package).

## 🤝 Sponsorship

The Swap ecosystem is open to selected sponsorships from exchange rate API providers and financial infrastructure companies.

Sponsorship can include:

- Documentation visibility
- Integration examples
- Ecosystem-level visibility across Swap, Exchanger, Laravel Swap, and Symfony Swap

For inquiries, contact the maintainer via [GitHub](https://github.com/florianv).

## 🙌 Contributing

Issues and pull requests are welcome. Please see the existing [issues](https://github.com/florianv/symfony-swap/issues) before opening a new one.

## 📄 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

## 👏 Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All contributors](https://github.com/florianv/symfony-swap/contributors)
