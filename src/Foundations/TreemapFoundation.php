<?php

namespace Js\Authenticator\Foundations;

use Illuminate\Support\Collection;

class TreemapFoundation
{
    /**
     * 取得路由結構
     *
     * @param Collection $system_routers
     * @param object $node
     * @return Collection
     */
    public function get_struct(Collection $system_routers, object $node): Collection
    {
        $parent = collect([[
            'name' => $node->name,
            'title' => $node->meta->title,
        ]]);

        $parent_item = $system_routers->filter(function($item)use($node){
            return $item->name == $node->meta->parent;
        });
        if(count($parent_item) !== 0){
            $parent = $parent->merge(self::get_struct($system_routers, $parent_item->first()));
        }

        return $parent;
    }

    /**
     * 取得路由子結構
     *
     * @param Collection $system_routers
     * @param Collection $node
     * @param string $system
     * @return Collection
     */
    public function get_child(Collection $system_routers, Collection $nodes, ?string $system = null): Collection
    {
        foreach($nodes as $v){
            $curr_system = ($system === null) ? $v->meta->system : $system;

            $children = $system_routers->filter(function($item)use($v){
                return collect($item->meta)->has('parent') && $item->meta->parent == $v->name;
            })->reduce(function ($result, $item, $key)use($curr_system){
                $item->meta->system = $curr_system;
                $result[$key] = $item;

                return $result;
            });

            if($children !== null) {
                $children = collect(json_decode(json_encode($children)))->values();
                $v->child = self::get_child($system_routers, $children, $curr_system);
            }
        }

        return $nodes;
    }

    /**
     * 取得路由子結構
     *
     * @param Collection $system_routers
     * @param Collection $node
     * @param string $system
     * @return Collection
     */
    public function get_parent_menu(Collection $system_routers, string $parent_node): Collection
    {
        $menu = collect([]);

        $parent_item = $system_routers->filter(function($item)use($parent_node){
            return isset($item->meta->inMenu) && $item->meta->inMenu == 'left' && $item->name == $parent_node;
        });

        foreach($parent_item as $v){
            $menu->push($v->name);
            $menu = $menu->merge(self::get_parent_menu($system_routers, $v->meta->parent));
        }

        return $menu;
    }
}