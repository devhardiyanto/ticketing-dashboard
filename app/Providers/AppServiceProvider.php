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
