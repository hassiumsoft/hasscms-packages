<?php

/**
 * HassCMS (http://www.hassium.org/).
 *
 * @link http://github.com/hasscms for the canonical source repository
 *
 * @copyright Copyright (c) 2016-2099 Hassium Software LLC.
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
namespace hass\module\components;

use hass\helpers\PackageLoader;
use yii\base\BootstrapInterface;
use hass\module\BaseModule;
use hass\helpers\Util;

/**
 *
 * @author zhepama <zhepama@gmail.com>
 *        
 * @since 0.1.0
 */
class ModuleManager extends PackageLoader
{

    const BOOTSTRAP_FRONTEND = 1;

    const BOOTSTRAP_BACKEND = 2;

    public $paths = [
        "@root/modules",
        "@root/core"
    ];

    public $infoClass = "\\hass\\module\\classes\\ModuleInfo";

    public function init()
    {
        parent::init();
    }

    /**
     * 若模块是启用的,则会添加到psr4File中
     */
    public function generatePsr4File()
    {}

    public function loadBootstrapModules($bootstrapType)
    {
        $modules = \hass\module\models\Module::findEnabledModules();
        
        /** @var \hass\module\models\Module $model */
        foreach ($modules as $model) {
            
            if (empty($model->class)) {
                continue;
            }
            // 如果有模块,而且模块的类存在则跳过,配置文件优先
            if (\Yii::$app->hasModule($model->id)) {
                $modules = \Yii::$app->getModules();
                if (is_object($modules[$model->id]) || isset($modules[$model->id]['class'])) {
                    continue;
                }
            }
            
            Util::setModule($model->id, [
                'class' => $model->class
            ]);
            $bootstraps = explode("|", $model->bootstrap);
            if (in_array($bootstrapType, $bootstraps)) {
                /** @var \hass\module\BaseModule $module */
                $module = \Yii::$app->getModule($model->id);
                
                if ($module instanceof BaseModule) {
                    $module->setModuleInfoModel($model);
                }
                
                if ($module instanceof BootstrapInterface) {
                    $module->bootstrap(\Yii::$app);
                }
            }
        }
    }
}