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

class MigrationCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'ds:guardian:make:migration 
                            {migration=all : Which migrations to generate, i.e. "roles", or "permissions"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates migration(s) following the DigitalSeraph Guardian specifications.';

    /**
     * The types of migrations that are available for generation
     *
     * @var array
     */
    protected $availableMigrations = ['admin_users', 'roles', 'permissions'];

    /**
     * A place to store all the migration information
     *
     * @var array
     */
    protected $migrationsArray = [];

     /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        /**
         * Use the guardian config file for migration settings
         */
        foreach ($this->availableMigrations as $migration) {
            $this->migrationsArray[] = $this->{$migration . 'Migration'} = $migration;
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
        $migration = $this->argument('migration');
        // namespace the views for blade templating
        $this->laravel->view->addNamespace('guardian', __DIR__.'/../../views');

        // Build an array of the requested migrations
        if (in_array($migration, $this->availableMigrations)) {
            $migrationsArray[$migration] = $this->{$migration . 'Migration'};
        } elseif ($migration == 'all') {
            foreach ($this->availableMigrations as $migration) {
                $migrationsArray[$migration] = $this->{$migration . 'Migration'};
            }
        } else {
            $this->error("Invalid migration name: ${migration}");
            return false;
        }

        // Print out a list of the migrations
        $this->info("\nThe following migration(s) will be generated:");

        foreach ($migrationsArray as $data) {
            $this->comment("  - ${data}");
        }

        // Get verification from the user
        if ($this->confirm("Proceed with the migration(s) creation?", "Yes")) {
            // Loop through the array and create the migrations
            foreach ($migrationsArray as $migration) {
                $this->info("Creating ${migration} migrations...");

                if ($this->createMigration($migration)) {
                    $this->info("${migration} migration was successfully created!");
                } else {
                    $this->error("Couldn't create the ${migration} migration.\n " .
                        "Check the write permissions within the database/seeds directory.");
                }
            }

            $this->line('');
        }
    }

    /**
     * Create a migration
     *
     * @param string $migration
     *
     * @return bool
     */
    protected function createMigration($migration)
    {
        $date = date('Y_m_d_');
        $order = array_search($migration, $this->migrationsArray);
        $migrationFile = database_path("/migrations/" . $date . $order . "00000_guardian_create_${migration}_tables.php");

        $data = [
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

        $output = $this->laravel->view->make("guardian::generators.migrations.${migration}")->with($data)->render();

        if (!file_exists($migrationFile) && $fs = fopen($migrationFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }
}
