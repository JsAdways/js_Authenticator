<?php

namespace Js\Authenticator\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Js\Authenticator\Contracts\PermissionContract;
use Js\Authenticator\Foundations\TreemapFoundation;

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

    // 取得前端路由 cache 名稱
    const SYSTEM_STRUCT_CACHE_NAME = 'system_struct';

    public function __construct(
        private readonly TreemapFoundation $TreemapFoundation
    )
    {}

    /**
     * 醬前端路由儲存到 cache
     */
    public function set_data(string $data): bool
    {
        return Cache::put(self::SYSTEM_STRUCT_CACHE_NAME, $data, now()->addSecond(15));
    }

    /**
     * 取得路由權限資料
     *
     * @return array[
     *     'head'
     *     'struct'
     * ]
     * @throws Exception
     */
    protected function get_route_structure(): array
    {
        $get_forestage_route = Http::asForm()->get(config('forestage.forestage_url'));

        if ($get_forestage_route->failed()) {
            throw new Exception('get forestage route is fail.');
        }

        if($get_forestage_route->json() === null){
            //nuxt版本
            if(!Cache::has(self::SYSTEM_STRUCT_CACHE_NAME)){
                throw new Exception('get system_struct cache is fail.');
            }
            $system_routers = collect(json_decode(Cache::get(self::SYSTEM_STRUCT_CACHE_NAME)));
            $system_routers = $system_routers->values();
        }else{
            //next版本
            $system_routers = json_decode(json_encode($get_forestage_route->json()));
            $system_routers = collect($system_routers);
        }

        //data_forget($system_routers, '*.component');

        //找出上方headMenu
        $menu_head = $system_routers->filter(function ($item) {
            return collect($item->meta)->has('inMenu') && $item->meta->inMenu === 'head';
        })->values();
        $menu_head = collect(json_decode($menu_head->toJson()));

        //找出左側leftMenu結構
        $menu_head = $this->TreemapFoundation->get_child($system_routers, $menu_head);
        $menu_head = $menu_head->reduce(function ($result, $item) {
            $result[$item->name] = $item;

            return $result;
        });

        //以router name為key，整理結構資料
        $default_system = '';
        $system_routers = $system_routers->sortBy('name');
        $struct = $system_routers->reduce(function ($result, $item) use ($default_system, $system_routers) {
            if ($item->meta->title !== '登入') {
                if (isset($item->meta->system)) {
                    $default_system = $item->meta->system;
                }
                $result[$item->name] = [];
                $result[$item->name]['crumb'] = $this->TreemapFoundation->get_struct($system_routers, $item)->reverse();
                $result[$item->name]['system'] = $default_system;
                $result[$item->name]['in_menu'] = $this->TreemapFoundation->get_parent_menu($system_routers, $item->meta->parent);
            }

            return $result;
        });

        return [
            'head' => $menu_head,
            'struct' => $struct
        ];
    }

    /**
     * 取得路由權限資料
     *
     * @return array[
     *     'status' => bool 狀態
     *     'message' => string 權限資訊
     * ]
     * @throws Exception
     */
    public function get(): array
    {
        if (config('forestage.custom_page_permission')) {
            $route_structure = config('forestage.page_permission');
        } else {
            $route_structure = $this->get_route_structure();
        }

        $permission_structure = [
            'page' => $route_structure,
            'function' => config('forestage.function_permission'),
        ];

        return $permission_structure;
    }
}
