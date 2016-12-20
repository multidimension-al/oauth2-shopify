<?php

namespace Multidimensional\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class ShopifyStore implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['shop']['id'];
    }

    /**
     * Get shop name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->response['shop']['name'];
    }

    /**
     * Get shop email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->response['shop']['email'];
    }

    /**
     * Get shop domain name.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->response['shop']['domain'];
    }

    /**
     * Get shop country.
     *
     * @return string|null
     */
    public function getCountry()
    {
        return $this->response['shop']['country_name'];
    }

    /**
     * Get shop owner name.
     *
     * @return string|null
     */
    public function getShopOwner()
    {
        return $this->response['shop']['shop_owner'];
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
