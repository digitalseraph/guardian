<?php

namespace WhiteSunrise\Guardian\Console\Commands;

/**
 * This file is part of WhiteSunrise Guardian,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package WhiteSunrise\Guardian
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SeederCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'ws:guardian:make:seeder
                            {seeder=all : Which seeder to generate, i.e. "roles", or "permissions"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates seeder(s) following the WhiteSunrise Guardian specifications.';

    /**
     * The types of seeders that are available for generation
     *
     * @var array
     */
    protected $availableSeeders = ['roles', 'permissions'];

    /**
     * A place to store all the seeder information
     *
     * @var array
     */
    protected $seedersArray = [];

     /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        /**
         * Get the user configuration information for data types
         *   format looks like: `$this->rolesSeeder = Config::get('guardian.roles_seeder');`
         */
        foreach ($this->availableSeeders as $seeder) {
            $this->seedersArray[] = $this->{$seeder . 'Seeder'} = Config::get("guardian.${seeder}_seeder");
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
        $seeder = $this->argument('seeder');
        // namespace the views for blade templating
        $this->laravel->view->addNamespace('guardian', __DIR__.'/../../views');

        // Build an array of the requested seeders
        if (in_array($seeder, $this->availableSeeders)) {
            $seedersArray[$seeder] = $this->{$seeder . 'Seeder'};
        } elseif ($seeder == 'all') {
            foreach ($this->availableSeeders as $seeder) {
                $seedersArray[$seeder] = $this->{$seeder . 'Seeder'};
            }
        } else {
            $this->error("Invalid seeder name: ${seeder}");
            return false;
        }

        // Print out a list of the seeders
        $this->info("\nThe following seeder(s) will be generated:");

        foreach ($seedersArray as $seeder => $filename) {
            $this->comment("  - ${filename}");
        }

        // Get verification from the user
        if ($this->confirm("Proceed with the seeder(s) creation?", "Yes")) {
            // Loop through the array and create the seeders
            foreach ($seedersArray as $seeder => $filename) {
                $this->info("Creating ${seeder} seeders...");

                if ($this->createSeeder($seeder, $filename)) {
                    $this->info("${seeder} seeder was successfully created!");
                } else {
                    $this->error("Couldn't create the ${filename}  seeder.\n " .
                        "Check the write permissions within the database/seeds directory.");
                }
            }

            $this->line('');
        }
    }

    /**
     * Create a seed file based on the seeder type and filename
     *
     * @param string $seeder    seeder type, i.e. roles, permissions
     * @param string $filename  seeder filename base
     *
     * @return bool
     */
    protected function createSeeder($seeder, $filename)
    {
        $seederFile = database_path("/seeds/${filename}.php");

        $data = [
            'className' => $filename,
            'filename' => $filename . ".php",
            'roleModelName' => Config::get('guardian.role'),
            'roleClassName' => array_last(explode('\\', Config::get('guardian.role'))),
            'rolesTable' => Config::get('guardian.roles_table'),
            'roleUserTable' => Config::get('guardian.role_user_table'),
            'usersTable' => Config::get('guardian.users_table'),
            'userKeyName' => Config::get('guardian.user_foreign_key'),
            'roleAdminUserTable' => Config::get('guardian.role_admin_user_table'),
            'adminUsersTable' => Config::get('guardian.admin_users_table'),
            'adminUserKeyName' => Config::get('guardian.admin_user_foreign_key'),
            'permissionsTable' => Config::get('guardian.permissions_table'),
            'permissionRoleTable' => Config::get('guardian.permission_role_table'),
        ];

        $data['allData'] = $data;

        $output = $this->laravel->view->make("guardian::generators.seeders.${seeder}")->with($data)->render();

        if (!file_exists($seederFile) && $fs = fopen($seederFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }
}
