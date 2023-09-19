<?php

declare(strict_types=1);

namespace Upmind\ProvisionProviders\AutoLogin;

use Upmind\ProvisionBase\Laravel\ProvisionServiceProvider;
use Upmind\ProvisionProviders\AutoLogin\Providers\Example\Provider as ExampleProvider;
use Upmind\ProvisionProviders\AutoLogin\Providers\Generic\Provider as GenericProvider;
use Upmind\ProvisionProviders\AutoLogin\Providers\SpamExperts\Provider as SpamExpertsProvider;

class LaravelServiceProvider extends ProvisionServiceProvider
{
    public function boot()
    {
        $this->bindCategory('auto-login', Category::class);

        // $this->bindProvider('auto-login', 'example', ExampleProvider::class);

        $this->bindProvider('auto-login', 'generic', GenericProvider::class);
        $this->bindProvider('auto-login', 'spam-experts', SpamExpertsProvider::class);
    }
}
