# <img src="https://s3.amazonaws.com/swap.assets/swap_logo.png" height="30px" width="30px"/> Symfony Swap

[![Build status](http://img.shields.io/travis/florianv/symfony-swap.svg?style=flat-square)](https://travis-ci.org/florianv/symfony-swap)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap-bundle.svg?style=flat-square)](https://packagist.org/packages/florianv/swap-bundle)
[![Version](http://img.shields.io/packagist/v/florianv/swap-bundle.svg?style=flat-square)](https://packagist.org/packages/florianv/swap-bundle)

Swap allows you to retrieve currency exchange rates from various services such as **[Fixer](https://fixer.io)**, **[currencylayer](https://currencylayer.com)** 
or **[exchangeratesapi](https://exchangeratesapi.io)** and optionally cache the results.

## Sponsors

<table>
   <tr>
      <td><img src="https://s3.amazonaws.com/swap.assets/fixer_icon.png?v=2" width="50px"/></td>
      <td><a href="https://fixer.io">Fixer</a> is a simple and lightweight API for foreign exchange rates that supports up to 170 world currencies.</td>
   </tr>
   <tr>
     <td><img src="https://s3.amazonaws.com/swap.assets/currencylayer_icon.png" width="50px"/></td>
     <td><a href="https://currencylayer.com">currencylayer</a> provides reliable exchange rates and currency conversions for your business up to 168 world currencies.</td>
   </tr>
   <tr>
     <td><img src="https://exchangeratesapi.io/assets/images/api-logo.svg" width="50px"/></td>
     <td><a href="https://exchangeratesapi.io">exchangeratesapi</a> provides reliable exchange rates and currency conversions for your business with over 15 data sources.</td>
   </tr>    
</table>

## QuickStart

```bash
$ composer require florianv/swap-bundle php-http/message php-http/guzzle6-adapter ^1.0
```

## Documentation

The complete documentation can be found [here](https://github.com/florianv/symfony-swap/blob/master/Resources/doc/index.md).

## Services

Here is the list of the currently implemented services:

| Service | Base Currency | Quote Currency | Historical |
|---------------------------------------------------------------------------|----------------------|----------------|----------------|
| [Fixer](https://fixer.io) | EUR (free, no SSL), * (paid) | * | Yes |
| [currencylayer](https://currencylayer.com) | USD (free), * (paid) | * | Yes |
| [exchangeratesapi](https://exchangeratesapi.io) | USD (free), * (paid) | * | Yes |
| [Abstract](https://www.abstractapi.com) | * | * | No |
| [coinlayer](https://coinlayer.com) | * Crypto (Limited standard currencies) | * Crypto (Limited standard currencies) | Yes |
| [European Central Bank](https://www.ecb.europa.eu/home/html/index.en.html) | EUR | * | Yes |
| [National Bank of Romania](http://www.bnr.ro) | RON, AED, AUD, BGN, BRL, CAD, CHF, CNY, CZK, DKK, EGP, EUR, GBP, HRK, HUF, INR, JPY, KRW, MDL, MXN, NOK, NZD, PLN, RSD, RUB, SEK, TRY, UAH, USD, XAU, XDR, ZAR | RON, AED, AUD, BGN, BRL, CAD, CHF, CNY, CZK, DKK, EGP, EUR, GBP, HRK, HUF, INR, JPY, KRW, MDL, MXN, NOK, NZD, PLN, RSD, RUB, SEK, TRY, UAH, USD, XAU, XDR, ZAR | Yes |
| [Central Bank of the Republic of Turkey](http://www.tcmb.gov.tr) | * | TRY | Yes |
| [Central Bank of the Czech Republic](https://www.cnb.cz) | * | CZK | Yes |
| [Central Bank of Russia](https://cbr.ru) | * | RUB | Yes |
| [Bulgarian National Bank](http://bnb.bg) | * | BGN | Yes |
| [WebserviceX](http://www.webservicex.net) | * | * | No |
| [1Forge](https://1forge.com) | * (free but limited or paid) | * (free but limited or paid) | No |
| [Cryptonator](https://www.cryptonator.com) | * Crypto (Limited standard currencies) | * Crypto (Limited standard currencies)  | No |
| [CurrencyDataFeed](https://currencydatafeed.com) | * (free but limited or paid) | * (free but limited or paid) | No |
| [Open Exchange Rates](https://openexchangerates.org) | USD (free), * (paid) | * | Yes |
| [Xignite](https://www.xignite.com) | * | * | Yes |
| [Currency Converter API](https://www.currencyconverterapi.com) | * | * | Yes (free but limited or paid) |
| [xChangeApi.com](https://xchangeapi.com) | * | * | Yes |
| [fastFOREX.io](https://www.fastforex.io) | USD (free), * (paid) | * | No |
| Array | * | * | Yes |

## Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All Contributors](https://github.com/florianv/symfony-swap/contributors)

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/florianv/symfony-swap/blob/master/Resources/meta/LICENSE) for more information.
