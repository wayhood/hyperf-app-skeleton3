<?php

declare(strict_types=1);
namespace App\Validate;

use DeathSatan\Hyperf\Validate\Lib\AbstractValidate as BaseValidate;

class TestGetValidate extends BaseValidate
{
    /**
     * @var array 自定义场景
     */
    protected $scenes =[];

    /**
     * 规则
     * @return array
     */
    protected function rules():array
    {
        return [
            'nick'=>[
                'required'
            ]
        ];
    }

    /**
     * 自定义错误消息
     * @return array
     */
    protected function messages():array
    {
        return [
            'nick.required'=>'昵称必须'
        ];
    }

    /**
     * 自定义验证属性
     * @return array
     */
    protected function attributes():array
    {
        return [];
    }
}
