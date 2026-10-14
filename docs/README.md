# Authenticate Documentation

The Authenticate plugin provides authentication objects for the `AuthComponent` of
CakePHP 2 applications running on [pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp):

| Class | Name in `authenticate` | Description |
| --- | --- | --- |
| `MultiColumnAuthenticate` | `Authenticate.MultiColumn` | Log in with form data, matching the username field against several columns, for example the username or the email address. |
| `CookieAuthenticate` | `Authenticate.Cookie` | Log in with the credentials stored in an encrypted cookie ("remember me"). |
| `TokenAuthenticate` | `Authenticate.Token` | Authenticate each request with a token given as a query parameter or a request header. |

The former `GoogleAuthenticate` class is not part of this plugin; it was moved to
[ceeram/GoogleAuthenticate](https://github.com/ceeram/GoogleAuthenticate).

- [Installation](#installation)
- [MultiColumnAuthenticate](#multicolumnauthenticate)
- [CookieAuthenticate](#cookieauthenticate)
- [TokenAuthenticate](#tokenauthenticate)
- [Combining Authenticate Objects](#combining-authenticate-objects)

## Installation

Install the plugin with [Composer](https://getcomposer.org/):

```bash
composer require pieceofcake2/authenticate
```

The plugin has the `cakephp-plugin` type, so it is installed into your plugins directory
by `composer/installers`. Set `installer-paths` in your application's `composer.json` so it
is installed into the right place:

| Layout | `installer-paths` |
| --- | --- |
| Modern (`config/`, `src/`, `templates/`, `plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| Legacy (`app/Config/`, `app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

Load the plugin in `config/bootstrap.php` (legacy layout: `app/Config/bootstrap.php`).
If you already use `CakePlugin::loadAll()`, this is not necessary:

```php
CakePlugin::load('Authenticate');
```

Then configure the authenticate objects in the `authenticate` setting of `AuthComponent`,
either in the `$components` property of your controller or in `beforeFilter()`, as shown
below.

## MultiColumnAuthenticate

`MultiColumnAuthenticate` works like `FormAuthenticate`, but the value of the username field
of the login form is searched in all the `columns`. With the settings below a user can log
in with either the username or the email address, typed into the `login` field:

```php
class AppController extends Controller
{
    public $components = [
        'Auth' => [
            'authenticate' => [
                'Authenticate.MultiColumn' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password',
                    ],
                    'columns' => ['username', 'email'],
                    'userModel' => 'User',
                    'scope' => ['User.active' => 1],
                ],
            ],
        ],
    ];
}
```

Or in `beforeFilter()`:

```php
class AppController extends Controller
{
    public $components = ['Auth'];

    public function beforeFilter()
    {
        parent::beforeFilter();

        $this->Auth->authenticate = [
            'Authenticate.MultiColumn' => [
                'fields' => [
                    'username' => 'login',
                    'password' => 'password',
                ],
                'columns' => ['username', 'email'],
                'userModel' => 'User',
                'scope' => ['User.active' => 1],
            ],
        ];
    }
}
```

| Setting | Description |
| --- | --- |
| `fields` | The form fields of the username and the password (default: `username` and `password`). |
| `columns` | The columns of the user model that the username is matched against. When empty, the `username` field is used as the column. |
| `userModel` | The user model (default: `User`). Plugin models can be given as `Plugin.Model`. |
| `scope` | Additional conditions for finding the user. |
| `contain` | Associated models to fetch with the user. |

The password is compared with `Security::hash()`, the hash of the default
`SimplePasswordHasher`. The `passwordHasher` setting is not supported, so passwords hashed
with `BlowfishPasswordHasher` cannot be checked.

## CookieAuthenticate

`CookieAuthenticate` reads the username and the password from an encrypted cookie written
by the `CookieComponent`, and logs the user in when they match. The cookie is named after
the user model (`User` for `User` or `SomePlugin.User`).

```php
class AppController extends Controller
{
    public $components = [
        'Cookie',
        'Auth' => [
            'authenticate' => [
                'Authenticate.Cookie' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password',
                    ],
                    'userModel' => 'SomePlugin.User',
                    'scope' => ['User.active' => 1],
                    'crypt' => 'aes',
                ],
            ],
        ],
    ];
}
```

| Setting | Description |
| --- | --- |
| `fields` | The keys of the username and the password in the cookie (default: `username` and `password`). |
| `userModel` | The user model (default: `User`). |
| `scope` | Additional conditions for finding the user. |
| `crypt` | The encryption type of the cookie, passed to `CookieComponent::type()` (default: `rijndael`). |

The `Cookie` component must be loaded, or a `CakeException` is thrown. On logout
(`AuthComponent::logout()`) the cookies of the `CookieComponent` are destroyed.

> [!IMPORTANT]
> Set `crypt` to `aes`. The default, `rijndael`, needs the mcrypt extension, which is not
> available for PHP 7.2 and later. `aes` uses OpenSSL when `Security.useOpenSsl` is `true`
> in `config/core.php` (the [pieceofcake2/app](https://github.com/pieceofcake2/app) skeleton
> sets it). Use the same type when you write the cookie.

### Setting the Cookie

The cookie is written by your application, for example when the user logs in with a
"remember me" checkbox. The cookie holds the plain password, encrypted, so always use an
encryption type and limit its lifetime:

```php
class UsersController extends AppController
{
    public $components = ['Cookie', 'Flash'];

    public function beforeFilter()
    {
        parent::beforeFilter();

        $this->Cookie->type('aes');
    }

    public function login()
    {
        if ($this->Auth->loggedIn() || $this->Auth->login()) {
            $this->_setCookie();

            return $this->redirect($this->Auth->redirectUrl());
        }
    }

    public function logout()
    {
        $this->Flash->success('Logged out');

        return $this->redirect($this->Auth->logout());
    }

    protected function _setCookie()
    {
        if (!$this->request->data('User.remember_me')) {
            return false;
        }
        $data = [
            'login' => $this->request->data('User.login'),
            'password' => $this->request->data('User.password'),
        ];
        $this->Cookie->write('User', $data, true, '+1 week');

        return true;
    }
}
```

The keys of the cookie data must match the `fields` setting.

## TokenAuthenticate

`TokenAuthenticate` authenticates stateless requests, for example to an API, with a token
stored in a column of the user model. The token is read from the request header given in
`header`, or from the query parameter given in `parameter` (`/api/posts.json?_token=...`).

```php
class AppController extends Controller
{
    public $components = [
        'Auth' => [
            'authenticate' => [
                'Authenticate.Token' => [
                    'parameter' => '_token',
                    'header' => 'X-MyApiTokenHeader',
                    'userModel' => 'User',
                    'scope' => ['User.active' => 1],
                    'fields' => [
                        'username' => 'username',
                        'password' => 'password',
                        'token' => 'public_key',
                    ],
                    'continue' => true,
                ],
            ],
        ],
    ];
}
```

| Setting | Description |
| --- | --- |
| `parameter` | The query parameter of the token (default: `_token`). |
| `header` | The request header of the token (default: `X-ApiToken`). At least one of `parameter` and `header` is required. |
| `userModel` | The user model (default: `User`). |
| `fields` | `token` is the column that holds the token (default: `token`). The `password` column is removed from the user data. |
| `scope` | Additional conditions for finding the user. |
| `recursive` | The `recursive` option of the find (default: `0`). |
| `contain` | Associated models to fetch with the user. |
| `continue` | When `true`, `AuthComponent` handles unauthenticated requests as usual: a redirect to the login action, or a 403 response for Ajax and JSON requests. When `false` (the default), the `unauthorized` exception is thrown. This applies only when `Authenticate.Token` is the last authenticate object, because `AuthComponent` asks only the last one. |
| `unauthorized` | The exception class to throw, or an HTTP status code for an `HttpException` (default: `BadRequestException`). |

## Combining Authenticate Objects

The authenticate objects are tried in order. With the settings below the cookie is checked
first, and the form data next:

```php
class AppController extends Controller
{
    public $components = [
        'Cookie',
        'Auth' => [
            'authenticate' => [
                'Authenticate.Cookie' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password',
                    ],
                    'userModel' => 'SomePlugin.User',
                    'scope' => ['User.active' => 1],
                    'crypt' => 'aes',
                ],
                'Authenticate.MultiColumn' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password',
                    ],
                    'columns' => ['username', 'email'],
                    'userModel' => 'User',
                    'scope' => ['User.active' => 1],
                ],
            ],
        ],
    ];
}
```
