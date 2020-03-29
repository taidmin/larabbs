<?php

namespace App\Models\Traits;

use Spatie\QueryBuilder\QueryBuilder;

trait QueryBuilderBindable
{
    // 重写路由模型绑定
    public function resolveRouteBinding($value)
    {
        $queryClass = property_exists($this, 'queryClass')
            ? $this->queryClass
            : 'App\\Http\\Queries\\'.class_basename(self::class).'Query';

        if(!class_exists($queryClass)){
            return parent::resolveRouteBinding($value);
        }

        return (new $queryClass($this))
            ->where($this->getRouteKeyName(), $value)
            ->first();
    }
}
