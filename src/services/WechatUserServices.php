<?php
namespace Yuyue8\TpWechat\services;

use Yuyue8\TpWechat\model\WechatUserModel;

/**
 * Class WechatUserServices
 * @package Yuyue8\TpWechat\services
 */
class WechatUserServices
{
    /**
     * 获取数据
     *
     * @param array $where
     * @param string $field
     * @return \think\model|null
     */
    public function getInfo(array $where, $field='*')
    {
        /** @var WechatUserModel $wechatUserModel */
        $wechatUserModel = app(WechatUserModel::class);

        return $wechatUserModel->field($field)->where($where)->find();
    }

    /**
     * 修改数据
     *
     * @param array $where
     * @param array $data
     * @return mixed
     */
    public function updateInfo(array $where, array $data)
    {
        /** @var WechatUserModel $wechatUserModel */
        $wechatUserModel = app(WechatUserModel::class);

        return $wechatUserModel->update($data, $where);
    }

    /**
     * 修改数据
     *
     * @param array $where
     * @param array $data
     * @return mixed
     */
    public function createInfo(array $data)
    {
        /** @var WechatUserModel $wechatUserModel */
        $wechatUserModel = app(WechatUserModel::class);

        return $wechatUserModel->create($data);
    }
}