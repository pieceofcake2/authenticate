# Authenticate ドキュメント

Authenticate プラグインは、[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp) で動く
CakePHP 2 アプリケーションの `AuthComponent` のための認証オブジェクトを提供します。

| クラス | `authenticate` での名前 | 説明 |
| --- | --- | --- |
| `MultiColumnAuthenticate` | `Authenticate.MultiColumn` | フォームのデータでログインします。ユーザー名のフィールドの値を、たとえばユーザー名とメールアドレスのように複数のカラムと照合します。 |
| `CookieAuthenticate` | `Authenticate.Cookie` | 暗号化された Cookie に保存された認証情報でログインします (「ログイン状態を保持する」)。 |
| `TokenAuthenticate` | `Authenticate.Token` | クエリパラメータまたはリクエストヘッダーで渡されたトークンで、リクエストごとに認証します。 |

以前の `GoogleAuthenticate` クラスはこのプラグインには含まれていません。
[ceeram/GoogleAuthenticate](https://github.com/ceeram/GoogleAuthenticate) に移動し、現在は
[pieceofcake2/totp](https://pieceofcake2.github.io/totp/) (TOTP による 2 要素認証) としてメンテナンスされています。

- [インストール](#installation)
- [MultiColumnAuthenticate](#multicolumnauthenticate)
- [CookieAuthenticate](#cookieauthenticate)
- [TokenAuthenticate](#tokenauthenticate)
- [認証オブジェクトの組み合わせ](#combining-authenticate-objects)

<a id="installation"></a>

## インストール

[Composer](https://getcomposer.org/) でプラグインをインストールします。

```bash
composer require pieceofcake2/authenticate
```

このプラグインは `cakephp-plugin` タイプなので、`composer/installers` によってプラグインディレクトリに
インストールされます。正しい場所にインストールされるように、アプリケーションの `composer.json` で
`installer-paths` を設定してください。

| レイアウト | `installer-paths` |
| --- | --- |
| モダン (`config/`、`src/`、`templates/`、`plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| レガシー (`app/Config/`、`app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

`config/bootstrap.php` (レガシーレイアウトでは `app/Config/bootstrap.php`) でプラグインを読み込みます。
すでに `CakePlugin::loadAll()` を使っている場合は不要です。

```php
CakePlugin::load('Authenticate');
```

次に、以下に示すように、コントローラーの `$components` プロパティか `beforeFilter()` で、
`AuthComponent` の `authenticate` 設定に認証オブジェクトを設定します。

<a id="multicolumnauthenticate"></a>

## MultiColumnAuthenticate

`MultiColumnAuthenticate` は `FormAuthenticate` と同じように動作しますが、ログインフォームのユーザー名の
フィールドの値を `columns` のすべてのカラムから検索します。次の設定では、ユーザーは `login`
フィールドにユーザー名とメールアドレスのどちらを入力してもログインできます。

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

または `beforeFilter()` で設定します。

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

| 設定 | 説明 |
| --- | --- |
| `fields` | ユーザー名とパスワードのフォームのフィールド (デフォルト: `username` と `password`)。 |
| `columns` | ユーザー名と照合するユーザーモデルのカラム。空の場合は `username` フィールドをカラムとして使います。 |
| `userModel` | ユーザーモデル (デフォルト: `User`)。プラグインのモデルは `Plugin.Model` の形式で指定できます。 |
| `scope` | ユーザーを検索するときの追加の条件。 |
| `contain` | ユーザーと一緒に取得するアソシエーション先のモデル。 |

パスワードは、デフォルトの `SimplePasswordHasher` のハッシュである `Security::hash()` で比較されます。
`passwordHasher` 設定はサポートしていないので、`BlowfishPasswordHasher` でハッシュ化したパスワードは
検証できません。

<a id="cookieauthenticate"></a>

## CookieAuthenticate

`CookieAuthenticate` は、`CookieComponent` が書き込んだ暗号化された Cookie からユーザー名とパスワードを
読み込み、一致すればユーザーをログインさせます。Cookie の名前はユーザーモデルの名前になります
(`User` や `SomePlugin.User` の場合は `User`)。

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

| 設定 | 説明 |
| --- | --- |
| `fields` | Cookie 内のユーザー名とパスワードのキー (デフォルト: `username` と `password`)。 |
| `userModel` | ユーザーモデル (デフォルト: `User`)。 |
| `scope` | ユーザーを検索するときの追加の条件。 |
| `crypt` | Cookie の暗号化のタイプ。`CookieComponent::type()` に渡されます (デフォルト: `rijndael`)。 |

`Cookie` コンポーネントを読み込んでおく必要があります。読み込んでいない場合は `CakeException` が
スローされます。ログアウト時 (`AuthComponent::logout()`) には、`CookieComponent` の Cookie が削除されます。

> [!IMPORTANT]
> `crypt` は `aes` に設定してください。デフォルトの `rijndael` は mcrypt エクステンションが必要ですが、
> mcrypt は PHP 7.2 以降では使えません。`aes` は、`config/core.php` で `Security.useOpenSsl` が `true`
> の場合に OpenSSL を使います ([pieceofcake2/app](https://github.com/pieceofcake2/app) スケルトンでは
> 設定済みです)。Cookie を書き込むときも同じタイプを使ってください。

<a id="setting-the-cookie"></a>

### Cookie の設定

Cookie はアプリケーションで書き込みます。たとえば、ユーザーが「ログイン状態を保持する」のチェックボックスを
付けてログインしたときに書き込みます。Cookie には平文のパスワードを暗号化して保存するので、必ず暗号化の
タイプを指定し、有効期間を制限してください。

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

Cookie のデータのキーは、`fields` 設定と一致している必要があります。

<a id="tokenauthenticate"></a>

## TokenAuthenticate

`TokenAuthenticate` は、たとえば API へのステートレスなリクエストを、ユーザーモデルのカラムに保存された
トークンで認証します。トークンは、`header` で指定したリクエストヘッダーか、`parameter` で指定した
クエリパラメータ (`/api/posts.json?_token=...`) から読み込みます。

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

| 設定 | 説明 |
| --- | --- |
| `parameter` | トークンのクエリパラメータ (デフォルト: `_token`)。 |
| `header` | トークンのリクエストヘッダー (デフォルト: `X-ApiToken`)。`parameter` と `header` の少なくとも一方が必要です。 |
| `userModel` | ユーザーモデル (デフォルト: `User`)。 |
| `fields` | `token` はトークンを保存するカラムです (デフォルト: `token`)。`password` カラムはユーザーのデータから取り除かれます。 |
| `scope` | ユーザーを検索するときの追加の条件。 |
| `recursive` | find の `recursive` オプション (デフォルト: `0`)。 |
| `contain` | ユーザーと一緒に取得するアソシエーション先のモデル。 |
| `continue` | `true` の場合、`AuthComponent` は認証されていないリクエストを通常どおり処理します。つまり、ログインアクションにリダイレクトするか、Ajax と JSON のリクエストには 403 レスポンスを返します。`false` (デフォルト) の場合は `unauthorized` の例外がスローされます。`AuthComponent` は最後の認証オブジェクトにしか問い合わせないので、これは `Authenticate.Token` が最後の認証オブジェクトの場合にだけ適用されます。 |
| `unauthorized` | スローする例外クラス、または `HttpException` の HTTP ステータスコード (デフォルト: `BadRequestException`)。 |

<a id="combining-authenticate-objects"></a>

## 認証オブジェクトの組み合わせ

認証オブジェクトは順番に試されます。次の設定では、まず Cookie を確認し、次にフォームのデータを
確認します。

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

<a id="origin"></a>

## 由来

このパッケージは
[FriendsOfCake/Authenticate](https://github.com/FriendsOfCake/Authenticate)
(Packagist では `friendsofcake/authenticate`、アップストリームの最終バージョンは 1.0.2)
のメンテナンスされたフォークで、元は M. Ypes (Ceeram) によって作られました。
[pieceofcake2](https://github.com/pieceofcake2) が、モダンな PHP 上の CakePHP 2 向けにメンテナンスしています。
オリジナルからの変更点は
[フォーク以降のコミット履歴](https://github.com/pieceofcake2/authenticate/compare/4d91c2b...1.x)
にあります。
