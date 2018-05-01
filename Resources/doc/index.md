# Installation

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

# Configuration

## Builtin providers

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        google: ~                          # Google Finance
        fixer:
            access_key: secret             # Fixer
        webservicex: ~                     # WebserviceX
        cryptonator: ~                     # Cryptonator
        russian_central_bank: ~            # Russian Central Bank
        european_central_bank: ~           # European Central Bank
        national_bank_of_romania: ~        # National Bank of Romania
        central_bank_of_czech_republic: ~  # Central Bank of the Czech Republic
        central_bank_of_republic_turkey: ~ # Central Bank of the Republic of Turkey
        open_exchange_rates:               # Open Exchange Rates
            app_id: secret
            enterprise: true
        currency_layer:                    # currencylayer
            access_key: secret
            enterprise: true
        xignite:                           # Xignite
            token: secret
        forge:                             # Forge
            api_key: secret
        array:
            rates:
                -
                    'EUR/GBP': 1.5
                    'EUR/USD': 1.1
                -
                    '2017-01-01':
                        'EUR/GBP': 1.5
                        'EUR/USD': 1.1
```

You can register multiple providers, they will be called in chain regarding to their priorities (higher first).
In this example __Fixer__ is the first one and __Google Finance__ is the second one:

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        google: ~
            #priority: 0 (default)
        fixer:
            access_key: secret
            priority: 1
```

## Cache

Currently only some of the [Symfony Cache](https://symfony.com/doc/current/components/cache.html#available-simple-cache-psr-16-classes) adapters are supported.

### Lifetime

You must specify a lifetime for your cache entries:

```yaml
# app/config/config.yml
florianv_swap:
    cache:
        ttl: 3600 # seconds
```

### Cache type

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

# Usage

The Swap service is available in the container:

```php
/** @var \Swap\Swap $swap */
$swap = $this->get('florianv_swap.swap');
```

For more information about how to use it, please consult the [Swap documentation](https://github.com/florianv/swap).
