# Shopify Provider for OAuth 2.0 Client

[![Latest Stable Version](https://poser.pugx.org/multidimensional/oauth2-shopify/v/stable)](https://packagist.org/packages/multidimensional/oauth2-shopify)
[![Total Downloads](https://poser.pugx.org/multidimensional/oauth2-shopify/downloads)](https://packagist.org/packages/multidimensional/oauth2-shopify)
[![License](https://poser.pugx.org/multidimensional/oauth2-shopify/license)](https://packagist.org/packages/multidimensional/oauth2-shopify)
[![CI](https://github.com/multidimension-al/oauth2-shopify/actions/workflows/ci.yml/badge.svg)](https://github.com/multidimension-al/oauth2-shopify/actions/workflows/ci.yml)
[![codecov](https://codecov.io/github/multidimension-al/oauth2-shopify/graph/badge.svg?token=AMLNRIPKAJ)](https://codecov.io/github/multidimension-al/oauth2-shopify)


This package provides Shopify's OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require multidimensional/oauth2-shopify
```

### Usage

Usage is the same as The League's OAuth client, using `\Multidimensional\OAuth2\Client\Provider\Shopify` as the provider.

### Authorization Code Flow

```php
$provider = new Multidimensional\OAuth2\Client\Provider\Shopify([
    'clientId'          => '{shopify-app-id}',
    'clientSecret'      => '{shopify-app-secret}',
    'shop'              => 'example.myshopify.com',
    'redirectUri'       => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {
    $options = [
        'scope' => ['read_orders','write_orders'] // array or string
    ];

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getName());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your Shopify authorization URL, you can specify the state and scopes your application may authorize.

```php
$options = [
    'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
    'scope' => ['read_orders','write_orders'] // array or string
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```
If neither are defined, the provider will utilize internal defaults.

At the time of authoring this documentation, the [following scopes are available](https://help.shopify.com/api/guides/authentication/oauth#scopes).

- read_content, write_content- Access to Article, Blog, Comment, Page, and Redirect.
- read_themes, write_themes - Access to Asset and Theme.
- read_products, write_products - Access to Product, product variant, Product Image, Collect, Custom Collection, and Smart Collection.
- read_customers, write_customers - Access to Customer and Saved Search.
- read_orders, write_orders - Access to Order, Transaction and Fulfillment.
- read_script_tags, write_script_tags - Access to Script Tag.
- read_fulfillments, write_fulfillments - Access to Fulfillment Service.
- read_shipping, write_shipping - Access to Carrier Service.
- read_analytics - Access to Analytics API.
- read_users, write_users - Access to User SHOPIFY PLUS.

## Testing

``` bash
make composer-install
make phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/multidimension-al/oauth2-shopify/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Nile Suan](https://github.com/nilesuan)
- [arnaudbagnis](https://github.com/arnaudbagnis)
- [All Contributors](https://github.com/multidimension-al/oauth2-shopify/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/multidimension-al/oauth2-shopify/blob/master/LICENSE) for more information.
