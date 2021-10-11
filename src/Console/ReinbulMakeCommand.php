<?php

namespace RealCahsowan\Reinbul\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReinbulMakeCommand extends GeneratorCommand
{
    protected $signature = "make:reinbul {name} {--factory} {--prefix=}";
    protected $description = "Generate REINBUL resource files.";

    public function handle()
    {
        $name = Str::slug($this->argument("name"));
        $prefix = $this->option('prefix');
        $prefixRoute = !is_null($prefix) ? Str::slug($prefix) . '.' : '';
        $prefixNamespace = !is_null($prefix) ? Str::studly($prefix) : '';
        $message =
            "Resources file for " .
            $name .
            " model generated, run artisan migrate, then please update your route:";

        $routeText =
        "Route::resource('" .
        $prefixRoute .
        Str::plural(Str::slug($name)) .
        "', Controllers\\" .
        $prefixNamespace . "\\" .
        Str::plural(Str::studly($name)) .
            "Controller::class);";

        // START
        File::put(
            $this->getModelPath($name),
            $this->getResourceContent($name, "model")
        );

        // Generate migration file
        File::put(
            $this->getMigrationPath($name),
            $this->getResourceContent($name, "migration")
        );

        // Generate controller file
        File::put(
            $this->getControllerPath($name, $prefixNamespace),
            $this->getResourceContent($name, "controller", $prefixNamespace)
        );

        // Generate pages view file
        foreach ($this->getPagesPath($name, $prefixNamespace) as $type => $path) {
            if (!File::exists(File::dirname($path))) {
                File::makeDirectory(File::dirname($path));
            }

            File::put($path, $this->getResourceContent($name, $type, $prefixNamespace));
        }

        // Generate factory file
        $factory = $this->option('factory');

        if ($factory) {
            File::put(
                $this->getFactoryPath($name),
                $this->getResourceContent($name, "factory")
            );
        }
        // END

        $this->info($message);
        $this->warn($routeText);
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
        return base_path(
            "database/migrations/" .
            date("Y_m_d_His") .
            "_create_" .
            Str::plural(Str::slug($name, '_')) .
            "_table.php"
        );
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

            if (!File::exists(File::dirname(app_path($path)))) {
                File::makeDirectory(File::dirname(app_path($path)));
            }
        }

        return app_path($path);
    }

    public function getPagesPath($name, $prefixNamespace)
    {
        if (! empty($prefixNamespace)) {
            $prefix_path = resource_path("js/Pages/" . $prefixNamespace);
            if (!File::exists($prefix_path)) {
                File::makeDirectory($prefix_path);
            }
        }

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

    public function getResourceContent($name, $type, $prefixNamespace = null)
    {
        $content = $this->getStubContent($type);
        $model = Str::studly($name);
        $data = Str::slug($name, '_');
        $resource = Str::plural(Str::studly($name));
        $resource_label = Str::title(str_replace('_', ' ', $data));
        $resource_data = Str::plural(Str::slug($name, '_'));
        $route_name = Str::plural(Str::slug($name, '-'));
        $controller = Str::plural(Str::studly($name)) . "Controller";
        $prefix = '';

        if (! empty($prefixNamespace)) {
            $prefix = '\\' . $prefixNamespace;
            $resource = $prefixNamespace . '/' . $resource;
            $route_name = Str::slug($prefixNamespace) . '.' . $route_name;
        }

        return preg_replace(
            [
                "/\[class\]/",
                "/\[resource\]/",
                "/\[resource_label\]/",
                "/\[resource_data\]/",
                "/\[route_name\]/",
                "/\[model\]/",
                "/\[data\]/",
                "/\[prefix\]/",
            ],
            [$controller, $resource, $resource_label, $resource_data, $route_name, $model, $data, $prefix],
            $content
        );
    }

    public function getStubContent($type)
    {
        $stubsDirectory = "/vendor/realcahsowan/reinbul/stubs/";

        $paths = [
            "model" => base_path($stubsDirectory . "Model.stub"),
            "factory" => base_path($stubsDirectory . "Factory.stub"),
            "migration" => base_path($stubsDirectory . "Migration.stub"),
            "controller" => base_path($stubsDirectory . "Controller.stub"),
            "pages_index" => base_path($stubsDirectory . "Pages/Index.stub"),
            "pages_create" => base_path($stubsDirectory . "Pages/Create.stub"),
            "pages_edit" => base_path($stubsDirectory . "Pages/Edit.stub"),
            "pages_form" => base_path($stubsDirectory . "Pages/Form.stub"),
        ];

        return file_get_contents($paths[$type]);
    }

    protected function getStub()
    {
        //
    }
}
