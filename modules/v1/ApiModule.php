<?php

namespace app\modules\api\v1;

use yii\base\Module;

/**
 * Class ApiModuleV1
 *
 * @property string $controllerNamespace
 *
 * @package app\modules\api\v1
 */
class ApiModule extends Module
{
    /**
     * @var string
     */
    public $controllerNamespace = 'app\modules\api\v1\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
}
