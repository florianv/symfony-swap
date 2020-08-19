# Documentation

## Index

* [Installation](#installation)
* [Configuration](#configuration)
  * [Services](#services)
  * [Cache](#cache)
    * [Lifetime](#lifetime)
    * [Cache type](#cache-type)
* [Usage](#usage)
* [Sponsors](#sponsors)

## Installation

```bash
composer require florianv/swap-bundle php-http/message php-http/guzzle6-adapter
```

Enable the Bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Florianv\SwapBundle\FlorianvSwapBundle(),
    );
}
```

## Configuration

### Services

We recommend to use one of the [services that support our project](#sponsors), providing a free plan up to 1,000 requests per day.

The complete list of all supported services is available here:

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        fixer:
            access_key: secret             # Fixer
        currency_layer:                    # currencylayer
            access_key: secret
            enterprise: true
        webservicex: ~                     # WebserviceX
        cryptonator: ~                     # Cryptonator
        exchange_rates_api: ~              # exchangeratesapi.io
        forge:                             # Forge
           api_key: secret    
        russian_central_bank: ~            # Russian Central Bank
        european_central_bank: ~           # European Central Bank
        national_bank_of_romania: ~        # National Bank of Romania
        central_bank_of_czech_republic: ~  # Central Bank of the Czech Republic
        central_bank_of_republic_turkey: ~ # Central Bank of the Republic of Turkey
        open_exchange_rates:               # Open Exchange Rates
            app_id: secret
            enterprise: true
        xignite:                           # Xignite
            token: secret
        currency_converter:                # Currency Converter API
            access_key: secret
            enterprise: true
        xchangeapi:                        # xChangeApi.com
           api-key: secret    
        array:
            rates:
                    'EUR/GBP': 1.5
                    'EUR/USD': 1.1
            historicalRates:
                    '2017-01-01':
                        'EUR/GBP': 1.5
                        'EUR/USD': 1.1
```

You can register multiple providers, they will be called in chain regarding to their priorities (higher first).
In this example, Swap uses the [Fixer](http://fixer.io) service, and will fallback to [currencylayer](https://currencylayer.com) in case of failure.

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        fixer:
            access_key: secret
            #priority: 0 (default)
        currency_layer:                   
            access_key: secret
            enterprise: true
            priority: 1          
```

### Cache

Currently only some of the [Symfony Cache](https://symfony.com/doc/current/components/cache.html#available-simple-cache-psr-16-classes) adapters are supported.

#### Lifetime

You must specify a lifetime for your cache entries:

```yaml
# app/config/config.yml
florianv_swap:
    cache:
        ttl: 3600 # seconds
```

#### Cache type

You can use a service id:

```yaml
# app/config/config.yml
florianv_swap:
    cache:
        type: my_cache_service
```

or one of the implemented providers (`array`, `apcu`, `filesystem`)

```yaml
# app/config/config.yml
florianv_swap:
    cache:
        type: apcu
```

## Usage

The Swap service is available in the container:

```php
/** @var \Swap\Swap $swap */
$swap = $this->get('florianv_swap.swap');
```

For more information about how to use it, please consult the [Swap documentation](https://github.com/florianv/swap).

## Sponsors

We are proudly supported by the following echange rate providers offering *free plans up to 1,000 requests per day*:

<img src="https://s3.amazonaws.com/swap.assets/fixer_icon.png?v=2" height="20px" width="20px"/> **[Fixer](https://fixer.io)**

Fixer is a simple and lightweight API for foreign exchange rates that supports up to 170 world currencies.
They provide real-time rates and historical data, however, EUR is the only available base currency on the free plan.

<img src="https://s3.amazonaws.com/swap.assets/currencylayer_icon.png" height="20px" width="20px"/> **[currencylayer](https://currencylayer.com)**

Currencylayer provides reliable exchange rates and currency conversions for your business up to 168 world currencies.
They provide real-time rates and historical data, however, USD is the only available base currency on the free plan.
