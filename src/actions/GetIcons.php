<?php

namespace markhuot\keystone\actions;

class GetIcons
{
    public function handle()
    {
        $iconPath = \Craft::getAlias('@templates/icons/');
        $icons = glob($iconPath.'*.svg');

        return collect($icons)
            ->map(fn ($path) => [
                'path' => str_replace($iconPath, '', $path),
                'name' => ucfirst(pathinfo($path, PATHINFO_FILENAME)),
            ]);
    }
}
