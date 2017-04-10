<?php
/**
 * 比较数据库的主逻辑.
 * User: Sanit <huangsinan@zsdx.cn>
 * Date: 2017/3/21
 * Time: 9:59
 */

namespace DatabaseCompare\Compare;

use DatabaseCompare\Connector\MySQLConnector;

class Compare
{
    /**
     * 数据库连接配置信息
     */
    protected $config;

    public function __construct($config)
    {
        if (is_array($config)) {
            $this->config = $config;
        }
    }

    public function compareResult()
    {
        $result = $this->compareTables();
        return [
            'result' => $result
        ];
    }

    /**
     * 比较两个或两个以上数据库的表
     */
    protected function compareTables()
    {
        if (empty($this->config)) {
            throw new \Exception('Can not connect to database without database config');
        }
        $databaseConfig = $this->config;
        $tables = [];
        $tableStructures = [];
        foreach ($databaseConfig as $db) {
            $mysql = new MySQLConnector($db);
            $tables[] = $mysql->getTables();
        }
        $tableResult = $this->compareTablesArray($tables);
        //对比下相同表有不一样的字段
        if (!empty($tableResult['intersect'])) {
            foreach ($databaseConfig as $key => $db) {
                $baseDb = $db['database'];
                $db['database'] = 'information_schema';
                $mysql = new MySQLConnector($db);
                $tableStructures[$baseDb] = $mysql->getTableStructure($tableResult['intersect'], $baseDb);
            }
        }
        $tableStructuresResult = $this->compareTablesStructure($tableStructures, $databaseConfig[0]['database'], $databaseConfig[1]['database']);
        return [
            'table_result' => $tableResult,
            'table_structure_result' => $tableStructuresResult
        ];
    }

    /**
     * 比较筛选出来数据库的
     */
    protected function compareTablesArray($tables = [])
    {
        $intersect = [];
        $first_diff = $second_diff = [];
        if (!empty($tables)) {
            $first_diff = array_diff($tables[0], $tables[1]);
            $second_diff = array_diff($tables[1], $tables[0]);
            $intersect = array_intersect($tables[0], $tables[1]);
        }
        return [
            'diff' => [
                'first_diff' => $first_diff,//第一个数据库里面的有的,第二个数据库里面没有的
                'second_diff' => $second_diff////第二个数据库里面的有的,第一个数据库里面没有的
            ],
            'intersect' => $intersect//两个数据库公有的
        ];
    }

    protected function compareTablesStructure($tableStructures = [], $firstDb, $secondDb)
    {
        $diff = [
            'both' => [],
            $firstDb => [],
            $secondDb => []
        ];
        if (!empty($tableStructures)) {
            $firstDbTemp = $secondDbTemp = [];
            foreach ($tableStructures as $key => $tableStructure) {
                if ($firstDb == $key) {
                    foreach ($tableStructure as $structure) {
                        $firstDbTemp[$structure['TABLE_NAME']][$structure['COLUMN_NAME']] = [
                            'columnDefault' => $structure['COLUMN_DEFAULT'],
                            'columnType' => $structure['COLUMN_TYPE'],
                            'isNullable' => $structure['IS_NULLABLE'],
                            'columnKey' => $structure['COLUMN_KEY'],
                            'collationName' => $structure['COLLATION_NAME'],
                            'columnComment' => $structure['COLUMN_COMMENT']
                        ];
                    }
                }
                if ($secondDb == $key) {
                    foreach ($tableStructure as $structure) {
                        $secondDbTemp[$structure['TABLE_NAME']][$structure['COLUMN_NAME']] = [
                            'columnDefault' => $structure['COLUMN_DEFAULT'],
                            'columnType' => $structure['COLUMN_TYPE'],
                            'isNullable' => $structure['IS_NULLABLE'],
                            'columnKey' => $structure['COLUMN_KEY'],
                            'collationName' => $structure['COLLATION_NAME'],
                            'columnComment' => $structure['COLUMN_COMMENT']
                        ];
                    }
                }
            }
            //第一个数据库跟第二个数据库相比
            if (!empty($firstDbTemp) && !empty($secondDbTemp)) {
                foreach ($firstDbTemp as $table => $column) {
                    foreach ($column as $col => $value) {
                        if (isset($secondDbTemp[$table][$col])) {
                            //说明第一张表跟第二张表都有这个字段
                            if ($value['columnType'] != $secondDbTemp[$table][$col]['columnType']) {
                                $diff['both'][$table][$col] = '数据库:' . $firstDb . '为:' . $value['columnType'] . PHP_EOL . '数据库:' . $secondDb . '为:' . $secondDbTemp[$table][$col]['columnType'];
                            } else if ($value['columnDefault'] != $secondDbTemp[$table][$col]['columnDefault']) {
                                $diff['both'][$table][$col] = '数据库:' . $firstDb . '字段默认值为:' . $value['columnDefault'] . PHP_EOL . '数据库:' . $secondDb . '字段默认值为:' . $secondDbTemp[$table][$col]['columnDefault'];
                            } else if ($value['isNullable'] != $secondDbTemp[$table][$col]['isNullable']) {
                                $diff['both'][$table][$col] = '数据库:' . $firstDb . '字段是否为空:' . $value['isNullable'] . PHP_EOL . '数据库:' . $secondDb . '字段是否为空:' . $secondDbTemp[$table][$col]['isNullable'];
                            } else if ($value['columnKey'] != $secondDbTemp[$table][$col]['columnKey']) {
                                $diff['both'][$table][$col] = '数据库:' . $firstDb . '字段是主键外键属性:' . $value['columnKey'] . PHP_EOL . '数据库:' . $secondDb . '字段是主键外键属性:' . $secondDbTemp[$table][$col]['columnKey'];
                            } else if ($value['collationName'] != $secondDbTemp[$table][$col]['collationName']) {
                                $diff['both'][$table][$col] = '数据库:' . $firstDb . '字段的编码为:' . $value['collationName'] . PHP_EOL . '数据库:' . $secondDb . '字段的编码为:' . $secondDbTemp[$table][$col]['collationName'];
                            } else {
                                $diff['both'][$table][$col] = 1;
                            }
                        } else {
                            $diff[$firstDb][$table][$col] = '数据库:' . $firstDb . '独有字段' . $col . ':' . json_encode($value);
                        }
                    }
                }
                //第二个数据库跟第一个数据库相比
                foreach ($secondDbTemp as $table => $column) {
                    foreach ($column as $col => $value) {
                        if (isset($firstDbTemp[$table][$col])) {
                            continue;
                        } else {
                            $diff[$secondDb][$table][$col] = '数据库:' . $secondDb . '独有字段' . $col . ':' . json_encode($value);
                        }
                    }
                }
            }
        }
        return $diff;
    }
}