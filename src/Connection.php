<?php
/**
 * 数据库连接的抽象类.
 * User: Sanit <huangsinan@zsdx.cn>
 * Date: 2017/3/13
 * Time: 14:01
 */

namespace DatabaseCompare;

use PDO;
use PDOStatement;

abstract class Connection
{
    /**
     * PDOStatement PDO操作实例
     */
    protected $PDOStatement;

    /**
     * 查询返回结果默认为数组
     * @var string
     */
    protected $resultSetType = 'array';

    /**
     * 数据库连接ID
     * @var array
     */
    protected $links = [];

    /**
     * PDO 当前连接ID
     * @var $linkID
     */
    protected $linkID;

    /**
     * 数据库连接参数配置
     * @var array
     */
    protected $config = [
        // 数据库类型
        'type'           => '',
        // 服务器地址
        'hostname'       => '',
        // 数据库名
        'database'       => '',
        // 用户名
        'username'       => '',
        // 密码
        'password'       => '',
        // 端口
        'hostport'       => '',
        // 连接dsn
        'dsn'            => '',
        // 数据库连接参数
        'params'         => [],
        // 数据库编码默认采用utf8
        'charset'        => 'utf8',
        // 数据库表前缀
        'prefix'         => '',
    ];

    /**
     * PDO连接参数
     * @var array
     */
    protected $params = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    public function __construct(array $config = [])
    {
        if(!empty($config)){
            $this->config = array_merge($this->config,$config);
        }
    }

    abstract protected function parseDsn($config);

    abstract public function getTables($dbName);

    abstract public function getTableStructure($tableName);

    /**
     * 连接数据库
     * @param array $config
     * @param int $linkNum
     * @param bool $autoConnection
     * @return mixed
     */
    public function connect(array $config = [], $linkNum = 0, $autoConnection = false)
    {
        if (!isset($this->links[$linkNum])) {
            if (!$config) {
                $config = $this->config;
            } else {
                $config = array_merge($this->config, $config);
            }
            // 连接参数
            if (isset($config['params']) && is_array($config['params'])) {
                $params = $config['params'] + $this->params;
            } else {
                $params = $this->params;
            }
            // 记录当前字段属性大小写设置
            $this->attrCase = $params[PDO::ATTR_CASE];
            // 记录数据集返回类型
            if (isset($config['resultset_type'])) {
                $this->resultSetType = $config['resultset_type'];
            }
            try {
                if (empty($config['dsn'])) {
                    $config['dsn'] = $this->parseDsn($config);
                }
                if ($config['debug']) {
                    $startTime = microtime(true);
                }
                $this->links[$linkNum] = new PDO($config['dsn'], $config['username'], $config['password'], $params);
            } catch (\PDOException $e) {
                if ($autoConnection) {
                    return $this->connect($autoConnection, $linkNum);
                } else {
                    throw $e;
                }
            }
        }
        return $this->links[$linkNum];
    }

    /**
     * 获取数据库的配置参数
     * @access public
     * @param string $config 配置名称
     * @return mixed
     */
    public function getConfig($config = '')
    {
        return $config ? $this->config[$config] : $this->config;
    }

    /**
     * 设置数据库的配置参数
     * @access public
     * @param string|array      $config 配置名称
     * @param mixed             $value 配置值
     * @return void
     */
    public function setConfig($config, $value = '')
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        } else {
            $this->config[$config] = $value;
        }
    }

    public function __destruct()
    {
        // 释放查询
        if ($this->PDOStatement) {
            $this->free();
        }
        // 关闭连接
        $this->close();
    }
    public function free()
    {
        $this->PDOStatement = null;
    }

    public function close()
    {
        $this->linkID = null;
    }

    /**
     * 获取PDO对象
     * @access public
     * @return \PDO|false
     */
    public function getPdo()
    {
        if (!$this->linkID) {
            return false;
        } else {
            return $this->linkID;
        }
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param string        $sql sql指令
     * @param array         $bind 参数绑定
     * @param bool|string   $class 指定返回的数据集对象
     * @return mixed
     * @throws \PDOException
     */
    public function query($sql, $bind = [], $class = false)
    {
        $this->initConnect();
        if (!$this->linkID) {
            return false;
        }
        // 根据参数绑定组装最终的SQL语句$sql

        //释放前次的查询结果
        if (!empty($this->PDOStatement)) {
            $this->free();
        }

        try {
            // 预处理
            $this->PDOStatement = $this->linkID->prepare($sql);
            // 执行查询
            $result = $this->PDOStatement->execute();
            $procedure = in_array(strtolower(substr(trim($sql), 0, 4)), ['call', 'exec']);
            return $this->getResult($class, $procedure);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    /**
     * 执行语句
     * @access public
     * @param string        $sql sql指令
     * @param array         $bind 参数绑定
     * @return int
     * @throws BindParamException
     * @throws PDOException
     */
    public function execute($sql, $bind = [])
    {
        $this->initConnect();
        if (!$this->linkID) {
            return false;
        }
        //释放前次的查询结果
        if (!empty($this->PDOStatement)) {
            $this->free();
        }

        try {
            // 预处理
            $this->PDOStatement = $this->linkID->prepare($sql);
            // 执行语句
            $result = $this->PDOStatement->execute();
            $this->numRows = $this->PDOStatement->rowCount();
            return $this->numRows;
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    /**
     * 初始化数据库连接
     * @access protected
     * @return void
     */
    protected function initConnect()
    {
        if (!$this->linkID) {
            // 默认单数据库
            $this->linkID = $this->connect();
        }
    }
}