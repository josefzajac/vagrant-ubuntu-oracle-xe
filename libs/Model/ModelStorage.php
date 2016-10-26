<?php
namespace App\Model;

class ModelStorage
{
    private static $classModels = array();
    private static $tableModels = array();

    /** @var \Dibi\Connection @inject */
    public static $db;

    /**
     * @param string $table
     *
     * @return BaseModel
     */
    public static function getModelByTableName($table)
    {
        if (!array_key_exists($table, self::$tableModels)) {
            self::$tableModels[$table] = new BaseModel($table);
        }

        return self::$tableModels[$table];
    }

    /**
     * @param string $modelClass
     *
     * @return BaseModel
     * @throws Exception
     */
    public static function getModelByModelClass($modelClass)
    {
        $modelClass = '\App\Model\\' . $modelClass;
        if (!class_exists($modelClass)) {
            throw  new \Exception('Model cannot be loaded! Class "' . $modelClass . '" does not exists!');
        }

        if (!array_key_exists($modelClass, self::$classModels)) {
            self::$classModels[$modelClass] = new $modelClass;
            self::$classModels[$modelClass]->db = self::$db;
        }

        return self::$classModels[$modelClass];
    }

}