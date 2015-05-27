<?php namespace Prograhammer\Inputter;

use Illuminate\Support\ServiceProvider;

class InputterServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		// $this->package('prograhammer/inputter');
	}		

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Create a new instance each time (instead of sharing -- commented out below)
		$this->app['inputter'] = new Inputter;
		
		// Share the instance
		//$this->app['easyinput'] = $this->app->share(function($app)
        //{
        //    return new EasyInput;
        //});
		
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('inputter');
	}

}