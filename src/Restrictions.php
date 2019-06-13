<?php

declare(strict_types=1);

namespace DKulyk\Restrictions;

use Illuminate\Support\Facades\Facade;

/**
 * Class Restrictions.
 * 
 * @method static array pushRestrictions(array $restrictions, bool $merge = true) Push restrictions to scope and returns new restrictions.
 * @method static array popRestrictions() Pop restrictions from scope and returns them.
 * @method static void where(array $restrictions, $merge = true, callable $callback = null)
 * @method static array getRestrictions()
 */
final class Restrictions extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'eloquent-restrictions';
    }
}
