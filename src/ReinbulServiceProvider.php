<?php

namespace RealCahsowan\Reinbul;

use Illuminate\Support\ServiceProvider;
use RealCahsowan\Reinbul\Console\ReinbulMakeCommand;

class ReinbulServiceProvider extends ServiceProvider 
{
	public function boot()
	{
		if ($this->app->runningInConsole()) {
	        $this->commands([
	            ReinbulMakeCommand::class,
	        ]);
	    }
	}

	public function register()
	{
		// code...
	}
}