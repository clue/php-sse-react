# clue/sse-react

[![CI status](https://github.com/clue/php-sse-react/actions/workflows/ci.yml/badge.svg)](https://github.com/clue/php-sse-react/actions)
[![installs on Packagist](https://img.shields.io/packagist/dt/clue/sse-react?color=blue&label=installs%20on%20Packagist)](https://packagist.org/packages/clue/sse-react)

Streaming, async HTML5 Server-Sent Events server (aka. SSE or EventSource), built on top of [React PHP](http://reactphp.org/).

> Note: This project is in early alpha stage! Feel free to report any issues you encounter.

## Quickstart example

See the [examples](examples).

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org/).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

Once released, this project will follow [SemVer](https://semver.org/).
At the moment, this will install the latest development version:

```JSON
{
    "require": {
        "clue/sse-react": "dev-master"
    }
}
```

This project aims to run on any platform and thus does not require any PHP
extensions and supports running on legacy PHP 5.3 through PHP 7.3.
It's *highly recommended to use the latest supported PHP version* for this project.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](http://getcomposer.org/):

```bash
composer install
```

To run the test suite, go to the project root and run:

```bash
php vendor/bin/phpunit
```

## License

MIT, see [LICENSE file](LICENSE).
