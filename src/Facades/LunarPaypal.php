<?php

namespace Creative2llc\LunarPaypal\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Creative2llc\LunarPaypal\PaypalPaymentType
 */
class LunarPaypal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Creative2llc\LunarPaypal\PaypalPaymentType::class;
    }
}
