# Authenticate plugin

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/authenticate?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/authenticate?label=Packagist)](https://packagist.org/packages/pieceofcake2/authenticate)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/authenticate/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/authenticate/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/authenticate/CI.yml?label=CI)](https://github.com/pieceofcake2/authenticate/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/authenticate?label=Coverage)](https://codecov.io/gh/pieceofcake2/authenticate)

__This is forked for CakePHP2.__

Plugin containing some authenticate classes for AuthComponent.

Current classes:
* MultiColumnAuthenticate, allow login with multiple db columns in single username field
  For example username or email
* CookieAuthenticate, login with a cookie
* TokenAuthenticate, login with a token as url parameter or header

GoogleAuthenticate is moved to separate repo: https://github.com/ceeram/GoogleAuthenticate

## Requirements

* PHP 8.0+
* CakePHP 2.10+

## Installation

run: `composer require pieceofcake2/authenticate`

## Usage

In `app/Config/bootstrap.php` add: `CakePlugin::load('Authenticate')`;

## Configuration:

Setup the authentication class settings

### MultiColumnAuthenticate:

```php
    //in $components
    public $components = [
        'Auth' => [
            'authenticate' => [
                'Authenticate.MultiColumn' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password'
                    ],
                    'columns' => ['username', 'email'],
                    'userModel' => 'User',
                    'scope' => ['User.active' => 1],
                ]
            ]
        ]
    ];

    //Or in beforeFilter()
    $this->Auth->authenticate = [
        'Authenticate.MultiColumn' => [
            'fields' => [
                'username' => 'login',
                'password' => 'password'
            ],
            'columns' => ['username', 'email'],
            'userModel' => 'User',
            'scope' => ['User.active' => 1],
        ]
    ];
```

### CookieAuthenticate:

```php
    //in $components
    public $components = [
        'Auth' => [
            'authenticate' => [
                'Authenticate.Cookie' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password'
                    ],
                    'userModel' => 'SomePlugin.User',
                    'scope' => ['User.active' => 1],
                ]
            ]
        ]
    ];

    //Or in beforeFilter()
    $this->Auth->authenticate = [
        'Authenticate.Cookie' => [
            'fields' => [
                'username' => 'login',
                'password' => 'password'
            ],
            'userModel' => 'SomePlugin.User',
            'scope' => ['User.active' => 1],
        ]
    ];
```

### Setup both:

It will first try to read the cookie, if that fails will try with form data:

```php
    //in $components
    public $components = [
        'Auth' => [
            'authenticate' => [
                'Authenticate.Cookie' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password'
                    ],
                    'userModel' => 'SomePlugin.User',
                    'scope' => ['User.active' => 1],
                ],
                'Authenticate.MultiColumn' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password'
                    ],
                    'columns' => ['username', 'email'],
                    'userModel' => 'User',
                    'scope' => ['User.active' => 1],
                ]
            ]
        ]
    ];
```

### Security

For enhanced security, make sure you add this code to your `AppController::beforeFilter()` if you intend to use Cookie
authentication:

```php
public function beforeFilter() {
  $this->Cookie->type('rijndael'); //Enable AES symetric encryption of cookie
}
```

### Setting the cookie

Example for setting the cookie:
```php
<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController
{
    public $components = ['Cookie'];

    public function beforeFilter() {
        $this->Cookie->type('rijndael');
    }

    public function login() {
        if ($this->Auth->loggedIn() || $this->Auth->login()) {
            $this->_setCookie();
            $this->redirect($this->Auth->redirect());
        }
    }

    protected function _setCookie() {
        if (!$this->request->data('User.remember_me')) {
            return false;
        }
        $data = [
            'username' => $this->request->data('User.username'),
            'password' => $this->request->data('User.password')
        ];
        $this->Cookie->write('User', $data, true, '+1 week');
        return true;
    }

    public function logout() {
        $this->Auth->logout();
        $this->Session->setFlash('Logged out');
        $this->redirect($this->Auth->redirect('/'));
    }
}
```

### TokenAuthenticate

```php
    //in $components
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
                ]
            ]
        ]
    ];

    //Or in beforeFilter()
    $this->Auth->authenticate = [
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
        ]
    ];
```
