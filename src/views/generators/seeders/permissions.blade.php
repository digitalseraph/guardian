<?php echo '<?php' ?>

use Illuminate\Database\Seeder;
use {{ $roleModelName }};
use App\Permission;

class GuardianPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // User Roles to add permissions to
        $noPerms            = Role::where('slug', '=', 'system.permissions.none')->first();
        $allPerms           = Role::where('slug', '=', 'system.permissions.all')->first();
        $adminSystem        = Role::where('slug', '=', 'admin.system')->first();
        $manager            = Role::where('slug', '=', 'manager')->first();
        $managerFinance     = Role::where('slug', '=', 'manager.finance')->first();
        $managerCorporate   = Role::where('slug', '=', 'manager.corporate')->first();
        $managerEntity      = Role::where('slug', '=', 'manager.entity')->first();
        $associate          = Role::where('slug', '=', 'associate')->first();
        $associateCoach     = Role::where('slug', '=', 'associate.coach')->first();
        $customer           = Role::where('slug', '=', 'customer')->first();
        $customerPauser     = Role::where('slug', '=', 'customer.pauser')->first();
        $customerFollower   = Role::where('slug', '=', 'customer.follower')->first();
        $customerProspect   = Role::where('slug', '=', 'customer.prospect')->first();

        /**
         * User Management Permissions
         */
        $userC = Permission::create(['name' => 'Create Users', 'slug' => 'user.create', 'description' => 'create users']);
        $userD = Permission::create(['name' => 'Delete Users', 'slug' => 'user.delete', 'description' => 'delete users']);
        $userU = Permission::create(['name' => 'Update Users', 'slug' => 'user.update', 'description' => 'update users']);
        $userV = Permission::create(['name' => 'View Users', 'slug' => 'user.view', 'description' => 'view users']);

        // Assign User Permissions to Roles
        $noPerms->attachPermissions([]);
        $allPerms->attachPermissions([$userC, $userD, $userU, $userV]);
        $adminSystem->attachPermissions([$userC, $userD, $userU, $userV]);
        $manager->attachPermissions([$userC, $userD, $userU, $userV]);
        $managerFinance->attachPermissions([$userV]);
        $managerCorporate->attachPermissions([$userV]);
        $managerEntity->attachPermissions([]);
        $associate->attachPermissions([$userU, $userV]);
        $associateCoach->attachPermissions([]);
        $customer->attachPermissions([]);
        $customerPauser->attachPermissions([]);
        $customerFollower->attachPermissions([]);
        $customerProspect->attachPermissions([]);
    }
}
