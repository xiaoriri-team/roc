<?php
/**
 * @author 小日日
 * @time 2023/2/9
 */

namespace roc\controller;

use roc\Context;

class UserController
{

    public function index(Context $context)
    {
        $context->writeJson(['user' => 'index']);
    }
}