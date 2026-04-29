---
title: "Symfony Swap: drop-in Symfony bundle for currency conversion"
description: Drop-in Symfony bundle for currency conversion. Multi-provider exchange rates with fallback, caching, and Symfony Cache integration. Maintained since 2014.
---

**Drop-in Symfony bundle for currency conversion. Install, register, drop a `florianv_swap.yaml` in `config/packages/`, and inject `florianv_swap.swap`. No service container plumbing, no boilerplate.**

Wiring an exchange rate library into Symfony usually means container plumbing, cache bridging, and HTTP client management. Symfony Swap does this for you.

> Used in production Symfony applications since 2014.

Symfony Swap is a drop-in package for **Symfony currency conversion**. Install it, configure providers in `config/packages/florianv_swap.yaml`, and pull **Symfony exchange rates** from multiple providers in one call. The bundle integrates with Symfony Cache out of the box and supports Symfony 6.4 / 7 / 8.

## What is Symfony Swap?

- Symfony Swap is the Symfony integration of [Swap](https://github.com/florianv/swap), the PHP currency conversion library.
- It registers a `florianv_swap.swap` service in the container (`Swap\Swap` class).
- Configuration lives in `config/packages/florianv_swap.yaml`.
- Caching uses Symfony Cache (`array`, `apcu`, `filesystem`, or any PSR-16 service ID).
- Providers are tried in priority order (higher priority first).

## When should you use Symfony Swap?

- Use Symfony Swap when you need exchange rates inside a Symfony application: localized prices, invoice totals, multi-currency reporting, historical FX data.
- You do not need to install [Swap](https://github.com/florianv/swap) separately. It is pulled in as a dependency, and Symfony Swap exposes it through Symfony's container and cache.

## Why Symfony Swap and not raw Swap?

Using [Swap](https://github.com/florianv/swap) directly inside a Symfony app means three pieces of plumbing on every project: registering the builder and the Swap service yourself, wiring Symfony Cache to the PSR-16 contract, and configuring providers in PHP rather than in the container. Doable, but boilerplate every project pays for.

Symfony Swap does this for you:

- **Drop-in.** Add the bundle to `config/bundles.php` and you are set.
- **Symfony Cache integration.** Choose `array`, `apcu`, `filesystem`, or any PSR-16 service ID under `cache.type`.
- **Container service.** `florianv_swap.swap` is ready to inject from any controller, service, or command.
- **Configurable.** `config/packages/florianv_swap.yaml` exposes providers, options, and the cache.
- **Priority-ordered providers.** Each provider has a `priority`; the bundle sorts them (higher priority tried first).

## Quickstart

Symfony Swap requires PHP 8.2 or newer and Symfony 6.4, 7, or 8.

Install via Composer:

```bash
composer require florianv/swap-bundle symfony/http-client nyholm/psr7
```

Register the bundle in `config/bundles.php`:

```php
// config/bundles.php
return [
    // ...
    Florianv\SwapBundle\FlorianvSwapBundle::class => ['all' => true],
];
```

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

Add commercial providers under `providers:` and they will be chained in priority order.

## View on GitHub

Source code, full documentation, providers list, and issue tracker:

**[→ View on GitHub](https://github.com/florianv/symfony-swap)**

## Related packages

- [Swap](https://github.com/florianv/swap): easy-to-use PHP currency conversion library.
- [Exchanger](https://github.com/florianv/exchanger): exchange rate provider layer.
- [Laravel Swap](https://github.com/florianv/laravel-swap): Laravel application of Swap.
- [Symfony Swap](https://github.com/florianv/symfony-swap): Symfony integration of Swap (this package).

## Documentation

The full documentation, with the per-provider configuration reference, custom service registration, cache types, and FAQ, is in [Resources/doc/index.md](https://github.com/florianv/symfony-swap/blob/master/Resources/doc/index.md) on the GitHub repository.

---

_Symfony Swap is open to selected partnerships with exchange rate providers._
