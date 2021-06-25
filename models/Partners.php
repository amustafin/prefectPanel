<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "partners".
 *
 * @property int        $id
 * @property string     $name
 * @property string     $api_key
 *
 */
class Partners extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'integrators';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'api_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'        => Yii::t('app', 'ID'),
            'name'      => Yii::t('app', 'Name'),
            'api_key'   => Yii::t('app', 'Api Key'),
        ];
    }

    /**
     * @param $apiToken
     * @return array|ActiveRecord|null
     */
    public static function getByApiKey($apiToken): ?ActiveRecord
    {
        return self::findOne(['api_key' => $apiToken]);
    }

    /**
     * @param bool $insert
     *
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (empty($this->api_key)) {
            $this->generateApiKey(false);
        }

        return parent::beforeSave(
            $insert
        );
    }

    /**
     * @param bool $needToSave
     *
     * @return Partners|bool
     */
    public function generateApiKey($needToSave = true)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
        do {
            /**
             *  допустимых символов 64, код на выходе также 64-значный, поэтому для первых (64)! записей
             *  этого кода будет достаточно. Если записей предполагается больше, нужно продумать другую логику,
             *  которая выходит за рамки тестового задания.
             */
            $this->api_key = str_shuffle($permitted_chars);
            $isExist = self::findOne(['api_key' => $this->api_key]);
        } while ($isExist);

        return ($needToSave ? $this->save() : $this);
    }
}
