<?php

namespace markhuot\keystone\assetbundles;

use craft\web\AssetBundle;

class KeystoneAssetBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@keystone/resources';

        $this->depends = [];

        $this->js = [
            'components/create.js',
            'components/edit.js',
            'components/drag.js',
        ];

        $this->css = [
            'keystone.css',
        ];

        parent::init();
    }
}
