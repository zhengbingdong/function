#  整理全网最全PHP常用开发函数

#### 安装方式


```shell
$ composer require zhengbingdong/php-tools-function
```

更多技术分享请更关注公众号：程序猿的栖息地

![程序猿的栖息地](https://gitee.com/zhengbingdong/php-tools-function/raw/master/qrcode.jpg)

#### 数组工具

##### 1.转树形HTML结构数据

```php
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

```

##### 2.转树形JSON格式数据

```php
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

```
##### 3.表单多维数据转换

```php
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

```

##### 4.多维数组合并
```php
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

```

#### Url工具

##### 1.转绝对路径

```php
/**
 * 转绝对路径
 * 示例:
 *  转换前: image/20220819/ad06320.jpeg
 *  转换后: http://www.baidu.cn/storage/image/20220819/ad06320.jpeg
 *
 * @param string $url (相对路径)
 * @return string     (绝对路径)
 */
public static function toAbsoluteUrl(string $url = ''): string
{
    if ($url === '' || $url === '/') {
        return $url;
    }

    if (str_starts_with($url, 'http:'.'//') || str_starts_with($url, 'https://')) {
        return $url;
    }

    if (!str_starts_with($url, '/')) {
        $url = '/' . $url;
    }

    if (str_starts_with($url, '/static') || str_starts_with($url, '/temporary')) {
        return request()->domain() . $url;
    }

    $engine = ConfigUtils::get('storage', 'default', 'local');
    if ($engine === 'local') {
        return request()->domain() . $url;
    } else {
        $config = ConfigUtils::get('storage', $engine, []);
        $domain = $config['domain'] ?? '';
        return rtrim($domain, '/') . $url;
    }
}
```

##### 2.转相对路径

```php
/**
 * 转相对路径
 * 示例:
 *  转换前: http://www.baidu.cn/storage/image/20220819/ad06320.jpeg
 *  转换后: image/20220819/ad06320.jpeg
 *
 * @param string $url (绝对路径)
 * @return string     (相对路径)
 */
public static function toRelativeUrl(string $url): string
{
    if (str_starts_with($url, 'http:'.'//') || str_starts_with($url, 'https://')) {
        $url = str_replace('http:'.'//', '', $url);
        $url = str_replace('https://', '', $url);
        $arr = explode('/', $url);
        array_shift($arr);
        return implode('/', $arr);
    }
    return $url;
}
```

##### 3.转本地根路径

```php
/**
 * 转本地根路径
 * 示例:
 *  转换前: storage/image/20220819/ad06320.jpeg
 *  转换后: /www/server/wait/public/storage/image/20220819/ad06320.jpeg
 *
 * @param string $url (相对路径)
 * @return string
 * @author zero
 */
public static function toRoot(string $url): string
{
    if (str_starts_with($url, 'http:'.'//') || str_starts_with($url, 'https://')) {
        $url = self::toRelativeUrl($url);
    }

    $rootPath = public_path() . $url;
    $rootPath = str_replace('\\', '/', $rootPath) ;
    return strval($rootPath);
}
```

##### 4.富文本Src地址提取

```php
/**
 * 富文本Src地址提取
 * 说明: 从富文本中取出所有Src
 *
 * @param string $content
 * @return array
 * @author zero
 */
public static function editorFetchSrc(string $content): array
{
    preg_match_all( '/(src|SRC)=("[^"]*")/i', $content, $match);

    $urls = [];
    foreach ($match[2] as $url) {
        $path = trim($url, '"');
        $urls[] = UrlUtils::toRelativeUrl($path);
    }

    return $urls;
}
```

##### 5.富文本Src转相对地址

```php
/**
 * 富文本Src转相对地址
 * 说明: 富文本内容里的Src转成相对链接
 *
 * @param string $content
 * @return string
 * @author zero
 */
public static function editorRelativeSrc(string $content): string
{
    $engine = ConfigUtils::get('storage', 'default', 'local');
    // 本地路径
    $domain  = request()->domain() . '/';
    $content = str_replace($domain, '', $content);
    // 云端路径
    if ($engine != 'local') {
        $config = ConfigUtils::get('storage', $engine, []);
        $domain = $config['domain'] ?? 'http'.'://';
        $content = str_replace($domain, '', $content);
    }
    return $content;
}
```

##### 6.富文本Src转绝对地址

```php
/**
 * 富文本Src转绝对地址
 * 说明: 富文本内容里的Src转成绝对链接
 *
 * @param string $content
 * @return string
 * @author zero
 */
public static function editorAbsoluteSrc(string $content): string
{
    $engine = ConfigUtils::get('storage', 'default', 'local');
    if ($engine !== 'local') {
        $config = ConfigUtils::get('storage', $engine, []);
        $domain = $config['domain'] ?? 'http'.'://';
    } else {
        $domain = request()->domain() . '/';
    }

    $preg = '/(<.*?src=")(?!http|https)(.*?)(".*?>)/is';
    return preg_replace($preg, "\${1}$domain\${2}\${3}", $content);
}
```
#### 时间工具

##### 1.返回今日开始和结束的时间戳

```php

/**
 * 返回今日开始和结束的时间戳
 *
 * @author zero
 * @return array
 */
public static function today(): array
{
    list($y, $m, $d) = explode('-', date('Y-m-d'));
    return [
        mktime(0, 0, 0, $m, $d, $y),
        mktime(23, 59, 59, $m, $d, $y)
    ];
}
```

##### 2.返回明天开始和结束时间戳

```php
/**
 * 返回明天开始和结束时间戳
 *
 * @author zero
 * @return array
 */
public static function tomorrow(): array
{
    $date = date("Y-m-d",strtotime("+1 day"));
    return [
        strtotime($date.' 00:00:00'),
        strtotime($date.' 23:59:59')
    ];
}
```

##### 3.返回昨日开始和结束的时间戳

```php
/**
 * 返回昨日开始和结束的时间戳
 *
 * @author zero
 * @return array
 */
public static function yesterday(): array
{
    $yesterday = date('d') - 1;
    return [
        mktime(0, 0, 0, date('m'), $yesterday, date('Y')),
        mktime(23, 59, 59, date('m'), $yesterday, date('Y'))
    ];
}
```

##### 4.返回本周开始和结束的时间戳

```php
/**
 * 返回本周开始和结束的时间戳
 *
 * @author zero
 * @return array
 */
public static function week(): array
{
    list($y, $m, $d, $w) = explode('-', date('Y-m-d-w'));
    if($w == 0) $w = 7; //修正周日的问题
    return [
        mktime(0, 0, 0, $m, $d - $w + 1, $y), mktime(23, 59, 59, $m, $d - $w + 7, $y)
    ];
}
```

##### 5.返回上周开始和结束的时间戳

```php
/**
 * 返回上周开始和结束的时间戳
 *
 * @author zero
 * @return array
 */
public static function lastWeek(): array
{
    $timestamp = time();
    return [
        strtotime(date('Y-m-d', strtotime("last week Monday", $timestamp))),
        strtotime(date('Y-m-d', strtotime("last week Sunday", $timestamp))) + 24 * 3600 - 1
    ];
}
```

##### 6.返回本月开始和结束的时间戳

```php
/**
 * 返回本月开始和结束的时间戳
 *
 * @return array
 * @author zero
 */
public static function month(): array
{
    list($y, $m, $t) = explode('-', date('Y-m-t'));
    return [
        mktime(0, 0, 0, $m, 1, $y),
        mktime(23, 59, 59, $m, $t, $y)
    ];
}
```

##### 7.返回今日开始和结束的时间戳

```php
/**
 * 返回上个月开始和结束的时间戳
 *
 * @author zero
 * @return array
 */
public static function lastMonth(): array
{
    $y = date('Y');
    $m = date('m');
    $begin = mktime(0, 0, 0, $m - 1, 1, $y);
    $end = mktime(23, 59, 59, $m - 1, date('t', $begin), $y);

    return [$begin, $end];
}
```

##### 8.返回今年开始和结束的时间戳

```php
/**
 * 返回今年开始和结束的时间戳
 *
 * @author zero
 * @return array
 */
public static function year(): array
{
    $y = date('Y');
    return [
        mktime(0, 0, 0, 1, 1, $y),
        mktime(23, 59, 59, 12, 31, $y)
    ];
}
```

##### 9.返回去年开始和结束的时间戳

```php
/**
 * 返回去年开始和结束的时间戳
 *
 * @author zero
 * @return array
 */
public static function lastYear(): array
{
    $year = date('Y') - 1;
    return [
        mktime(0, 0, 0, 1, 1, $year),
        mktime(23, 59, 59, 12, 31, $year)
    ];
}
```

##### 10.获取几天前零点到现在/昨日结束的时间戳

```php
/**
 * 获取几天前零点到现在/昨日结束的时间戳
 *
 * @author zero
 * @param int $day 天数
 * @param bool $now 返回现在或者昨天结束时间戳
 * @return array
 */
public static function dayToNow(int $day = 1, bool $now = true): array
{
    $end = time();
    if (!$now) {
        list($foo, $end) = self::yesterday();
    }

    unset($foo);
    return [
        mktime(0, 0, 0, date('m'), date('d') - $day, date('Y')),
        $end
    ];
}
```

##### 11.返回几天前的时间戳

```php
 /**
 * 返回几天前的时间戳
 *
 * @author zero
 * @param int $day
 * @return int
 */
public static function daysAgo(int $day = 1): int
{
    $nowTime = time();
    return $nowTime - self::daysToSecond($day);
}
```

##### 12.返回几天后的时间戳

```php
/**
 * 返回几天后的时间戳
 *
 * @author zero
 * @param int $day
 * @return int
 */
public static function daysAfter(int $day = 1): int
{
    $nowTime = time();
    return $nowTime + self::daysToSecond($day);
}
```

##### 13.天数转换成秒数

```php
/**
 * 天数转换成秒数
 *
 * @author zero
 * @param int $day
 * @return int
 */
public static function daysToSecond(int $day = 1): int
{
    return intval($day * 86400);
}
```

##### 14.周数转换成秒数

```php
/**
 * 周数转换成秒数
 *
 * @author zero
 * @param int $week
 * @return int
 */
public static function weekToSecond(int $week = 1): int
{
    return intval(self::daysToSecond() * 7 * $week);
}
```

##### 15.最近N天的日期

```php
 /**
 * 最近N天的日期
 *
 * @author zero
 * @param int $day
 * @return array
 */
public static function nearToDate(int $day = 7): array
{
    $time = time();
    $date = [];
    for ($i=1; $i<=$day; $i++){
        $date[$i-1] = date('Y-m-d' ,strtotime( '+' . ($i - $day) .' days', $time));
    }
    return $date;
}
```

##### 16.当前毫秒数

```php
/**
 * 当前毫秒数
 *
 * @author zero
 * @return float
 */
public static function millisecond(): float
{
    list($mse, $sec) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($mse) + floatval($sec)) * 1000);
}
```

##### 17.返回今天是周几

```php
/**
 * 返回今天是周几
 *
 * @author zero
 * @return mixed
 */
public static function dayWeek(): string
{
    $week = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
    return $week[date('w')];
}
```

##### 18.大写月份

```php
/**
 * 大写月份
 *
 * @author zero
 * @param int $month
 * @return string
 */
public static function capMonth(int $month=0): string
{
    $month = $month > 0 ? $month : intval(date('m'));
    return match ($month) {
        1 => '一月',
        2 => '二月',
        3 => '三月',
        4 => '四月',
        5 => '五月',
        6 => '六月',
        7 => '七月',
        8 => '八月',
        9 => '九月',
        10 => '十月',
        11 => '十一月',
        12 => '十二月',
        default => '未知月份',
    };
}
```

##### 19.格式化时间戳

```php
/**
 * 格式化时间戳
 *
 * @param int $time
 * @return string
 */
public static function formatTime(int $time): string
{
    if ($time > 86400) {
        $days = intval($time / 86400);
        $hour = intval(($time - ($days * 86400)) / 3600);
        return $days.'天'.$hour.'时';
    } else {
        $hour   = intval($time / 3600);
        $minute = intval(($time - ($hour * 3600)) / 60);
        return $hour.'时'.$minute.'分';
    }
```