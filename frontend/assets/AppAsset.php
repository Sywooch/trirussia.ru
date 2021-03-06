<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/2.8.0/css/flag-icon.min.css',
        '/css/select2-bootstrap.css',
        '/css/font.css',
        '/css/likely.css',
        '/css/jquery.fancybox.min.css',
        '/css/custom.css?v=1',
        '/css/site.css',
        '/css/style.css',
    ];
    public $js = [
        '/js/jquery-match-height.js',
        '/js/jquery.mask.js',
        '/js/modernizr.js',
        '/js/tilt.jquery.js',
        '/js/jquery.lazyload.min.js',
        '/js/jquery.smooth-scroll.js',
        '/js/jquery.fancybox.min.js',
        '/js/theia-sticky-sidebar.js',
        '/js/scotchPanels.min.js',
        //'/js/leftside-top-menu.js',
        '/js/modalfix.js',
        '/js/ad-sidebar.js',
        '/js/pace.min.js',
        '/js/geopattern-1.2.3.min.js',
        '/js/site.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
