<?php

namespace WhiteSunrise\Guardian\Models;

/**
 * This file is part of WhiteSunrise Guardian,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package WhiteSunrise\Guardian
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use WhiteSunrise\Guardian\Contracts\GuardianRoleInterface;
use WhiteSunrise\Guardian\Traits\GuardianRoleTrait;

class GuardianRole extends Model implements GuardianRoleInterface
{
    use GuardianRoleTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('guardian.roles_table');
    }
}
