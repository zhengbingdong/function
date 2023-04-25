<?php
// +----------------------------------------------------------------------
// | 整理全网最全PHP常用开发函数
// +----------------------------------------------------------------------
// | Author: zhengbingdong <1529903642@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace zhengbingdong\tools\func;

/**
 * Url工具
 *
 * Class UrlUtils
 * @package app\common\utils
 */
class UrlUtils
{
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
}