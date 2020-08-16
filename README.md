# <img src="https://s3.amazonaws.com/swap.assets/swap_logo.png" height="30px" width="30px"/> Symfony Swap

[![Build status](http://img.shields.io/travis/florianv/symfony-swap.svg?style=flat-square)](https://travis-ci.org/florianv/symfony-swap)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap-bundle.svg?style=flat-square)](https://packagist.org/packages/florianv/swap-bundle)
[![Version](http://img.shields.io/packagist/v/florianv/swap-bundle.svg?style=flat-square)](https://packagist.org/packages/florianv/swap-bundle)

Swap allows you to retrieve currency exchange rates from various services such as **[Fixer](https://fixer.io)** or **[currencylayer](https://currencylayer.com)** and optionally cache the results.

## QuickStart

```bash
$ composer require florianv/swap-bundle php-http/message php-http/guzzle6-adapter ^1.0
```

## Documentation

The complete documentation can be found [here](https://github.com/florianv/symfony-swap/blob/master/Resources/doc/index.md).

## Sponsors :heart_eyes: 

We are proudly supported by the following echange rate providers offering *free plans up to 1,000 requests per day*:

<img src="https://s3.amazonaws.com/swap.assets/fixer_icon.png?v=2" height="20px" width="20px"/> **[Fixer](https://fixer.io)**

Fixer is a simple and lightweight API for foreign exchange rates that supports up to 170 world currencies.
They provide real-time rates and historical data, however, EUR is the only available base currency on the free plan.

<img src="https://s3.amazonaws.com/swap.assets/currencylayer_icon.png" height="20px" width="20px"/> **[currencylayer](https://currencylayer.com)**

Currencylayer provides reliable exchange rates and currency conversions for your business up to 168 world currencies.
They provide real-time rates and historical data, however, USD is the only available base currency on the free plan.

## Services

Here is the list of the currently implemented services:

| Service | Base Currency | Quote Currency | Historical |
|---------------------------------------------------------------------------|----------------------|----------------|----------------|
| [Fixer](https://fixer.io) | EUR (free, no SSL), * (paid) | * | Yes |
| [currencylayer](https://currencylayer.com) | USD (free), * (paid) | * | Yes |
| [European Central Bank](https://www.ecb.europa.eu/home/html/index.en.html) | EUR | * | Yes |
| [National Bank of Romania](http://www.bnr.ro) | RON | * | Yes |
| [Central Bank of the Republic of Turkey](http://www.tcmb.gov.tr) | * | TRY | Yes |
| [Central Bank of the Czech Republic](https://www.cnb.cz) | * | CZK | Yes |
| [Central Bank of Russia](https://cbr.ru) | * | RUB | Yes |
| [WebserviceX](http://www.webservicex.net) | * | * | No |
| [1Forge](https://1forge.com) | * (free but limited or paid) | * (free but limited or paid) | No |
| [Cryptonator](https://www.cryptonator.com) | * Crypto (Limited standard currencies) | * Crypto (Limited standard currencies)  | No |
| [CurrencyDataFeed](https://currencydatafeed.com) | * (free but limited or paid) | * (free but limited or paid) | No |
| [Open Exchange Rates](https://openexchangerates.org) | USD (free), * (paid) | * | Yes |
| [Xignite](https://www.xignite.com) | * | * | Yes |
| [CurrencyConverterApi](https://www.currencyconverterapi.com) | * | * | Yes (free but limited or paid) |
| [xChangeApi.com](https://xchangeapi.com) | * | * | Yes |
| Array | * | * | Yes |

## Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All Contributors](https://github.com/florianv/symfony-swap/contributors)

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/florianv/symfony-swap/blob/master/Resources/meta/LICENSE) for more information.
