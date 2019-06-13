<?php

namespace JPay\Util;

use JPay\JPayObject;
use stdClass;

abstract class Util
{
    /**
     * Whether the provided array (or other) is a list rather than a dictionary.
     *
     * @param array|mixed $array
     * @return boolean True if the given object is a list.
     */
    public static function isList($array)
    {
        if (!is_array($array)) {
            return false;
        }

        // TODO: generally incorrect, but it's correct given JPay's response
        foreach (array_keys($array) as $k) {
            if (!is_numeric($k)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Recursively converts the PHP JPay object to an array.
     *
     * @param array $values The PHP JPay object to convert.
     * @param bool
     * @return array
     */
    public static function convertJPayObjectToArray($values, $keep_object = false)
    {
        $results = [];
        foreach ($values as $k => $v) {
            // FIXME: this is an encapsulation violation
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof JPayObject) {
                $results[$k] = $keep_object ? $v->__toStdObject(true) : $v->__toArray(true);
            } elseif (is_array($v)) {
                $results[$k] = self::convertJPayObjectToArray($v, $keep_object);
            } else {
                $results[$k] = $v;
            }
        }
        return $results;
    }

    /**
     * Recursively converts the PHP JPay object to an stdObject.
     *
     * @param array $values The PHP JPay object to convert.
     * @return array
     */
    public static function convertJPayObjectToStdObject($values)
    {
        $results = new stdClass;
        foreach ($values as $k => $v) {
            // FIXME: this is an encapsulation violation
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof JPayObject) {
                $results->$k = $v->__toStdObject(true);
            } elseif (is_array($v)) {
                $results->$k = self::convertJPayObjectToArray($v, true);
            } else {
                $results->$k = $v;
            }
        }
        return $results;
    }

    /**
     * Converts a response from the JPay API to the corresponding PHP object.
     *
     * @param stdObject $resp The response from the JPay API.
     * @param array $opts
     * @return JPayObject|array
     */
    public static function convertToJPayObject($resp, $opts)
    {
        $types = [
            'agreement' => \JPay\Agreement::class,
            'balance_bonus' => \JPay\BalanceBonus::class,
            'balance_settlement' => \JPay\BalanceSettlements::class,
            'balance_transaction' => \JPay\BalanceTransaction::class,
            'balance_transfer' => \JPay\BalanceTransfer::class,
            'batch_refund' => \JPay\BatchRefund::class,
            'batch_transfer' => \JPay\BatchTransfer::class,
            'batch_withdrawal' => \JPay\BatchWithdrawal::class,
            'channel' => \JPay\Channel::class,
            'charge' => \JPay\Charge::class,
            'coupon' => \JPay\Coupon::class,
            'coupon_template' => \JPay\CouponTemplate::class,
            'customs' => \JPay\Customs::class,
            'event' => \JPay\Event::class,
            'list' => \JPay\Collection::class,
            'order' => \JPay\Order::class,
            'profit_transaction' => \JPay\ProfitTransaction::class,
            'recharge' => \JPay\Recharge::class,
            'red_envelope' => \JPay\RedEnvelope::class,
            'refund' => \JPay\Refund::class,
            'royalty' => \JPay\Royalty::class,
            'royalty_settlement' => \JPay\RoyaltySettlement::class,
            'royalty_template' => \JPay\RoyaltyTemplate::class,
            'royalty_transaction' => \JPay\RoyaltyTransaction::class,
            'settle_account' => \JPay\SettleAccount::class,
            'split_profit' => \JPay\SplitProfit::class,
            'split_receiver' => \JPay\SplitReceiver::class,
            'sub_app' => \JPay\SubApp::class,
            'sub_bank' => \JPay\SubBank::class,
            'transfer' => \JPay\Transfer::class,
            'user' => \JPay\User::class,
            'withdrawal' => \JPay\Withdrawal::class,
        ];
        if (self::isList($resp)) {
            $mapped = [];
            foreach ($resp as $i) {
                array_push($mapped, self::convertToJPayObject($i, $opts));
            }
            return $mapped;
        } elseif (is_object($resp)) {
            if (isset($resp->object)
                && is_string($resp->object)
                && isset($types[$resp->object])) {
                    $class = $types[$resp->object];
            } else {
                $class = 'JPay\\JPayObject';
            }
            return $class::constructFrom($resp, $opts);
        } else {
            return $resp;
        }
    }

    /**
     * Get the request headers
     * @return array An hash map of request headers.
     */
    public static function getRequestHeaders()
    {
        if (function_exists('getallheaders')) {
            $headers = [];
            foreach (getallheaders() as $name => $value) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))))] = $value;
            }
            return $headers;
        }
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @return string|mixed The UTF8-encoded string, or the object passed in if
     *    it wasn't a string.
     */
    public static function utf8($value)
    {
        if (is_string($value)
            && mb_detect_encoding($value, "UTF-8", true) != "UTF-8"
        ) {
            return utf8_encode($value);
        } else {
            return $value;
        }
    }
}
