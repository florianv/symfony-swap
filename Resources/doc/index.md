# Installation

Add this line to your `composer.json` file:

```json
{
    "require": {
        "florianv/swap-bundle": "~1.0"
    }
}
```

Update the dependency by running:

```bash
$ php composer.phar update florianv/swap-bundle
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

Finally, enable its configuration:

```yaml
# app/config/config.yml
florianv_swap: ~
```

# Configuration

## Builtin providers

### Yahoo Finance

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        yahoo_finance: ~
```

### Google Finance

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        google_finance: ~
```

### European Central Bank

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        european_central_bank: ~
```

### Open Exchange Rates

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        open_exchange_rates:
            # Your app id
            app_id: secret

            # True if your AppId is an enterprise one. Defaults to false
            enterprise: true
```

### Xignite

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        xignite:
            # Your API token
            token: secret
```

### WebserviceX

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        webservicex: ~
```

You can register multiple providers, they will be called in chain. In this example the Yahoo Finance is
the first one and Google Finance is the second one:

```yaml
# app/config/config.yml
florianv_swap:
    providers:
        yahoo_finance: ~
        google_finance: ~
```

## Custom providers

In order to add your custom providers implementing `Swap\ProviderInterface`, you can tag them as `florianv_swap.provider`:

```xml
<service id="acme_demo.provider.custom" class="Acme\DemoBundle\Provider\Custom">
    <tag name="florianv_swap.provider" />
</service>
```

# Usage

The Swap service is available in the container:

```php
/** @var \Swap\SwapInterface $swap */
$swap = $this->get('florianv_swap.swap');
```

For more information about how to use it, please consult the [Swap documentation](https://github.com/florianv/swap).
