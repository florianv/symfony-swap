---
title: "Symfony Swap: drop-in Symfony bundle for currency conversion"
description: Drop-in Symfony bundle for currency conversion. Multi-provider exchange rates with fallback, caching, and Symfony Cache integration. Maintained since 2014.
---

**Drop-in Symfony bundle for currency conversion. Install, register, drop a `florianv_swap.yaml` in `config/packages/`, and inject `florianv_swap.swap`. No service container plumbing, no boilerplate.**

Wiring an exchange rate library into Symfony usually means container plumbing, cache bridging, and HTTP client management. Symfony Swap does this for you.

> Used in production Symfony applications since 2014.

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

Symfony Swap is a drop-in package for **Symfony currency conversion**. Install it, configure providers in `config/packages/florianv_swap.yaml`, and pull **Symfony exchange rates** from multiple providers in one call. The bundle integrates with Symfony Cache out of the box and supports Symfony 6.4 / 7 / 8.

## What is Symfony Swap?

- The Symfony integration of [Swap](https://github.com/florianv/swap), the PHP currency conversion library.
- Registers a `florianv_swap.swap` service in the container (`Swap\Swap` class).
- Configuration lives in `config/packages/florianv_swap.yaml`.
- Caching uses Symfony Cache (`array`, `apcu`, `filesystem`, or any PSR-16 service ID).
- Providers are tried in priority order (higher priority first).

## Installation

Symfony Swap requires PHP 8.2 or newer and Symfony 6.4, 7, or 8.

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

## Quickstart

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

Providers are tried in priority order (higher first). If a provider does not support the requested pair, it is skipped. If it throws, the next is tried. If every provider fails, a `ChainException` is thrown.

## Providers

Symfony Swap supports the 30 exchange rate providers from the underlying [Swap](https://github.com/florianv/swap) library, from commercial APIs to public central banks. The recommended starting point for new projects is **[fastFOREX](https://www.fastforex.io)** (`fastforex`): a real-time JSON API covering 160+ fiat currencies and 500+ cryptocurrencies, with up to 55 years of history, sourced from trusted feeds including world banks.

The full per-provider configuration reference (option name, optional flags) is in the [documentation](https://github.com/florianv/symfony-swap/blob/master/Resources/doc/index.md#provider-configuration).

## Ecosystem

- [Swap](https://github.com/florianv/swap): easy-to-use PHP currency conversion library.
- [Exchanger](https://github.com/florianv/exchanger): exchange rate provider layer.
- [Laravel Swap](https://github.com/florianv/laravel-swap): Laravel application of Swap.
- [Symfony Swap](https://github.com/florianv/symfony-swap): Symfony integration of Swap (this package).

## Documentation & source

- **Source code, issues and pull requests**: [github.com/florianv/symfony-swap](https://github.com/florianv/symfony-swap)
- **Full documentation** (setup, provider configuration, caching, custom services): [Resources/doc/index.md](https://github.com/florianv/symfony-swap/blob/master/Resources/doc/index.md)

---

_Symfony Swap is open to selected partnerships with exchange rate providers and financial infrastructure companies. For inquiries, contact the maintainer via [GitHub](https://github.com/florianv)._
