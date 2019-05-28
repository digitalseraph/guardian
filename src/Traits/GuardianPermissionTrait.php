<?php

namespace DigitalSeraph\Guardian\Traits;

/**
 * This file is part of White Sunrise Guardian,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package DigitalSeraph\Guardian
 */

use Illuminate\Support\Facades\Config;

trait GuardianPermissionTrait
{
    /**
     * Many-to-Many relations with role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('guardian.role'), Config::get('guardian.permission_role_table'), Config::get('guardian.permission_foreign_key'), Config::get('guardian.role_foreign_key'));
    }

    /**
     * Boot the permission model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the permission model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($permission) {
            if (!method_exists(Config::get('guardian.permission'), 'bootSoftDeletes')) {
                $permission->roles()->sync([]);
            }

            return true;
        });
    }
}
