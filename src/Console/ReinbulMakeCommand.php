<?php

namespace RealCahsowan\Reinbul\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReinbulMakeCommand extends GeneratorCommand
{
    protected $name = "make:reinbul";
    protected $description = "Generate REINBUL resource files.";

    public function handle()
    {
        $name = $this->argument("name");
        $message =
            "Resources file for " .
            $name .
            " model generated, run artisan migrate, then please update your route:";

        $routeText =
            "Route::resource('" .
            Str::plural(Str::lower($name)) .
            "', Controllers\\" .
            Str::plural(Str::title($name)) .
            "Controller::class);";

        // Generate model file
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
            $this->getControllerPath($name),
            $this->getResourceContent($name, "controller")
        );

        // Generate pages view file
        foreach ($this->getPagesPath($name) as $type => $path) {
            if (!File::exists(File::dirname($path))) {
                File::makeDirectory(File::dirname($path));
            }

            File::put($path, $this->getResourceContent($name, $type));
        }

        $this->info($message);
        $this->warn($routeText);
    }

    public function getModelPath($name)
    {
        return app_path("Models/" . Str::title($name) . ".php");
    }

    public function getMigrationPath($name)
    {
        return base_path(
            "database/migrations/" .
                date("Y_m_d_His") .
                "_create_" .
                Str::plural(Str::lower($name)) .
                "_table.php"
        );
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

    public function getResourceContent($name, $type)
    {
        $content = $this->getStubContent($type);
        $model = Str::title($name);
        $data = Str::lower($name);
        $resource = Str::plural(Str::title($name));
        $resource_data = Str::plural(Str::lower($name));
        $controller = Str::plural(Str::title($name)) . "Controller";

        return preg_replace(
            [
                "/\[class\]/",
                "/\[resource\]/",
                "/\[resource_data\]/",
                "/\[model\]/",
                "/\[data\]/",
            ],
            [$controller, $resource, $resource_data, $model, $data],
            $content
        );
    }

    public function getStubContent($type)
    {
        $stubsDirectory = "packages/realcahsowan/reinbul/stubs/";

        $paths = [
            "model" => base_path($stubsDirectory . "Model.stub"),
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
