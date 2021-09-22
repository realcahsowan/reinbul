<?php

namespace RealCahsowan\Reinbul;

use Illuminate\Support\ServiceProvider;
use RealCahsowan\Reinbul\Console\ReinbulMakeCommand;
use RealCahsowan\Reinbul\Console\ReinbulRemoveCommand;

class ReinbulServiceProvider extends ServiceProvider 
{
	public function boot()
	{
		if ($this->app->runningInConsole()) {
	        $this->commands([
	            ReinbulMakeCommand::class,
	            ReinbulRemoveCommand::class,
	        ]);
	    }
	}

	public function register()
	{
		// code...
	}
}