<?php

namespace app\admin\middleware;

use Closure;
use think\App;

/**
 * lake-admin 中间件
 *
 * @create 2020-4-7
 * @author deatil
 */
class LakeAdminAppMap
{

    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app  = $app;
    }

    /**
     * 中间件
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     *
     * @create 2020-4-7
     * @author deatil
     */
    public function handle($request, Closure $next)
    {
        $this->app->config->set([
            'app_map' => [
                'admin' => function($app) {
                    $app->http->path(dirname(__DIR__));
                },
            ],
        ], 'app');
        
        return $next($request);
    }

}
