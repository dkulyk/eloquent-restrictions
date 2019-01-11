<?php

declare(strict_types=1);

namespace DKulyk\Restrictions;

final class Restrictions
{
    private static $restrictions = [];

    /**
     * Push restrictions to scope and returns new restrictions.
     *
     * @param array $restrictions
     * @param bool $merge
     *
     * @return array
     */
    public static function pushRestrictions(array $restrictions, bool $merge = true)
    {
        return self::$restrictions[] = $merge ? array_merge(self::getRestrictions(), $restrictions) : $restrictions;
    }

    /**
     * Pop restrictions from scope and returns them.
     *
     * @return array
     */
    public static function popRestrictions()
    {
        return array_pop(self::$restrictions) ?: [];
    }

    /**
     * @param  array $restrictions
     * @param  bool|callable $merge
     * @param  callable|null $callback
     *
     * @return mixed
     */
    public static function where(array $restrictions, $merge = true, callable $callback = null)
    {
        if (is_callable($merge)) {
            $callback = $merge;
            $merge = true;
        }

        self::pushRestrictions($restrictions, $merge);

        if (is_null($callback)) {
            return;
        }

        try {
            return call_user_func($callback);
        } finally {
            self::popRestrictions();
        }
    }

    /**
     * Get current scope restrictions.
     *
     * @return array
     */
    public static function getRestrictions(): array
    {
        return end(self::$restrictions) ?: [];
    }
}
