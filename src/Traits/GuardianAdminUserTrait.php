<?php

namespace DigitalSeraph\Guardian\Traits;

/**
 * This file is part of DigitalSeraph Guardian,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package DigitalSeraph\Guardian
 */

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

trait GuardianAdminUserTrait
{
    //Big block of caching functionality.
    public function cachedRoles()
    {
        $userPrimaryKey = $this->primaryKey;
        $cacheKey = 'guardian_roles_for_admin_user_'.$this->$userPrimaryKey;
        if (Cache::getStore() instanceof TaggableStore) {
            return Cache::tags(Config::get('guardian.role_admin_user_table'))->remember($cacheKey, Config::get('cache.ttl'), function () {
                return $this->roles()->get();
            });
        } else {
            return $this->roles()->get();
        }
    }
    public function save(array $options = [])
    {
        //both inserts and updates
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('guardian.role_admin_user_table'))->flush();
        }
        return parent::save($options);
    }
    public function delete(array $options = [])
    {
        //soft or hard
        parent::delete($options);
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('guardian.role_admin_user_table'))->flush();
        }
    }
    public function restore()
    {
        //soft delete undo's
        parent::restore();
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('guardian.role_admin_user_table'))->flush();
        }
    }

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('guardian.role'), Config::get('guardian.role_admin_user_table'), Config::get('guardian.admin_user_foreign_key'), Config::get('guardian.role_foreign_key'));
    }

    /**
     * Boot the admin user model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the admin user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            if (!method_exists(Config::get('auth.model'), 'bootSoftDeletes')) {
                $user->roles()->sync([]);
            }

            return true;
        });
    }

    /**
     * Checks if the user has a role by its slug.
     *
     * @param string|array $slug       Role slug or array of role slugs.
     * @param bool         $requireAll All roles in the array are required.
     *
     * @return bool
     */
    public function hasRole($slug, $requireAll = false)
    {
        if (is_array($slug)) {
            foreach ($slug as $roleSlug) {
                $hasRole = $this->hasRole($roleSlug);

                if ($hasRole && !$requireAll) {
                    return true;
                } elseif (!$hasRole && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                if ($role->slug == $slug) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user has a permission by its slug.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool         $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function can($slug, $requireAll = false)
    {
        if (is_array($slug)) {
            foreach ($slug as $permissionSlug) {
                $hasPerm = $this->can($permissionSlug);

                if ($hasPerm && !$requireAll) {
                    return true;
                } elseif (!$hasPerm && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the perms were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the perms were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                // Validate against the Permission table
                foreach ($role->cachedPermissions() as $permission) {
                    if (str_is($slug, $permission->slug)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Checks role(s) and permission(s).
     *
     * @param string|array $roles       Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param array        $options     validate_all (true|false) or return_type (boolean|array|both)
     *
     * @throws \InvalidArgumentException
     *
     * @return array|bool
     */
    public function ability($roles, $permissions, $options = [])
    {
        // Convert string to array if that's what is passed in.
        if (!is_array($roles)) {
            $roles = explode(',', $roles);
        }
        if (!is_array($permissions)) {
            $permissions = explode(',', $permissions);
        }

        // Set up default values and validate options.
        if (!isset($options['validate_all'])) {
            $options['validate_all'] = false;
        } else {
            if ($options['validate_all'] !== true && $options['validate_all'] !== false) {
                throw new InvalidArgumentException();
            }
        }
        if (!isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        } else {
            if ($options['return_type'] != 'boolean' &&
                $options['return_type'] != 'array' &&
                $options['return_type'] != 'both') {
                throw new InvalidArgumentException();
            }
        }

        // Loop through roles and permissions and check each.
        $checkedRoles = [];
        $checkedPermissions = [];
        foreach ($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role);
        }
        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->can($permission);
        }

        // If validate all and there is a false in either
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if (($options['validate_all'] && !(in_array(false, $checkedRoles) || in_array(false, $checkedPermissions))) ||
            (!$options['validate_all'] && (in_array(true, $checkedRoles) || in_array(true, $checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }

        // Return based on option
        if ($options['return_type'] == 'boolean') {
            return $validateAll;
        } elseif ($options['return_type'] == 'array') {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        } else {
            return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
        }
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     */
    public function attachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->attach($role);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     */
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->detach($role);
    }

    /**
     * Attach multiple roles to an admin user
     *
     * @param mixed $roles
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->attachRole($role);
        }
    }

    /**
     * Detach multiple roles from an admin user
     *
     * @param mixed $roles
     */
    public function detachRoles($roles = null)
    {
        if (!$roles) {
            $roles = $this->roles()->get();
        }

        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }



    /**
     * Return the admin users without the specified roles
     *
     * @var array $roles
     * @return Collection
     */
    public static function withoutRoles($roles)
    {
        if (!$roles) {
            return null;
        }

        return Config::get('guardian.admin_user')::all()->reject(function ($adminUser) use ($roles) {
            return $adminUser->hasRole($roles);
        });
    }

    /**
     * Return the admin users with the specified role
     *
     * @var array $roles
     * @return Collection
     */
    public static function withRoles($roles)
    {
        if (!$roles) {
            return null;
        }

        return Config::get('guardian.admin_user')::all()->reject(function ($adminUser) use ($roles) {
            return !$adminUser->hasRole($roles);
        });
    }
}
