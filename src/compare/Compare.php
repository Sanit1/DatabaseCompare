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
        if(is_array($config)){
            $this->config = $config;
        }
    }

    public function compareResult()
    {
        $result = $this->compareTables();
        return [
            'result'=>$result
        ];
    }

    /**
     * 比较两个或两个以上数据库的表
     */
    protected function compareTables()
    {
        if(empty($this->config)){
            throw new \Exception('Can not connect to database without database config');
        }
        $databaseConfig = $this->config;
        $tables=[];$tableStructures=[];
        foreach ($databaseConfig as $db){
            $mysql = new MySQLConnector($db);
            $tables[] = $mysql->getTables();
        }
        $tableResult = $this->compareTablesArray($tables);
        //对比下相同表有不一样的字段
        if(!empty($tableResult['intersect'])){
            foreach ($databaseConfig as $key=>$db){
                $mysql = new MySQLConnector($db);
                foreach ($tableResult['intersect'] as $intersect){
                    $tableStructures[$key][$intersect] = $mysql->getTableStructure($intersect);
                }
            }
        }
        $tableStructuresResult = $this->compareTablesStructure($tableStructures);
        return [
            'table_result'=>$tableResult,
            'table_structure_result'=>$tableStructuresResult
        ];
    }

    /**
     * 比较筛选出来数据库的
     */
    protected function compareTablesArray($tables = [])
    {
        $diff = [];
        $intersect = [];
        if(!empty($tables)){
            $count = count($tables);
            if($count == 2){
                $diff = array_diff($tables[0],$tables[1]);
                $intersect = array_intersect($tables[0],$tables[1]);
            } else{
                for ($i=1;$i<=$count-1;$i++){
                    $diff[] = array_diff($tables[0],$tables[$i]);
                    $intersect[] = array_intersect($tables[0],$tables[$i]);
                }
            }
        }
        return [
            'diff' => $diff,//第一个数据库里面的有的,其它数据库里面没有的
            'intersect' =>$intersect//第一个和其它数据库公有的
        ];
    }

    protected function compareTablesStructure($tableStructures = [])
    {
        $diff = [];
        if(!empty($tableStructures)){
            $count = count($tableStructures);
            if($count == 2){
                foreach ($tableStructures[0] as $key => $tableStructure){
                    if(json_encode($tableStructures[0][$key])==json_encode($tableStructures[1][$key])){
                        $diff[$key]='equal';
                    }else {
                        $diff[$key]='not equal';
                    }
                }
            }else{

            }
        }
        return $diff;
    }
}