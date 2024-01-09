# eco-helpers

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]



Eco Helpers is an overlay to Laravel that aids in rapid application development for
security controlled CRUD applications.



## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practices by being named the following.

bin/
src/


## Install

Via Composer

``` bash
$ composer require scott-nason/eco-helpers
```

## Usage

``` php
$skeleton = new scott-nason\eco-helpers();
echo $skeleton->echoPhrase('Hello, League!');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email scott@nasonproductions.com instead of using the issue tracker.

## Credits

- [Scott Nason][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/scott-nason/eco-helpers.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/scott-nason/eco-helpers/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/scott-nason/eco-helpers.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/scott-nason/eco-helpers.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/scott-nason/eco-helpers.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/scott-nason/eco-helpers
[link-travis]: https://travis-ci.org/scott-nason/eco-helpers
[link-scrutinizer]: https://scrutinizer-ci.com/g/scott-nason/eco-helpers/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/scott-nason/eco-helpers
[link-downloads]: https://packagist.org/packages/scott-nason/eco-helpers
[link-author]: https://github.com/stnason
[link-contributors]: ../../contributors
