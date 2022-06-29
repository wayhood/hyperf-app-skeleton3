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
#[Action('test.list')]
# 分类
#[Category(name: '测试')]
# 描述
#[Description(name: '测试列表')]
# 请求参数
#[RequestParam(name: 'start', type: 'int', require: false, example: '0',     description: '起始位置, 默认从0开始')]
#[RequestParam(name: 'limit', type: 'int', require: false, example: '10',    description: '获取记录条数, 默认10条')]
# 响应参数
#[ResponseParam(name: 'users',                 type: 'array',    example: 'users[]',     description: '用户数组')]
#[ResponseParam(name: 'users.0',               type: 'map',      example: '无',          description: '用户对象')]
#[ResponseParam(name: 'users.0.id',            type: 'int',      example: '1',           description: 'id')]
#[ResponseParam(name: 'users.0.mobile_phone',  type: 'string',   example: '12345789001', description: '电话')]
#[ResponseParam(name: 'users.0.password',      type: 'float',    example: 'opxxxxxx',    description: '密码')]
# 错误代码
#[ErrorCode(code: 1000, message: '不知道')]
# 是否可用
#[Usable]
# 是否需要Token
#[Token(token: false)]
class ListAction extends AbstractAction
{
    public function run($params, $extras, $headers) {
        $start = 0;
        $limit = 10;
        if (isset($params['start'])) {
            $start = intval($params['start']);
        }

        if (isset($params['limit'])) {
            $limit = intval($params['limit']);
        }

        // $res = DB::query("SELECT * FROM `account` LIMIT ?,?", [$start, $limit]);
        return $this->errorReturn(1000);
        return $this->successReturn([
            'users' => []
        ]);
    }
}