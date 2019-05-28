# Guardian - Multi-Auth with roles and permissions built right in

This package allows a quick and tested way to setup a multi-auth user validation and authentication system, with custom middleware, guards, migrations, and seeders for testing. This package has only been tested in Laravel 5.3 and 5.4.

See below for installation instructions and other information.

## Installation

This package can be easily installed as a private composer package. See below for details and docs.

### Laravel 5.4

1. Create a folder named `packages` in your Laravel project directory, and clone the repository into that folder:

```sh
cd <project-root-directory>
mkdir packages && cd packages
git clone git@github.com:digitalseraph/guardian.git
```

2. Add the cloned repository to your project's composer.json file in the `autoload` section like so:

```json
"autoload": {
    "classmap": [
        "database"
    ],
    "psr-4": {
        "App\\": "app/",
        "DigitalSeraph\\Guardian\\": "packages/digitalseraph/guardian/src/"
    }
},
```

1. Clear composer and artisan autoload from your project root: 

```shell
composer dumpautoload && php artisan clear-compiled
```

4. Open your `config/app.php` and add the following to the `providers` array: 

```php
DigitalSeraph\Guardian\GuardianServiceProvider::class,
```

5. In the same `config/app.php` and add the following to the `aliases` array: 

```php
'Guardian' =>  DigitalSeraph\Guardian\Facades\GuardianFacade::class,
```

6. Publish the package configuration file to **config/guardian.php**:

```shell
php artisan vendor:publish --provider="DigitalSeraph\Guardian\GuardianServiceProvider" --tag=config
```

7. Open your `config/auth.php` and make the following 3 changes for the Admin User class:

    1. In the `providers` array:

        ```php
        'providers' => [
            'users' => [
                'driver' => 'eloquent',
                'model' => App\Models\User::class,
            ],
            'admins' => [
                'driver' => 'eloquent',
                'model' => App\Models\AdminUser::class,
            ],
        ],
        ```

    2. In the `guards` array:

        ```php
        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
            'web_admin' => [
                'driver' => 'session',
                'provider' => 'admins',
            ],

            'api' => [
                'driver' => 'token',
                'provider' => 'users',
            ],
        ],
        ```

    3. In the `passwords` array:

        ```php
        'passwords' => [
            'users' => [
                'provider' => 'users',
                'table' => 'password_resets',
                'expire' => 60,
            ],
            'admins' => [
                'provider' => 'admins',
                'table' => 'admin_password_resets',
                'expire' => 60,
            ],
        ],
        ```

