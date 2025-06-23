<?php

namespace FlexBlade;

/**
 * @deprecated This class is deprecated and will be removed in a future version.
 *             Use Blade::Bag() directly instead of View::Bag().
 */
class View
{
    /**
     * @deprecated Use Blade::Bag() directly instead.
     */
    public static function Bag(): Bag
    {
        // Trigger deprecation notice
        @trigger_error(
            'FlexBlade\View::Bag() is deprecated. Use Blade::Bag() directly instead.',
            E_USER_DEPRECATED
        );

        return Blade::Bag();
    }
}