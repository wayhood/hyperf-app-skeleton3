<?php

declare(strict_types=1);
namespace App\Action\Test;

use Hyperf\DB\DB;
use Wayhood\HyperfAction\Annotation\Action;
use Wayhood\HyperfAction\Annotation\Category;
use Wayhood\HyperfAction\Annotation\Description;
use Wayhood\HyperfAction\Annotation\RequestParam;
use Wayhood\HyperfAction\Annotation\ResponseParam;
use Wayhood\HyperfAction\Annotation\Usable;
use Wayhood\HyperfAction\Annotation\ErrorCode;
use Wayhood\HyperfAction\Annotation\Token;
use Wayhood\HyperfAction\Action\AbstractAction;

# 以下注解用于生成文档校验数据类型和过滤响应输出
#[Action('test.get')]
# 分类
#[Category(name: '测试')]
# 描述
#[Description(name: '测试请求')]
# 请求参数
#[RequestParam(name: 'nick', type: 'string', require: true, example: 'test', description: '用户昵称')]
#[RequestParam(name: 'a',    type: 'string', require: true, example: 'a',    description: '请求参数a')]
#[RequestParam(name: 'b',    type: 'int',    require: true, example: '1',    description: '请求参数b')]
#[RequestParam(name: 'c',    type: 'float',  require: true, example: '0.1',  description: '请求参数c')]
# 响应参数
#[ResponseParam(name: 'user',      type: 'map',    example: '无',          description: '返回用户信息')]
#[ResponseParam(name: 'user.name', type: 'string', example: 'syang',       description: '返回用户信息')]
#[ResponseParam(name: 'user.age',  type: 'int',    example: '40',          description: '返回用户信息')]
#[ResponseParam(name: 'user.tel',  type: 'string', example: '12345789001', description: '返回用户信息')]
# 错误代码
#[ErrorCode(code: 1000, message: 'error')]
# 是否可用
#[Usable]
# 是否需要Token
#[Token(token: false)]
class GetAction extends AbstractAction
{
    public function run($params, $extras, $headers) {
        return $this->successReturn([
            'user' => [
                'name' => 'syang',
                'age' => 40,
                'tel' => '1234567890'
            ]
        ]);
    }
}