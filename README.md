# clue/sse-react

[![CI status](https://github.com/clue/php-sse-react/workflows/CI/badge.svg)](https://github.com/clue/php-sse-react/actions)
[![installs on Packagist](https://img.shields.io/packagist/dt/clue/sse-react?color=blue&label=installs%20on%20Packagist)](https://packagist.org/packages/clue/sse-react)

Streaming, async HTML5 Server-Sent Events server (aka. SSE or EventSource), built on top of [React PHP](http://reactphp.org/).

> Note: This project is in early alpha stage! Feel free to report any issues you encounter.

## Quickstart example

See the [examples](examples).

## Install

The recommended way to install this library is [through composer](http://getcomposer.org). [New to composer?](http://getcomposer.org/doc/00-intro.md)

```json
{
    "require": {
        "clue/sse-react": "dev-master"
    }
}
```

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](http://getcomposer.org):

```sh
composer install
```

To run the test suite, go to the project root and run:

```sh
php vendor/bin/phpunit
```

## License

MIT
