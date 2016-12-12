# Shopify Provider for OAuth 2.0 Client

[![Latest Stable Version](https://poser.pugx.org/multidimensional/oauth2-shopify/v/stable)](https://packagist.org/packages/multidimensional/oauth2-shopify)
[![License](https://poser.pugx.org/multidimensional/oauth2-shopify/license)](https://packagist.org/packages/multidimensional/oauth2-shopify)
[![Build Status](https://travis-ci.org/multidimensional/oauth2-shopify.svg?branch=master)](https://travis-ci.org/multidimensional/oauth2-shopify)
[![Total Downloads](https://poser.pugx.org/multidimensional/oauth2-shopify/downloads)](https://packagist.org/packages/multidimensional/oauth2-shopify)

This package provides Shopify's OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require multidimensional/oauth2-shopify
```

## Usage

Usage is the same as The League's OAuth client, using `\Multidimensional\OAuth2\Client\Provider\Shopify` as the provider.

### Authorization Code Flow

```php
$provider = new Multidimensional\OAuth2\Client\Provider\Shopify([
    'clientId'          => '{shopify-app-id}',
    'clientSecret'      => '{shopify-app-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
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

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/multidimensional/oauth2-shopify/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Nile Suan](https://github.com/nilesuan)
- [All Contributors](https://github.com/multidimensional/oauth2-shopify/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/multidimensional/oauth2-shopify/blob/master/LICENSE) for more information.
