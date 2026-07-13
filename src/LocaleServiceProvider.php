<?php

namespace YasserElgammal\GreenLocale;

use InvalidArgumentException;
use YasserElgammal\Green\Config\Repository as ConfigRepository;
use YasserElgammal\Green\Support\ServiceProvider;
use YasserElgammal\GreenLocale\Contracts\LocaleResolver;
use YasserElgammal\GreenLocale\Drivers\ConfigLocaleResolver;
use YasserElgammal\GreenLocale\Drivers\QueryLocaleResolver;
use YasserElgammal\GreenLocale\Drivers\RequestLocaleResolver;
use YasserElgammal\GreenLocale\Drivers\SessionLocaleResolver;

class LocaleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $manager = $this->createManager();

        LocaleManager::setInstance($manager);

        $this->app->instance(LocaleManager::class, $manager);
        $this->app->instance(Contracts\LocaleRepository::class, $manager);
    }

    public function boot(): void
    {
    }

    public static function make(string $configPath = ''): LocaleManager
    {
        $config = [];
        $path = $configPath ?: (defined('BASE_PATH') ? BASE_PATH . '/config/locale.php' : '');

        if ($path !== '' && file_exists($path)) {
            $config = require $path;
        }

        $manager = new LocaleManager($config);
        LocaleManager::setInstance($manager);

        $resolver = $config['resolver'] ?? 'config';
        $resolvers = is_array($resolver) ? $resolver : [$resolver];

        foreach ($resolvers as $driverName) {
            $manager->addResolver(self::createResolver((string) $driverName, $config));
        }

        return $manager;
    }

    private function createManager(): LocaleManager
    {
        $config = [];

        if ($this->app->has('config')) {
            $repository = $this->app->make('config');

            if ($repository instanceof ConfigRepository) {
                $loadedConfig = $repository->get('locale', []);
                $config = is_array($loadedConfig) ? $loadedConfig : [];
            }
        }

        if ($config === [] && defined('BASE_PATH')) {
            $path = BASE_PATH . '/config/locale.php';

            if (file_exists($path)) {
                $config = require $path;
            }
        }

        $manager = new LocaleManager($config);

        $resolver = $config['resolver'] ?? 'config';
        $resolvers = is_array($resolver) ? $resolver : [$resolver];

        foreach ($resolvers as $driverName) {
            $manager->addResolver(self::createResolver((string) $driverName, $config));
        }

        return $manager;
    }

    private static function createResolver(string $name, array $config): LocaleResolver
    {
        return match ($name) {
            'request' => new RequestLocaleResolver(),
            'query' => new QueryLocaleResolver((string) ($config['query_parameter'] ?? 'locale')),
            'session' => new SessionLocaleResolver((string) ($config['session_key'] ?? 'locale')),
            'config' => new ConfigLocaleResolver((string) ($config['default_locale'] ?? 'en')),
            default => throw new InvalidArgumentException("Unknown locale resolver [$name]."),
        };
    }
}
