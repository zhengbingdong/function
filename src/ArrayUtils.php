<?php
// +----------------------------------------------------------------------
// | 整理全网最全PHP常用开发函数
// +----------------------------------------------------------------------
// | Author: zhengbingdong <1529903642@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace zhengbingdong\tools\func;

/**
 * 数组工具
 *
 * Class ArrayService
 * @package app\common\service
 */
class ArrayUtils
{
    /**
     * 转树形HTML结构数据
     *
     * @param $data         (数据集)
     * @param int $pid      (父级ID)
     * @param string $field (父级字段名)
     * @param string $pk    (主键)
     * @param string $html  (层级文本标识)
     * @param int $level    (当前所在层级)
     * @param bool $clear   (是否清空)
     * @return array
     * @author zero
     */
    public static function toTreeHtml($data, int $pid=0, string $field='pid', string $pk='id', string $html='|--', int $level=0, bool $clear=true): array
    {
        static $list = [];
        if ($clear) $list = [];

        foreach ($data as $key => $value) {
            if ($value[$field] == $pid) {
                $value['html'] = str_repeat($html, $level);
                $list[] = $value;
                unset($data[$key]);
                self::toTreeHtml($data, $value[$pk], $field, $pk, $html, $level + 1, false);
            }
        }

        return $list;
    }

    /**
     * 转树形JSON格式数据
     *
     * @param array $data   (数据集)
     * @param int $pid      (父级ID)
     * @param string $field (字段名称)
     * @param string $pk    (主键)
     * @return array
     * @author zero
     */
    public static function toTreeJson(array $data, int $pid=0, string $field='pid', string $pk='id'): array
    {
        $tree = array();
        foreach ($data as $value) {
            if ($value[$field] == $pid) {
                $value['children'] = self::toTreeJson($data, $value[$pk], $field, $pk);
                $tree[] = $value;
            }
        }
        return $tree;
    }

    /**
     * 表单多维数据转换
     * 例：
     * 转换前：{"x":0,"a":[1,2,3],"b":[11,22,33],"c":[111,222,3333,444],"d":[1111,2222,3333]}
     * 转换为：[{"a":1,"b":11,"c":111,"d":1111},{"a":2,"b":22,"c":222,"d":2222},{"a":3,"b":33,"c":3333,"d":3333}]
     *
     * @param $arr array (表单二维数组)
     * @param $fill bool (fill为false,返回数据长度取最短,反之取最长,空值自动补充)
     * @return array
     * @author zero
     */
    public static function formToLinear(array $arr, bool $fill = false): array
    {
        $keys = [];
        $count = $fill ? 0 : PHP_INT_MAX;
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $keys[] = $k;
                $count = $fill ? max($count, count($v)) : min($count, count($v));
            }
        }
        if (empty($keys)) {
            return [];
        }
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            foreach ($keys as $v) {
                $data[$i][$v] = $arr[$v][$i] ?? null;
            }
        }
        return $data;
    }

    /**
     * 多维数组合并
     *
     * @param $array1 (数组1)
     * @param $array2 (数组2)
     * @return array
     * @author zero
     */
    public static function arrayMergeMultiple($array1, $array2): array
    {
        $merge = $array1 + $array2;
        $data = [];
        foreach ($merge as $key => $val) {
            if (
                isset($array1[$key])
                && is_array($array1[$key])
                && isset($array2[$key])
                && is_array($array2[$key])
            ) {
                $data[$key] = self::arrayMergeMultiple($array1[$key], $array2[$key]);
            } else {
                $data[$key] = $array2[$key] ?? $array1[$key];
            }
        }
        return $data;
    }
}