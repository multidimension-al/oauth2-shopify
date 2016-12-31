<?php

namespace Multidimensional\OAuth2\Client\Test\Provider;

use Multidimensional\OAuth2\Client\Provider\Shopify as ShopifyProvider;

use Mockery as m;

class ShopifyTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new ShopifyProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'shop' => 'mock_domain',
            'accessType' => 'online'
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertEquals('/admin/oauth/authorize', $uri['path']);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertArrayHasKey('option', $query);

        $this->assertEquals('per-user', $query['option']);

        $this->assertContains('read_content', $query['scope']);
        $this->assertContains('read_products', $query['scope']);

        $this->assertAttributeNotEmpty('state', $this->provider);
    }

    public function testBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);

        $this->assertEquals('/admin/oauth/access_token', $uri['path']);
    }

    public function testResourceOwnerDetailsUrl()
    {
        $parameters = $this->provider->getAuthorizationParameters([]);

        $this->assertEquals('per-user', $parameters['option']);

    }

    public function testAuthorizationParameters()
    {
        $token = m::mock('League\OAuth2\Client\Token\AccessToken', [['access_token' => 'mock_access_token']]);

        $url = $this->provider->getResourceOwnerDetailsUrl($token);
        $uri = parse_url($url);

        $this->assertEquals('/admin/shop.json', $uri['path']);
        $this->assertNotContains('mock_access_token', $url);

    }

    public function testDefaultScopes()
    {
        $scopes = $this->provider->getDefaultScopes();

        $this->assertContains('read_content', $scopes);
        $this->assertContains('read_products', $scopes);

    }

    public function testScopeSeparator()
    {
        $separator = $this->provider->getScopeSeparator();

        $this->assertEquals(',', $separator);

    }

    public function testcheckResponse() {
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"shop": { "id": 12345, "name": "mock_name", "email": "mock_email", "domain": "mock_store.myshopify.com"}}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);
        
        $checkedresponse = $this->provider->checkResponse($postResponse, '{"shop": { "id": 12345, "name": "mock_name", "email": "mock_email", "domain": "mock_store.myshopify.com"}}');

    }

    /**
     * @expectedException League\OAuth2\Client\Provider\Exception\IdentityProviderException
     **/
    public function testcheckResponseException() {
        $status = rand(400,500);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"errors":"[API] Invalid API key or access token (unrecognized login or wrong password)"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);

        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

    }

    public function testcreateResourceOwner()
    {
        $response = json_decode('{
          "shop": {
            "id": 12345,
            "name": "mock_name",
            "email": "mock_email",
            "domain": "mock_store.myshopify.com",
            "created_at": "",
            "province": "",
            "country": "",
            "address1": "",
            "zip": "",
            "city": "",
            "source": "",
            "phone": "",
            "updated_at": "",
            "customer_email": null,
            "latitude": 0,
            "longitude": 0,
            "primary_location_id": 0,
            "primary_locale": "en",
            "address2": null,
            "country_code": "US",
            "country_name": "mock_country_name",
            "currency": "USD",
            "timezone": "(GMT-05:00) Eastern Time (US & Canada)",
            "iana_timezone": "America/New_York",
            "shop_owner": "mock_shop_owner",
            "money_format": "${{amount}}",
            "money_with_currency_format": "${{amount}} USD",
            "province_code": null,
            "taxes_included": false,
            "tax_shipping": null,
            "county_taxes": true,
            "plan_display_name": "affiliate",
            "plan_name": "affiliate",
            "has_discounts": false,
            "has_gift_cards": false,
            "myshopify_domain": "example.myshopify.com",
            "google_apps_domain": null,
            "google_apps_login_enabled": null,
            "money_in_emails_format": "${{amount}}",
            "money_with_currency_in_emails_format": "${{amount}} USD",
            "eligible_for_payments": false,
            "requires_extra_payments_agreement": false,
            "password_enabled": true,
            "has_storefront": true,
            "eligible_for_card_reader_giveaway": false,
            "finances": true,
            "setup_required": false,
            "force_ssl": true
          }
        }', true);

        $provider = m::mock('Multidimensional\OAuth2\Client\Provider\Shopify[fetchResourceOwnerDetails]')
            ->shouldAllowMockingProtectedMethods();

        $provider->shouldReceive('fetchResourceOwnerDetails')
            ->times(1)
            ->andReturn($response);

        $token = m::mock('League\OAuth2\Client\Token\AccessToken');
        $shop = $provider->getResourceOwner($token);

        $this->assertInstanceOf('League\OAuth2\Client\Provider\ResourceOwnerInterface', $shop);

        $this->assertEquals(12345, $shop->getId());
        $this->assertEquals('mock_name', $shop->getName());
        $this->assertEquals('mock_email', $shop->getEmail());
        $this->assertEquals('mock_store.myshopify.com', $shop->getDomain());
        $this->assertEquals('mock_country_name', $shop->getCountry());
        $this->assertEquals('mock_shop_owner', $shop->getShopOwner());

        $shop = $shop->toArray();

        $this->assertInternalType('array', $shop);
        $this->assertEquals(48, count($shop));
    }

    public function testAuthorizationHeaders()
    {

        $token = m::mock('League\OAuth2\Client\Token\AccessToken', [['access_token' => 'mock_access_token']]);

        $token->shouldReceive('getToken')
            ->times(1)
            ->andReturn('mock_token');

        $headers = $this->provider->getAuthorizationHeaders($token);

        $this->assertArrayHasKey('X-Shopify-Access-Token', $headers);
        $this->assertEquals('mock_token', $headers['X-Shopify-Access-Token']);

    }
}
