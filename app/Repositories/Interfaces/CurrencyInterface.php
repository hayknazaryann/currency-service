<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface CurrencyInterface extends EloquentInterface
{
    /**
     * @param string $date
     * @return Model|null
     */
    public function findByDate(string $date): ?Model;
}
