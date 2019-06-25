<?php

/*
 * This file is part of the moonpie/macro.
 *
 * (c) moonpie<875010341@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace app\index\service\macro\ByteMiniPayment\Notify;

use Closure;

class Paid extends Handler
{
    //protected $check = false;
    /**
     * @param \Closure $closure
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function handle(Closure $closure)
    {
        $this->strict(
            \call_user_func($closure, $this->getMessage(), [$this, 'fail'])
        );

        return $this->toResponse();
    }
}
