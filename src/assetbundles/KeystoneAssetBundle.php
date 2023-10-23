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
            'lib/alpine.min.js',
            'lib/axios.min.js',
            'components/alpine.js',
        ];

        $this->css = [
            'keystone.min.css',
        ];

        parent::init();
    }
}
