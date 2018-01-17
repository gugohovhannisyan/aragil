<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.12.2017
 * Time: 15:38
 */

namespace Aragil\Model;


abstract class Model implements ModelInterface
{
    /**
     * @var null|\PDO
     */
    private $connection = null;

    /**
     * @var null|array
     */
    private $connectionParams = null;

    /**
     * Model constructor.
     * @param array $settings
     */
    public function __construct($settings)
    {
        if(isset($settings['pdo']) && $settings['pdo'] instanceof \PDO) {
            $this->setConnection($settings['pdo']);
        }

        if(isset($settings['connectionParams']) && is_array($settings['connectionParams'])) {
            $this->setConnectionParams($settings['connectionParams']);
        }
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        if(is_null($this->connection)) {
            $this->setConnection(getPdo($this->connectionParams));
        }

        return $this->connection;
    }

    /**
     * @param \PDO $pdo
     */
    public function setConnection(\PDO $pdo)
    {
        $this->connection = $pdo;
    }

    /**
     * @param array $params
     */
    public function setConnectionParams(array $params)
    {
        $this->connectionParams = $params;
    }

    public function insert($data)
    {
        $values = join(',', array_map(function ($row) {
            $row = array_map(function($value) {
                return $this->getConnection()->quote($value);
            }, $row);
            return '(' . join(',', $row) . ')';
        }, $data));

        $fields = join(',', array_keys($values[0]));

        return $this->getConnection()->exec("
            INSERT INTO {$this->getTable()}
              ({$fields})
            VALUES
              {$values}
        ");
    }
}