# Authenticate plugin

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/authenticate?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/authenticate?label=Packagist)](https://packagist.org/packages/pieceofcake2/authenticate)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/authenticate/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/authenticate/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen?label=PHPStan)](https://github.com/pieceofcake2/authenticate/actions/workflows/CI.yml)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/authenticate/CI.yml?label=CI)](https://github.com/pieceofcake2/authenticate/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/authenticate?label=Coverage)](https://codecov.io/gh/pieceofcake2/authenticate)

Authentication objects for the `AuthComponent` of CakePHP 2 applications running on
[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp):

- `MultiColumnAuthenticate`: log in with the username or another column, such as the email
  address, in a single form field
- `CookieAuthenticate`: log in with an encrypted cookie ("remember me")
- `TokenAuthenticate`: authenticate requests with a token in a query parameter or a header

This is a fork of [FriendsOfCake/Authenticate](https://github.com/FriendsOfCake/Authenticate)
for CakePHP 2.

## Documentation

- Website: https://pieceofcake2.github.io/authenticate/ (日本語: https://pieceofcake2.github.io/authenticate/ja/)
- Markdown: [docs/README.md](docs/README.md) (日本語: [docs/ja/README.md](docs/ja/README.md)) (for AI tools and offline reading)

The Markdown documentation is also included in the Composer package, in the plugin's directory: `plugins/Authenticate/docs/README.md` (the location depends on your `installer-paths`). Install the package with `--dev` if you only need it for development.

## Requirements

- PHP 8.0+
- pieceofcake2/cakephp 2.12+

## Installation

```bash
composer require pieceofcake2/authenticate
```

Then load the plugin with `CakePlugin::load('Authenticate')` and add the authenticate objects
to the `authenticate` setting of `AuthComponent`.

## Contributing

See the [contribution guidelines](https://github.com/pieceofcake2/.github/blob/master/CONTRIBUTING.md).

## Origin

This package is a maintained fork of
[FriendsOfCake/Authenticate](https://github.com/FriendsOfCake/Authenticate)
(`friendsofcake/authenticate` on Packagist, last upstream version 1.0.2), originally by M. Ypes
(Ceeram). It is maintained for CakePHP 2 on modern PHP by
[pieceofcake2](https://github.com/pieceofcake2). Changes from the original are listed in the
[commit history since the fork](https://github.com/pieceofcake2/authenticate/compare/4d91c2b...1.x).

## License

Licensed under the MIT License. See [LICENSE](LICENSE).
