<?php
/**
 * @access    public
 * @author    liyu
 * @desc      基础模型
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    //定义范围查询 制定相关字段
    public function scopeWithOnly($query, $relation, Array $columns)
    {
        return $query->with([$relation => function ($query) use ($columns){
            $query->select(array_merge(['id'], $columns));
        }]);
    }
    //定义范围查询 并执行查询条件
    public function scopeWithFilter($query, $relation, Array $columns_filter)
    {
        return $query->with([$relation => function ($query) use ($columns_filter){
            $query->select(array_merge(['id'], $columns_filter['columns']))->where($columns_filter['filters']);
        }]);
    }
}
