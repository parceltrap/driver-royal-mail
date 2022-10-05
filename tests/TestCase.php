<?php

declare(strict_types=1);

namespace ParcelTrap\RoyalMail\Tests;

use ParcelTrap\ParcelTrapServiceProvider;
use ParcelTrap\RoyalMail\RoyalMailServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ParcelTrapServiceProvider::class, RoyalMailServiceProvider::class];
    }
}
