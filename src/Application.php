<?php
/**
 * @author 小日日
 * @time 2023/1/27
 */

namespace roc;

class Application
{
    /**
     * @return void
     */
    public static function init()
    {
        Container::bind(IRoutes::class,TrieRoutes::class);
    }

}