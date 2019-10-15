<?php

/*
 *  Copyright (c) 2018-2019.
 *  This file is part of the moonpie production
 *  (c) johnzhang <875010341@qq.com>
 *  This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
*/

namespace Moonpie\Macro\ByteMiniProgram\ContentSecurity;

use Moonpie\Macro\ByteMiniProgram\Kernel\BaseClient;

/**
 * 处理内容安全的方法集合
 * @author johnzhang <875010341@qq.com>
 */
class Client extends BaseClient
{
    /**
     * 文本信息监测
     * @param array $text
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function checkText($text)
    {
        $text = (array) $text;
        $params = [
            'tasks' => []
        ];
        foreach ($text as $txt) {
            $params['tasks'][] = ['content' => $txt];
        }
        $headers = $this->getAccessTokenHeaders();

        return $this->httpPostJsonWithHeader('v2/tags/text/antidirt', $params, $headers);
    }
    /**
     * 获取token的header信息
     */
    protected function getAccessTokenHeaders()
    {
        $headers = [];
        if ($this->accessToken) {
            $headers = [
                'X-Token' => $this->accessToken->getToken()[$this->accessToken->getTokenKey()],
            ];
        }
        return $headers;
    }

    /**
     * 图片内容监测
     * @param string $path
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function checkImage(array $targets, array $urls)
    {
        $params = [
            'targets' => $targets,
            'tasks' => [],
        ];
        foreach ($urls as $url) {
            $params['tasks'][] = ['image' => $url];
        }
        $headers = $this->getAccessTokenHeaders();
        return $this->httpPostJsonWithHeader('v2/tags/image', $params, $headers);
    }
}
