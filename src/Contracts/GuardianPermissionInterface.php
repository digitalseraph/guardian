<?php

namespace WhiteSunrise\Guardian\Contracts;

/**
 * This file is part of WhtieSunrise Guardian,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package WhiteSunrise\Guardian
 */

interface GuardianPermissionInterface
{

    /**
     * Many-to-Many relations with role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();
}
