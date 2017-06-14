<?php echo '<?php' ?>

use Illuminate\Database\Seeder;

use {{ $roleModelName }};

class {{ $className }} extends Seeder
{
    /**
    {{ var_dump($allData) }}

    */

    /**
     * Run the database seeds.
     *
     * @return  void
     */
    public function run()
    {
        /*****************************
         * Customer Level Operations *
         *****************************/
        
        // Generic Customer Role that all other Customers Inherit from
        $customerRole = Role::create([
            'name' => 'Customer',
            'slug' => 'customer',
            'description' => 'Generic Customer Role that all other Customers Inherit from',
            'parent_id' => 0
        ]);

        // Customer - Pauser
        $customerPauser = Role::create([
            'name' => 'Pauser Customer',
            'slug' => 'customer.pauser',
            'description' => '',
            'parent_id' => $customer->id
        ]);

        // Customer - Follower
        $customerFollower = Role::create([
            'name' => 'Follower Customer',
            'slug' => 'customer.follower',
            'description' => '',
            'parent_id' => $customerPauser->id
        ]);

        // Customer - Prospect
        $customerProspect = Role::create([
            'name' => 'Prospect Customer',
            'slug' => 'customer.prospect',
            'description' => '',
            'parent_id' => $customer->id
        ]);

        /*************************************
         * System Admin Level Operations
         *************************************/

        // Admin Roles - System Admin
        $ = Role::create([
            'name' => 'System Administrator',
            'slug' => 'system-admin',
            'description' => 'System Administrators have access to the entire system.',
            'parent_id' => 1
        ]);


        /********************************
         * Entity Level Operations
         ********************************/










        /**
         * Managers
         */

        // Manager
        $manager = Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => '',
            'parent_id' => 0
        ]);

        // Manager - Finance
        $managerFinance = Role::create([
            'name' => 'Finance Manager',
            'slug' => 'manager.finance',
            'description' => '',
            'parent_id' => $manager->id
        ]);

        // Manager - Corporate
        $managerCorporate = Role::create([
            'name' => 'Corporate Manager',
            'slug' => 'manager.corporate',
            'description' => '',
            'parent_id' => $manager->id
        ]);

        // Manager - Entity
        $managerEntity = Role::create([
            'name' => 'Entity Manager',
            'slug' => 'manager.entity',
            'description' => '',
            'parent_id' => $manager->id
        ]);

        /**************************************************
         *
         *              Front-End Users
         *
         *************************************************/

        /**
         * Associates
         */

        // Associate
        $associate = Role::create([
            'name' => 'Associate',
            'slug' => 'associate',
            'description' => '',
            'parent_id' => 0
        ]);

        // Associate - Coach
        $associateCoach = Role::create([
            'name' => 'Coach Associate',
            'slug' => 'associate.coach',
            'description' => '',
            'parent_id' => $associate->id
        ]);

        /**
         * Customers
         */

        // Customer - Customer
        $customer = Role::create([
            'name' => 'Customer',
            'slug' => 'customer',
            'description' => '',
            'parent_id' => 0
        ]);

        // Customer - Pauser
        $customerPauser = Role::create([
            'name' => 'Pauser Customer',
            'slug' => 'customer.pauser',
            'description' => '',
            'parent_id' => $customer->id
        ]);

        // Customer - Follower
        $customerFollower = Role::create([
            'name' => 'Follower Customer',
            'slug' => 'customer.follower',
            'description' => '',
            'parent_id' => $customerPauser->id
        ]);

        // Customer - Prospect
        $customerProspect = Role::create([
            'name' => 'Prospect Customer',
            'slug' => 'customer.prospect',
            'description' => '',
            'parent_id' => $customer->id
        ]);
    }
}
