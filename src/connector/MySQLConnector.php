<?php
/**
 * MySQL连接类.
 * User: Sanit <huangsinan@zsdx.cn>
 * Date: 2017/3/13
 * Time: 19:44
 */

namespace DatabaseCompare\Connector;

use PDO;
use DatabaseCompare\Connection;

class MySQLConnector extends Connection
{
    public function parseDsn($config)
    {
        // TODO: Implement parseDsn() method.
        $dsn = 'mysql:dbname=' . $config['database'] . ';host=' . $config['hostname'];
        if (!empty($config['hostport'])) {
            $dsn .= ';port=' . $config['hostport'];
        } elseif (!empty($config['socket'])) {
            $dsn .= ';unix_socket=' . $config['socket'];
        }
        if (!empty($config['charset'])) {
            $dsn .= ';charset=' . $config['charset'];
        }
        return $dsn;
    }

    public function getTables($dbName = '')
    {
        // TODO: Implement getTables() method.
        $this->initConnect();
        $sql = !empty($dbName) ? 'SHOW TABLES FROM ' . $dbName : 'SHOW TABLES ';
        $pdo = $this->linkID->query($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info = [];
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    public function getTableStructure($tableName, $baseDb)
    {
        // TODO: Implement getTableStructure() method.
        $this->initConnect();
        if (empty($tableName)) {
            throw new \PDOException('表名字无法为空');
        }
        $tablesStr = '';
        foreach ($tableName as $key => $value) {
            if(!empty($value)) {
                $tablesStr = $tablesStr . "'" . $value . "'".",";
            }
        }
        $tablesStr = substr($tablesStr,0,strlen($tablesStr)-1);
        $sql = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_DEFAULT, IS_NULLABLE, COLUMN_TYPE, COLUMN_COMMENT, COLUMN_KEY, COLLATION_NAME
 FROM `COLUMNS` WHERE `TABLE_NAME` IN (" . $tablesStr . ") AND TABLE_SCHEMA = " . "'" . $baseDb . "'";
        $pdo = $this->linkID->query($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info = [];
        foreach ($result as $key => $val) {
            $info[$key] = $val;
        }
        return $info;
    }
}