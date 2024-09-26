<?php

declare(strict_types=1);

namespace Upmind\ProvisionProviders\AutoLogin\Providers\SmarterMail;

use GuzzleHttp\Client;
use Upmind\ProvisionProviders\AutoLogin\Category;
use Upmind\ProvisionBase\Provider\DataSet\AboutData;
use Upmind\ProvisionProviders\AutoLogin\Data\EmptyResult;
use Upmind\ProvisionProviders\AutoLogin\Data\LoginResult;
use Upmind\ProvisionProviders\AutoLogin\Data\CreateParams;
use Upmind\ProvisionProviders\AutoLogin\Data\CreateResult;
use Upmind\ProvisionBase\Provider\Contract\ProviderInterface;
use Upmind\ProvisionProviders\AutoLogin\Data\AccountIdentifierParams;
use Upmind\ProvisionProviders\AutoLogin\Providers\SmarterMail\Data\Configuration;

/**
 * Empty provider for demonstration purposes.
 */
class Provider extends Category implements ProviderInterface
{
    protected Configuration $configuration;
    protected Client|null $client = null;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public static function aboutProvider(): AboutData
    {
        return AboutData::create()
            ->setName('Smarter Mail')
            // ->setLogoUrl('https://example.com/logo.png')
            ->setDescription('Empty provider for demonstration purposes');
    }

    /**
     * @inheritDoc
     *
     * @throws \Upmind\ProvisionBase\Exception\ProvisionFunctionError
     */
    public function create(CreateParams $params): CreateResult
    {
        try{
            $response = $this->api()->createDomain($params);
        } catch (\Exception $e) {
            $this->errorResult($e->getMessage());
        }

        return CreateResult::create()
            ->setMessage('Domain created')
            ->setServiceIdentifier($response['domainData']['name'])
            ->setPackageIdentifier($params->package_identifier);
    }

    /**
     * @inheritDoc
     */
    public function login(AccountIdentifierParams $params): LoginResult
    {
        try{
            $url = $this->api()->getAutoLoginUrl($params);
        } catch (\Exception $e) {
            $this->errorResult($e->getMessage());
        }

        return LoginResult::create()
            ->setMessage('Login URL generated')
            ->setUrl($url);
    }

    /**
     * @inheritDoc
     */
    public function suspend(AccountIdentifierParams $params): EmptyResult
    {
        try{
            $this->api()->toggleDomainStatus($params, false);
        } catch (\Exception $e) {
            $this->errorResult($e->getMessage());
        }

        return EmptyResult::create()
            ->setMessage('Account suspended');
    }

    /**
     * @inheritDoc
     */
    public function unsuspend(AccountIdentifierParams $params): EmptyResult
    {
        try{
            $this->api()->toggleDomainStatus($params, true);
        } catch (\Exception $e) {
            $this->errorResult($e->getMessage());
        }

        return EmptyResult::create()
            ->setMessage('Account unsuspended');
    }

    /**
     * @inheritDoc
     */
    public function terminate(AccountIdentifierParams $params): EmptyResult
    {
        try{
            $response = $this->api()->terminateDomain($params);
        } catch (\Exception $e) {
            $this->errorResult($e->getMessage());
        }

        if ($response['success'] !== true) {
            $this->errorResult('Failed to terminate domain');
        }

        return EmptyResult::create()
            ->setMessage('Domain terminated');
    }

    /**
     * Get a Guzzle HTTP client instance.
     */
    protected function api(): Api
    {
        return new Api($this->configuration);
    }
}
