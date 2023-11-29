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
            'components/drag.js',
            'lib/alpine.min.js',
            'lib/axios.min.js',
            'components/post.js',
            'components/slideout.js',
            'components/edit.js',
        ];

        $this->css = [
            'keystone.min.css',
        ];

        parent::init();
    }
}
