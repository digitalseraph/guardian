<?php

namespace DigitalSeraph\Guardian\Console\Commands;

/**
 * This file is part of DigitalSeraph Guardian,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package DigitalSeraph\Guardian
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ModelCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'ds:guardian:make:model
                            {model=all : Which model to generate, i.e. "role", or "permission"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates models following the DigitalSeraph Guardian specifications.';

    /**
     * The types of models that are available for generation
     *
     * @var array
     */
    protected $availableModels = ['admin_user', 'role', 'permission'];

    /**
     * A place to store all the model information
     *
     * @var array
     */
    protected $modelsArray = [];

     /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        /**
         * Use the guardian config file for model settings
         */
        foreach ($this->availableModels as $model) {
            $this->modelsArray[$model]['namespace'] = $this->{$model . 'ModelNamespace'}
                = Config::get("guardian.${model}");
            $this->modelsArray[$model]['namespaceArray'] = $this->{$model . 'ModelNamespaceArray'}
                = explode("\\", Config::get("guardian.${model}"));
            $this->modelsArray[$model]['model'] = $this->{$model . 'Model'} = Config::get("guardian.${model}");
            $this->modelsArray[$model]['table'] = $this->{$model . 'sTable'} = Config::get("guardian.${model}s_table");
            $this->modelsArray[$model]['key'] = $this->{$model . 'KeyName'} = Config::get("guardian.${model}_foreign_key");
        }
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // get the console argument if it exists
        $model = $this->argument('model');
        // namespace the views for blade templating
        $this->laravel->view->addNamespace('guardian', __DIR__.'/../../views');

        // Build an array of the requested models
        if (in_array($model, $this->availableModels)) {
            // $modelsArray[$model] = $this->{$model . 'Model'};
            $modelsArray[$model] = $this->modelsArray[$model];
        } elseif ($model == 'all') {
            foreach ($this->availableModels as $model) {
                $modelsArray[$model] = $this->modelsArray[$model];
            }
        } else {
            $this->error("Invalid model name: ${model}");
            return false;
        }

        // Print out a list of the models
        $this->info("\nThe following model(s) will be generated:");

        foreach ($modelsArray as $data) {
            $this->comment("  - ${data['namespace']}");
        }

        // Get verification from the user
        if ($this->confirm("Proceed with the model(s) creation?", "Yes")) {
            // Loop through the array and create the models
            foreach ($modelsArray as $data) {
                $this->info("Creating ${data['model']} models...");
                if ($this->createModel($model)) {
                    $this->info("${model} model was successfully created!");
                } else {
                    $this->error("Couldn't create the ${model}  model.\n " .
                        "Check the write permissions within the app/ directory.");
                }
            }

            $this->line('');
        }
    }

    /**
     * Create the model.
     *
     * @param string $model
     *
     * @return bool
     */
    protected function createModel($model)
    {
        $className = array_last($this->modelsArray[$model]['namespaceArray']);
        $modelFile = app_path("Models/${className}.php");

        $data = [
            'rolesTable' => Config::get('guardian.roles_table'),
            'roleUserTable' => Config::get('guardian.role_user_table'),
            'roleAdminUserTable' => Config::get('guardian.role_admin_user_table'),
            'permissionsTable' => Config::get('guardian.permissions_table'),
            'permissionRoleTable' => Config::get('guardian.permission_role_table'),
            'modelsArray' => $this->modelsArray
        ];

        $output = $this->laravel->view->make('guardian::generators.models.'.$model)->with($data)->render();

        if (!file_exists($modelFile) && $fs = fopen($modelFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }
}
