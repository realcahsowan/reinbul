<?php

namespace RealCahsowan\Reinbul\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReinbulRemoveCommand extends Command
{
    protected $signature = "remove:reinbul {name} {--prefix=}";
    protected $description = "Remove REINBUL resource files.";

    public function handle()
    {
        $name = $this->argument("name");
        $prefix = $this->option('prefix');
        $prefixRoute = !is_null($prefix) ? Str::slug($prefix) . '.' : '';
        $prefixNamespace = !is_null($prefix) ? Str::studly($prefix) : '';

        $routeText =
        "Route::resource('" .
        $prefixRoute .
        Str::plural(Str::slug($name)) .
        "', Controllers\\" .
        $prefixNamespace . "\\" .
        Str::plural(Str::studly($name)) .
            "Controller::class);";

        $message =
            "Resources file for " .
            $name .
            " model removed sucessfuly, please continue with following steps:\n" .
            " - run 'php artisan migrate:rollback'\n" .
            " - remove '" . $routeText . "' from your route file";

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
            $this->getControllerPath($name, $prefixNamespace)
        );

        // Remove pages view file
        foreach ($this->getPagesPath($name, $prefixNamespace) as $type => $path) {
            File::delete($path);
        }

        $pagesDir = resource_path("js/Pages/") . Str::plural(Str::studly($name));
        File::deleteDirectory($pagesDir);

        // Remove factory file
        File::delete(
            $this->getFactoryPath($name),
        );

        // Remove remaining dirs
        if (! empty($prefixNamespace)) {
            $prefixControllerDir = File::dirname($this->getControllerPath($name, $prefixNamespace));
            File::deleteDirectory($prefixControllerDir);

            $pagesDir = resource_path("js/Pages/") . 
                        $prefixNamespace .
                        Str::plural(Str::studly($name));
            
            File::deleteDirectory($pagesDir);
            File::deleteDirectory(resource_path("js/Pages/") . $prefixNamespace);
        }

        $this->info($message);
    }

    public function getModelPath($name)
    {
        return app_path("Models/" . Str::studly($name) . ".php");
    }

    public function getFactoryPath($name)
    {
        return base_path("database/factories/" . Str::studly($name) . "Factory.php");
    }

    public function getMigrationPath($name)
    {
        $migrationFile = collect(File::allFiles(base_path('database/migrations')))
            ->filter(function ($item) use ($name) {
                return Str::contains($item->getFilename(), Str::plural(Str::slug($name, '_')));
            })->first();

        return $migrationFile;
    }

    public function getControllerPath($name, $prefixNamespace)
    {
        $path = "Http/Controllers/" .
                Str::plural(Str::studly($name)) .
                "Controller.php";

        if (! empty($prefixNamespace)) {
            $path = "Http/Controllers/" . 
                    $prefixNamespace . '/' .
                    Str::plural(Str::studly($name)) .
                    "Controller.php";
        }

        return app_path($path);
    }

    public function getPagesPath($name, $prefixNamespace)
    {
        return [
            "pages_index" => resource_path(
                "js/Pages/" . (! empty($prefixNamespace) ? $prefixNamespace . '/' : '') . Str::plural(Str::studly($name)) . "/Index.js"
            ),
            "pages_create" => resource_path(
                "js/Pages/" . (! empty($prefixNamespace) ? $prefixNamespace . '/' : '') . Str::plural(Str::studly($name)) . "/Create.js"
            ),
            "pages_edit" => resource_path(
                "js/Pages/" . (! empty($prefixNamespace) ? $prefixNamespace . '/' : '') . Str::plural(Str::studly($name)) . "/Edit.js"
            ),
            "pages_form" => resource_path(
                "js/Pages/" . (! empty($prefixNamespace) ? $prefixNamespace . '/' : '') . Str::plural(Str::studly($name)) . "/Form.js"
            ),
        ];
    }
}
