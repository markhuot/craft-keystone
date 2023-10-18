<?php

namespace markhuot\keystone\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\vue\VueAsset;

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
            'keystone.min.css',
        ];

        parent::init();
    }
}
