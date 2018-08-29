<?php
namespace sorokinmedia\asset_manager;

/**
 * Class DeployController
 * @package sorokinmedia\asset_manager
 */
class DeployController extends \yii\console\Controller
{
    public $defaultAction = 'prod';

    public $frontendRepo;
    public $frontendAppearanceAssetsPath;
    public $backendAppearanceAssetsPath;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $this->frontendRepo = \Yii::getAlias('@react') . '/dsts-rental-service-front';

        $this->frontendAppearanceAssetsPath = \Yii::getAlias('@root') . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'web'. DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . '*';
        $this->backendAppearanceAssetsPath = \Yii::getAlias('@root') . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'web'. DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . '*';

        return parent::beforeAction($action);
    }

    /**
     * Выполнить полный деплой
     */
    public function actionAll()
    {
        $this->actionProd();
        //$this->actionCompress();
    }

    /**
     * для прода
     */
    public function actionProd()
    {
        $this->actionPullMaster();
        $this->actionBuild();
        $this->actionFlush();
        $this->actionFlushYii();
    }

    /**
     * для прода с инсталом
     */
    public function actionProdInstall()
    {
        $this->actionPullMaster();
        $this->actionNpmInstall();
        $this->actionBuild();
        $this->actionFlush();
        $this->actionFlushYii();
    }

    /**
     * для дева
     */
    public function actionDev()
    {
        $this->actionPull();
        $this->actionBuild();
        $this->actionFlush();
        $this->actionFlushYii();
    }

    /**
     * для дева с инсталом
     */
    public function actionDevInstall()
    {
        $this->actionPull();
        $this->actionNpmInstall();
        $this->actionBuild();
        $this->actionFlush();
        $this->actionFlushYii();
    }

    /**
     * Сбилдить bundle.js
     */
    public function actionBuild()
    {
        shell_exec("swapoff -a && swapon -a; cd {$this->frontendRepo}; npm run build_prod;"); // перекачаем мастер
        echo 'build finished';
    }

    /**
     * запускает npm install
     */
    public function actionNpmInstall()
    {
        shell_exec("cd {$this->frontendRepo}; npm i;"); // перекачаем мастер
        echo 'build finished';
    }

    /**
     * Спуллить все репы в их текущих ветках
     */
    public function actionPull()
    {
        shell_exec("cd {$this->frontendRepo}; git pull;"); // перекачаем компоненты
    }

    /**
     * Спуллить все репы в состояние мастер
     */
    public function actionPullMaster()
    {
        shell_exec("cd {$this->frontendRepo}; git checkout master; git pull;"); // перекачаем верстку
    }

    /**
     * Почистить кэш асетов
     */
    public function actionFlush()
    {
        if(!file_exists(AssetVersion::assetConfigPath())) { // версия ассэтов хранится в файле, если файл не существует - создаем
            $assetVersionFile = fopen(AssetVersion::assetConfigPath(), "w");
            fwrite($assetVersionFile, 0);
            fclose($assetVersionFile);
        }
        file_put_contents(AssetVersion::assetConfigPath(), (file_get_contents(AssetVersion::assetConfigPath()) + 1));


        shell_exec("rm -rf {$this->frontendAppearanceAssetsPath}"); // удаляем ассеты с бандлами чтобы перекопировать их
        echo 'front cache flushed' . PHP_EOL;
    }

    /**
     * чистка кеша Yii
     */
    public function actionFlushYii()
    {
        shell_exec("php yii cache/flush-all");
        echo 'yii cache flushed' . PHP_EOL;
    }

    /**
     * Gzip компрессия
     */
    public function actionCompress()
    {
        $rootPath = \Yii::getAlias('@root') . DIRECTORY_SEPARATOR;
        shell_exec('for file in `find ' . $rootPath . ' -name \*.css -type f -o -name \*.js -type f -o -name \*.html -type f`;
        do gzip -9 -f -c $file > $file.gz;
        done');
    }
}