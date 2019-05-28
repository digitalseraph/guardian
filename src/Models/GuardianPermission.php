<?php

namespace DigitalSeraph\Guardian\Models;

/**
 * This file is part of DigitalSeraph Guardian,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package DigitalSeraph\Guardian
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use DigitalSeraph\Guardian\Contracts\GuardianPermissionInterface;
use DigitalSeraph\Guardian\Traits\GuardianPermissionTrait;

class GuardianPermission extends Model implements GuardianPermissionInterface
{
    use GuardianPermissionTrait;

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
        $this->table = Config::get('guardian.permissions_table');
    }
}