8. If you want to use Middleware (requires Laravel 5.1 or later. you also need to add the following to the `routeMiddleware` array in `app/Http/Kernel.php`:

```php
'role' => \DigitalSeraph\Guardian\Middleware\GuardianRole::class,
'permission' => \DigitalSeraph\Guardian\Middleware\GuardianPermission::class,
'ability' => \DigitalSeraph\Guardian\Middleware\GuardianAbility::class,
```

### Configuration

Set the proper values in the `config/auth.php`.

To further customize table names and model namespaces, edit the `config/guardian.php` configuration file.

#### Users

Guardian uses the `app/Models` directory for storing models. **If you used [Laravel's Authentication Quickstart](https://laravel.com/docs/5.4/authentication#authentication-quickstart.** to generate your User model and controllers, make sure you update the values in your config file.

1. Generate the Guardian migrations: 

```bash
php artisan digitalseraph:guardian:make:migration all
```

This will generate the `<timestamp>_guardian_create_roles_tables.php` and `<timestamp>_guardian_create_permissions_tables.php` migrations. Now you can run the migration(s.:

```bash
php artisan migrate
```

After the migration, five new tables will be present:

1. `roles` - stores role records
2. `permissions` - stores permission records
3. `role_users` - stores [many-to-many](http://laravel.com/docs/4.2/eloquent#many-to-many. relations between roles and users
4. `role_admin_users` - stores [many-to-many](http://laravel.com/docs/4.2/eloquent#many-to-many. relations between roles and admin users
5. `permission_roles` - stores [many-to-many](http://laravel.com/docs/4.2/eloquent#many-to-many. relations between roles and permissions

### Models


#### Role

Generate the new Role model inside `app/models/Role.php` by running:

```bash
artisan digitalseraph:guardian:make:model role
```

The `Role` model is used for both the `User` and `AdminUser` models. Both have pivot tables to keep user data separate. `Role` model has four main attributes:
- `name` - Unique human readable name for the Role. For example: "User Administrator", "Project Owner", "Widget  Co. Employee".
- `slug` - Unique name for the Role, used for looking up role information in the application layer. For example: "admin", "owner", "employee".
- `description` - A more detailed explanation of what the Role does. This field is optional.
- `parent_id` - Optional field to inherit permissions from another role.

Both `parent_id` and `description` are optional; their fields are nullable in the database.

> Alternatively, you may use an existing Role model instead by extending the GuardianRole class, shown below. 

```php

namespace App\Models;

use DigitalSeraph\Guardian\Models\GuardianRole;

class Role extends GuardianRole
{
}
```

#### Permission

Generate the new Permission model inside `app/models/Permission.php` by running:

```bash
artisan digitalseraph:guardian:make:model permission
```

The `Permission` model has three main attributes:

- `name` - Unique human readable name for the permission. For example "Create Posts", "Edit Users", "Post Payments", "Subscribe to mailing list".
- `slug` - Unique name for the permission, used for looking up permission information in the application layer. For example: "create-post", "edit-user", "post-payment", "mailing-list-subscribe".
- `description` - A more detailed explanation of the Permission.

In general, it may be helpful to think of the last two attributes in the form of a sentence: "The permission `name` allows a user to `description`."

> Alternatively, you may use an existing Permission model instead by extending the GuardianPermission class, shown below. 

```php
<?php

namespace App\Models;

use DigitalSeraph\Guardian\Models\GuardianPermission;

class Permission extends GuardianPermission
{
}
```


#### User

Next, use the `GuardianUserTrait` trait in your existing `User` model. For example:

```php
<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use DigitalSeraph\Guardian\Traits\GuardianUserTrait;

class User extends Authenticatable
{
    use Notifiable;
    use GuardianUserTrait;

    // continued...
}
```

This will enable the relation with `Role` and add the following methods to the `User` model:

1. `roles()`
2. `hasRole($roleSlug, $requireAll = false)` - accepts either a string or an array of roles to check
3. `can($permissionSlug, $requireAll = false)` - accepts either a string or an array of permissions to check
4. `ability($roles, $permissions, $options)` - more advanced role and permission checking
5. `attachRoles($role)`
6. `detachRoles($role)`
7. `withoutRoles($roles)` static method that returns the users WITHOUT the specified roles
8. `withRoles($roles)` static method that returns the users WITH the specified roles


#### Admin User

Next, use the `GuardianAdminUserTrait` trait in your existing `AdminUser` model. For example:

```php
<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use DigitalSeraph\Guardian\Traits\GuardianAdminUserTrait;

class AdminUser extends Authenticatable
{
    use Notifiable;
    use GuardianAdminUserTrait;

    // continued...
}
```

This will enable the relation with `Role` and add the following methods to the `User` model:

1. `roles()`
2. `hasRole($roleSlug, $requireAll = false)` - accepts either a string or an array of roles to check
3. `can($permissionSlug, $requireAll = false)` - accepts either a string or an array of permissions to check
4. `ability($roles, $permissions, $options)` - more advanced role and permission checking
5. `attachRoles($role)`
6. `detachRoles($role)`
7. `withoutRoles($roles)` static method that returns the users WITHOUT the specified roles
8. `withRoles($roles)` static method that returns the users WITH the specified roles

Don't forget to dump composer autoload:

```bash
composer dump-autoload
```

### Seeders

1. Generate the seeder files: `artisan digitalseraph:guardian:make:seeder`
2. Add the seeders to your DatabaseSeeder class:

    ```php
    $this->call(GuardianRolesTableSeeder::class);
    ```

3. Generate the seeder files: `artisan digitalseraph:guardian:make:seeder`

4. Add the following to your DatabaseSeeder class: `$this->call(GuardianRolesTableSeeder::class);` or run `artisan db:seed --class=GuardianRolesTableSeeder`

5. Add the following to your DatabaseSeeder class: `$this->call(GuardianPermissionsTableSeeder::class);` or run `artisan db:seed --class=GuardianPermissionsTableSeeder`

### Usage

#### Concepts

> You can generate any of the following models using the artisan commands: `User`, `AdminUser`, `Role`, and `Permission` 

- Generate all the models: `artisan digitalseraph:guardian:make:model all`
- Generate a specific model: `artisan digitalseraph:guardian:make:model <user|admin_user|role|permission>`

---

1. Generate the seeder files: `artisan digitalseraph:guardian:make:seeder`
2. Add the following to your DatabaseSeeder class: `$this->call(GuardianRolesTableSeeder::class);` or run `artisan db:seed --class=GuardianRolesTableSeeder`
3. Add the following to your DatabaseSeeder class: `$this->call(GuardianPermissionsTableSeeder::class);` or run `artisan db:seed --class=GuardianPermissionsTableSeeder `
4. (Optionally) Add the Facade to your aliases in **config/app.php**:

    ```php
    'Guardian' => DigitalSeraph\Guardian\Facades\GuardianFacade::class,
    ```

5. (Optionally) Generate the models: `artisan digitalseraph:guardian:make:model`
6. Use the GuardianUserTrait in your User model:

    ```php
    <?php

    use DigitalSeraph\Guardian\Traits\GuardianUserTrait;

    class User extends Eloquent
    {
        use GuardianUserTrait; // add this trait to your user model

        ...
    }
    ```
