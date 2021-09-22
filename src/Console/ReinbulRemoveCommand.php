<?php

namespace RealCahsowan\Reinbul\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReinbulRemoveCommand extends Command
{
    protected $signature = "remove:reinbul {name}";
    protected $description = "Remove REINBUL resource files.";

    public function handle()
    {
        $name = $this->argument("name");

        $routeText =
        "Route::resource('" .
        Str::plural(Str::lower($name)) .
        "', Controllers\\" .
        Str::plural(Str::title($name)) .
            "Controller::class);";

        $message =
            "Resources file for " .
            $name .
            " model removed sucessfuly, please continue with following steps:\n" .
            " - run 'php artisan migrate:rollback'\n" .
            " - remove '" . $routeText. "' from your route file"; 

        // Remove model file
        File::delete(
            $this->getModelPath($name)
        );

        // Remove migration file
        if (File::exists($this->getMigrationPath($name))) {
            File::delete($this->getMigrationPath($name));
        }

        // Remove controller file
        File::delete(
            $this->getControllerPath($name)
        );

        // Remove pages view file
        foreach ($this->getPagesPath($name) as $type => $path) {
            File::delete($path);
        }

        $pagesDir = resource_path("js/Pages/") . Str::plural(Str::title($name));
        File::deleteDirectory($pagesDir);

        // Remove factory file
        File::delete(
            $this->getFactoryPath($name),
        );

        $this->info($message);
    }

    public function getModelPath($name)
    {
        return app_path("Models/" . Str::title($name) . ".php");
    }

    public function getFactoryPath($name)
    {
        return base_path("database/factories/" . Str::title($name) . "Factory.php");
    }

    public function getMigrationPath($name)
    {
        $migrationFile = collect(File::allFiles(base_path('database/migrations')))
            ->filter(function ($item) use ($name) {
                return Str::contains($item->getFilename(), Str::lower(Str::plural($name)));
            })->first();

        return $migrationFile;
    }

    public function getControllerPath($name)
    {
        return app_path(
            "Http/Controllers/" .
            Str::plural(Str::title($name)) .
            "Controller.php"
        );
    }

    public function getPagesPath($name)
    {
        return [
            "pages_index" => resource_path(
                "js/Pages/" . Str::plural(Str::title($name)) . "/Index.js"
            ),
            "pages_create" => resource_path(
                "js/Pages/" . Str::plural(Str::title($name)) . "/Create.js"
            ),
            "pages_edit" => resource_path(
                "js/Pages/" . Str::plural(Str::title($name)) . "/Edit.js"
            ),
            "pages_form" => resource_path(
                "js/Pages/" . Str::plural(Str::title($name)) . "/Form.js"
            ),
        ];
    }
}
