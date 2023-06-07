<?php
namespace Yuyue8\TpWechat\utils;

use FilesystemIterator;

/**
 * 文件管理
 * Class File
 * @package Yuyue8\TpWechat\utils
 */
class FileUtil
{

    /**
     * 获取目录中pem证书列表，以文件名为key，绝对路径为值
     *
     * @param string $dirname
     * @return void
     */
    public function getCertsList(string $dirname)
    {
        if (!is_dir($dirname)) {
            return [];
        }

        $items = new FilesystemIterator($dirname);

        $array = [];
        foreach ($items as $item) {
            $path = $item->getPathname();

            if ($item->isFile() && substr($path, -4) === '.pem') {
                $array[$item->getBasename('.pem')] = $path;
            }
        }

        return $array;
    }

    /**
     * 删除文件夹
     * @param $dirname
     * @return bool
     */
    public function rmdir(string $dirname, bool $is_rm_dir = false)
    {
        if (!is_dir($dirname)) {
            return false;
        }

        $items = new FilesystemIterator($dirname);

        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                $this->rmdir($item->getPathname());
            } else {
                $this->unlink($item->getPathname());
            }
        }

        if($is_rm_dir){
            @rmdir($dirname);
        }

        return true;
    }

    /**
     * 判断文件是否存在后，删除
     * @param string $path
     * @return bool
     */
    public function unlink(string $path): bool
    {
        try {
            return is_file($path) && unlink($path);
        } catch (\Exception $e) {
            return false;
        }
    }
}