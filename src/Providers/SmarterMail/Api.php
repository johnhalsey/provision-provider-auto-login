<?php

namespace Upmind\ProvisionProviders\AutoLogin\Providers\SmarterMail;

use GuzzleHttp\Client;
use Upmind\ProvisionProviders\AutoLogin\Data\CreateParams;
use Upmind\ProvisionProviders\AutoLogin\Data\AccountIdentifierParams;
use Upmind\ProvisionProviders\AutoLogin\Providers\SmarterMail\Data\Configuration;

class Api
{
    protected Client $client;

    public function __construct(protected Configuration $configuration)
    {
        $this->setClient();
    }

    protected function setClient()
    {
        $this->client = new Client([
            'base_uri' => $this->configuration->base_url . '/api/v1/',
        ]);
    }

    public function getAutoLoginUrl(AccountIdentifierParams $params): string
    {
        $response = $this->post('auth/retrieve-login-token', [
            'username'      => $params->username,
            'isSystemAdmin' => false
        ]);

        return $response['autoLoginUrl'];
    }

    public function createDomain(CreateParams $params): array
    {
        $domain = $params->service_identifier;

        return $this->post('settings/sysadmin/domain-put', [
            'domainData'                      => [
                'name'             => $domain,
                'path'             => '/var/lib/smartermail/Domains/' . $domain,
                'hostname'         => 'mail:' . $domain,
                'isEnabled'        => true,
                'userLimit'        => 10,
                'aliasLimit'       => 1000,
                'domainAliasCount' => 1000,
                'listLimit'        => 1000,
                'maxSize'          => 100000000000,
            ],
            'domainLocation'                  => 0,
            'deliverLocallyForExternalDomain' => true,
            'adminUsername'                   => '',
            'adminPassword'                   => '',
        ]);
    }

    public function terminateDomain(AccountIdentifierParams $params): array
    {
        return $this->post('settings/sysadmin/domain-delete/' . $params->service_identifier . '/true');
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function post($endpoint, $data = []): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->client->request(
            'POST',
            $endpoint,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json'    => $data,
            ]
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getAccessToken(): string
    {
        $response = $this->client->request(
            'POST',
            'auth/authenticate-user',
            [
                'json' => [
                    'username' => $this->configuration->username,
                    'password' => $this->configuration->password,
                ],
            ]
        );

        $json = json_decode($response->getBody(), true);

        return $json['accessToken'];
    }
}
