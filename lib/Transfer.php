<?php

namespace MasJPay;

class Transfer extends ApiResource
{
    /**
     * @param $id
     * @param null $options
     * @return mixed
     * @throws Error\Api
     * @throws Error\InvalidRequest
     */
    public static function retrieve($id, $options = null)
    {
        return self::_retrieve($id, $options);
    }

    /** 查询 transfer 对象列表
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return array An array of Transfer.
     */
    public static function all($params = null, $options = null)
    {
        return self::_all($params, $options);
    }

    /** 创建 transfer 对象
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return Transfer The created transfer.
     */
    public static function create($params = null, $options = null)
    {
        return self::_create($params, $options);
    }
}
