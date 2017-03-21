# DatabaseCompare
比较两个或者两个以上的数据库
</br>
目前对于两个以上的数据库的比较还没完善

# 用法
composer require "sanit/databasecompare";

然后再在config里面配置好你的数据库信息,然后就能用了,目前只能简单的比较两张表结构是否相同;
具体用法请看index.php

# 结果说明


````
{
    "result": {
        "table_result": {//两个数据库表的比较
            "diff": {//第一张表与第二张表不同的表(差集)
            },
            "intersect": {//第一张表与第二张相同的表(交集)
            }
        },
        "table_structure_result": {//相同的名字的表的表结构比较
            "table1": "not equal",//不相同的表结构
            "table2": "equal",//相同的表结构
            "table3": "equal"
        }
    }
}
````