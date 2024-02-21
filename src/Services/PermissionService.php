<?php

namespace Js\Authenticator\Services;

use Cache;
use Exception;
use File;
use Log;
use Js\Authenticator\Contracts\PermissionContract;

class PermissionService implements PermissionContract
{
    //路由資料取值關鍵字
    const ROUTE_DATA_KEYS = ['name', 'title', 'parent'];

    //值是取得值的關鍵字
    const COMPONENT_DATA_KEYS = [
        'name' => 'name',
        'title' => 'title',
        'parent' => 'data-parent',
    ];

    const CACHE_NAME = 'permission_structure';

    protected $path;

    public function __construct()
    {
        $folder = config('forestage.forestage_path');
        $this->path = base_path($folder);
    }

    /**
     * 取得路由權限資料
     *
     * @return array[
     *     'status' => bool 狀態
     *     'data' => array 權限資訊
     * ]
     */
    protected function get_route_structure(): array
    {
        //前端主路由檔案
        $main_route_path = "{$this->path}/router.js";
        if (! File::exists($main_route_path)) {
            throw new Exception("{$main_route_path} is not exists.");
        }
        $route_paths[] = $main_route_path;

        //取得主路由內容
        $main_route_content = File::get($main_route_path);
        //去除換行與空白
        $main_route_content = preg_replace('/\s(?=)/', '', $main_route_content);
        preg_match_all('/from\'\~\/(routerComponent.*?)\'/', $main_route_content, $sub_routes);
        $sub_routes = $sub_routes[1];
        if (! empty($sub_routes)) {
            foreach ($sub_routes as $v) {
                $route_paths[] = "{$this->path}/{$v}.js";
            }
        }

        //取得所有路徑權限
        $permission_structures = [];
        foreach ($route_paths as $route_path) {
            $path_content = File::get($route_path);
            $path_content = preg_replace('/\s(?=)/', '', $path_content);
            preg_match('/\[.*?\]/', $path_content, $route_block);
            if (empty($route_block)) {
                Log::debug("{$route_path} is empty.");
                continue;
            }
            //取得路由區塊
            preg_match_all('/\{(.*?)\}\}/', $route_block[0], $routes);
            if (empty($routes[1])) {
                Log::debug("{$route_path} info is empty.");
                continue;
            }

            foreach ($routes[1] as $route) {
                $funtion_info = [
                    'type' => 'page',
                ];
                $data_is_integrity = true;
                foreach (self::ROUTE_DATA_KEYS as $key) {
                    $rule = "/{$key}:'(.*?)',/";
                    preg_match($rule, $route, $data);
                    if (empty($data)) {
                        $data_is_integrity = false;
                        break;
                    }
                    $funtion_info[$key] = $data[1];
                }
                if ($data_is_integrity) {
                    $permission_structures[] = $funtion_info;
                }
            }
        }

        return $permission_structures;
    }

    /**
     * 取得路由權限資料
     *
     * @return array[
     *     'status' => bool 狀態
     *     'message' => string 權限資訊
     * ]
     */
    protected function set_cache(array $permission_structure): void
    {
        if (Cache::has(self::CACHE_NAME)) {
            Cache::forget(self::CACHE_NAME);
        }

        if (! Cache::put(self::CACHE_NAME, $permission_structure, $seconds = 1440)) {
            throw new Exception('cache had error.');
        }
    }

    /**
     * 取得路由權限資料
     *
     * @return array[
     *     'status' => bool 狀態
     *     'message' => string 權限資訊
     * ]
     */
    public function get(): array
    {
        $permission_structure = Cache::get(self::CACHE_NAME);
        if (is_null($permission_structure)) {    
            if (config('forestage.custom_page_permission')) {
                $route_structure = config('forestage.page_permission');
            } else {
                $route_structure = $this->get_route_structure();
            }

            $permission_structure = [
                'page' => $route_structure,
                'function' => config('forestage.function_permission'),
            ];

            $this->set_cache($permission_structure);
        }

        return $permission_structure;
    }
}