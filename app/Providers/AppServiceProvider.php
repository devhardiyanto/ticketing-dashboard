<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		$repositories = [
			\App\Repositories\Contracts\EventRepositoryInterface::class => \App\Repositories\Eloquent\EventRepository::class,
			\App\Repositories\Contracts\OrganizationRepositoryInterface::class => \App\Repositories\Eloquent\OrganizationRepository::class,
			\App\Repositories\Contracts\TicketTypeRepositoryInterface::class => \App\Repositories\Eloquent\TicketTypeRepository::class,
			\App\Repositories\Contracts\BannerRepositoryInterface::class => \App\Repositories\Eloquent\BannerRepository::class,
			\App\Repositories\Contracts\PlatformFeeConfigRepositoryInterface::class => \App\Repositories\Eloquent\PlatformFeeConfigRepository::class,
			\App\Repositories\Contracts\OrderRepositoryInterface::class => \App\Repositories\Eloquent\OrderRepository::class,
			\App\Repositories\Contracts\UserRepositoryInterface::class => \App\Repositories\Eloquent\UserRepository::class,
			\App\Repositories\Contracts\AnalyticsRepositoryInterface::class => \App\Repositories\Eloquent\AnalyticsRepository::class,
		];

		foreach ($repositories as $interface => $implementation) {
			$this->app->bind($interface, $implementation);
		}
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		//
	}
}
