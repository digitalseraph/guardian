<?php

/**
 * This file is part of DigitalSeraph Guardian,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package DigitalSeraph\Guardian
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Role model, table, and foreign key
    |--------------------------------------------------------------------------
    |
    | This is the Role model used by Guardian to create correct relations.  Update
    | the role if it is in a different namespace.
    |
    */
   
    // Role model
    'role' => 'App\Models\Role',

    // Roles table
    'roles_table' => 'roles',

    // Role foreign key
    'role_foreign_key' => 'role_id',

    /*
    |--------------------------------------------------------------------------
    | User model, table, foreign key, and  Pivot table (for Roles Relation)
    |--------------------------------------------------------------------------
    |
    | This is yo User model used by Guardian to create correct relations.
    | Update the User if it is in a different namespace. This is typucally 
    | used for customers (as opposed to administrators)
    |
    */
   
    // User model
    'user' => 'App\Models\User',

    // Users table
    'users_table' => 'users',

    // `user foreign key`
    'user_foreign_key' => 'user_id',

    // `role_user` table (pivot table for users <-> role relationships)
    'role_user_table' => 'role_users',

    /*
    |--------------------------------------------------------------------------
    | AdminUser model, table, foreign key, and  Pivot table (for Roles Relation)
    |--------------------------------------------------------------------------
    |
    | This is the Administrator User model used by Guardian to create 
    | correct relations. Update the Administrative User if it is in a 
    | different namespace.
    |
    */
   
    // AdminUsers model
    'admin_user' => 'App\Models\AdminUser',

    // AdminUsers table
    'admin_users_table' => 'admin_users',

    // `admin user foreign key`
    'admin_user_foreign_key' => 'admin_user_id',

    // `role_admin_user` table (pivot table for admin users <-> role relationships)
    'role_admin_user_table' => 'role_admin_users',



    /*
    |--------------------------------------------------------------------------
    | Permissions model, table, 
    |--------------------------------------------------------------------------
    |
    | This is the Permission model used by Guardian to create correct relations.
    | Update the permission if it is in a different namespace.
    |
    */
   
    // Permissions model
    'permission' => 'App\Models\Permission',
    
    // Permissions table
    'permissions_table' => 'permissions',
    
    // `permission_role` table
    'permission_role_table' => 'permission_roles',
    
    // Permissions `permission foreign key` (pivot table for roles <-> permissions)
    'permission_foreign_key' => 'permission_id',

    /*
    |--------------------------------------------------------------------------
    | Roles table Seeder
    |--------------------------------------------------------------------------
    |
    | This is the name of the file to use for seeding the `roles` table.
    | This will be generated by Guardian automaticaally, but you may rename it.
    |
    */
    'roles_seeder' => 'GuardianRolestableSeeder',

    /*
    |--------------------------------------------------------------------------
    | Guardian Permissions table Seeder
    |--------------------------------------------------------------------------
    |
    | This is the name of the file to use for seeding the `permissions` table.
    | This will be generated by Guardian automaticaally, but you may rename it.

    */
    'permissions_seeder' => 'GuardianPermissionstableSeeder',
];
