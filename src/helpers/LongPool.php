<?php

namespace shcherbanich\core\helpers;

use Yii;

class LongPool
{

    /**
     * Сгенерировать канал
     *
     * @var string $prefix
     *
     * @return array
     */
    public static function generateChannelData($prefix = '')
    {
        return [
            'channel' => $prefix.Yii::$app->security->generateRandomString(50),
            'expires_in' => time() + 3600
        ];
    }

    /**
     * Проверить данные канала
     *
     * @var array $channel_data
     *
     * @return boolean
     */
    public static function validateChannelData(array $channel_data){

        return $channel_data && isset($channel_data['channel']) && isset($channel_data['expires_in']) && $channel_data['expires_in'] > time();
    }
}