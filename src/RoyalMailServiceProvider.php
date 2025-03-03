<?php

declare(strict_types=1);

namespace ParcelTrap\RoyalMail;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use ParcelTrap\Contracts\Factory;
use ParcelTrap\ParcelTrap;

class RoyalMailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var ParcelTrap $factory */
        $factory = $this->app->make(Factory::class);

        $factory->extend(RoyalMail::IDENTIFIER, function () {
            /** @var Repository $config */
            $config = $this->app->make(Repository::class);

            return new RoyalMail(
                // @phpstan-ignore-next-line
                clientId: (string) $config->get('parceltrap.drivers.royal_mail.client_id'),
                // @phpstan-ignore-next-line
                clientSecret: (string) $config->get('parceltrap.drivers.royal_mail.client_secret'),
                acceptTerms: true,
            );
        });
    }
}
