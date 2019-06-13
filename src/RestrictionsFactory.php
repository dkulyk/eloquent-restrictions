<?php

declare(strict_types=1);

namespace DKulyk\Restrictions;

/**
 * Class RestrictionsFactory.
 */
final class RestrictionsFactory
{
    private $restrictions = [];

    /**
     * Push restrictions to scope and returns new restrictions.
     *
     * @param  array  $restrictions
     * @param  bool  $merge
     *
     * @return array
     */
    public function pushRestrictions(array $restrictions, bool $merge = true)
    {
        return $this->restrictions[] = $merge ? array_merge($this->getRestrictions(), $restrictions) : $restrictions;
    }

    /**
     * Pop restrictions from scope and returns them.
     *
     * @return array
     */
    public function popRestrictions()
    {
        return array_pop($this->restrictions) ?: [];
    }

    /**
     * @param  array  $restrictions
     * @param  bool|callable  $merge
     * @param  callable|null  $callback
     *
     * @return void
     */
    public function where(array $restrictions, $merge = true, callable $callback = null): void
    {
        if (is_callable($merge)) {
            $callback = $merge;
            $merge = true;
        }

        $this->pushRestrictions($restrictions, $merge);

        if (is_null($callback)) {
            return;
        }

        try {
            call_user_func($callback);
        } finally {
            $this->popRestrictions();
        }
    }

    /**
     * Get current scope restrictions.
     *
     * @return array
     */
    public function getRestrictions(): array
    {
        return end($this->restrictions) ?: [];
    }
}