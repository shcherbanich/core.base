<?php

namespace shcherbanich\core\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * ConsoleRunner - a component for running console commands on background.
 *
 * Usage:
 * ```
 * ...
 * $cr = new ConsoleRunner(['file' => '@my/path/to/yii']);
 * $cr->run('controller/action param1 param2 ...');
 * ...
 * ```
 * or use it like an application component:
 * ```
 * // config.php
 * ...
 * components [
 *     'consoleRunner' => [
 *         'class' => 'common\components\ConsoleRunner',
 *         'file' => '@my/path/to/yii' // or an absolute path to console file
 *     ]
 * ]
 * ...
 *
 * // some-file.php
 * Yii::$app->consoleRunner->run('controller/action',[' param1', 'param2']);
 * ```
 */
class ConsoleRunner extends Component
{
    /**
     * @var string Console application file that will be executed.
     * Usually it can be `yii` file.
     */
    public $file;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->file === null) {
            throw new InvalidConfigException('The "file" property must be set.');
        }
    }

    /**
     * Running console command on background
     *
     * @param string $command Argument that will be passed to console application
     * @param array $params
     * @return boolean
     */
    public function run($command, $params = []){

        $paramsArr = [];

        foreach($params as $param) {

            $paramsArr[] = escapeshellarg($param);
        }

        $cmd = PHP_BINDIR . '/php ' . Yii::getAlias($this->file) . " {$command} ".implode(' ',$paramsArr);
        if ($this->isWindows() === true) {
            pclose(popen('start /b ' . $cmd, 'r'));
        } else {
            pclose(popen($cmd . ' > /dev/null &', 'r'));
        }
        return true;
    }

    /**
     * Check operating system
     *
     * @return boolean true if it's Windows OS
     */
    protected function isWindows()
    {
        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32') {
            return true;
        } else {
            return false;
        }
    }
}
