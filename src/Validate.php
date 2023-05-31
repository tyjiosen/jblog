<?php
/*
* @Descripttion: 常用验证类
* @Author: jiosen <4631458@qq.com>
* @Date: 2023-05-31 14:54:32
*/
namespace Jiosen\Lib;

class Validate
{
    /**
     * 验证是否有效IP
     * @param string $value 验证值
     * @param mixed $rule  验证规则 ipv4 ipv6
     * @return bool
     */
    static public function ip($value, $rule = 'ipv4')
    {
        if (!in_array($rule, ['ipv4', 'ipv6'])) {
            $rule = 'ipv4';
        }

        return self::filter($value, [FILTER_VALIDATE_IP, 'ipv6' == $rule ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4]);
    }

    /**
     * 验证是否有效email
     * @param string $value 验证值
     * @return bool
     */
    static public function email($value)
    {
        return self::filter($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 验证是否有效URL
     * @param string $value 验证值
     * @return bool
     */
    static public function url($value)
    {
        return self::filter($value, FILTER_VALIDATE_URL);
    }

    /**
     * 验证是否纯字母
     * @param string $value 验证值
     * @return bool
     */
    static public function alpha($value)
    {
        return self::regex($value,'/^[A-Za-z]+$/');
    }

    /**
     * 验证是否字母+数字
     * @param string $value 验证值
     * @return bool
     */
    static public function alphaNum($value)
    {
        return self::regex($value,'/^[A-Za-z0-9]+$/');
    }

    /**
     * 验证是否纯中文
     * @param string $value 验证值
     * @return bool
     */
    static public function chs($value)
    {
        return self::regex($value,'/^[\x{4e00}-\x{9fa5}\x{9fa6}-\x{9fef}\x{3400}-\x{4db5}\x{20000}-\x{2ebe0}]+$/u');
    }

    /**
     * 验证是手机号
     * @param string $value 验证值
     * @return bool
     */
    static public function mobile($value)
    {
        return self::regex($value,'/^(13[0-9]|14[01456879]|15[0-35-9]|16[2567]|17[0-8]|18[0-9]|19[0-35-9])\d{8}$/');
    }
    
    /**
     * 验证是否身份证号码
     * @param string $value 验证值
     * @return bool
     */
    static public function idCard($value)
    {
        return self::regex($value,'/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/') && self::getIdCardLastNum($value);
    }

    /**
     * 验证身份证号码最后一位
     * @param string $value 验证值
     * @return bool
     */
    static public function getIdCardLastNum($idCard)
	{
        $idCard = str_split($idCard);

		$check_sum = 0;

	    for ($i = 0; $i < 17; $i++) {

	        $check_sum += $idCard[$i] * ((1 << (17 - $i)) % 11);
	    }

	    $check_code = (12 - $check_sum % 11) % 11;

	    $check_code = $check_code == 10 ? 'X' : strval($check_code);

	    return $check_code == $idCard[17];
	}

    /**
     * 正则验证
     * @param string $value 验证值
     * @param string $rule 正则
     * @return bool
     */
    static public function regex($value,$rule)
    {
        if (is_string($rule) && 0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }

        return is_scalar($value) && 1 === preg_match($rule, (string) $value);
    }

    /**
     * 使用filter_var方式验证
     * @param mixed $value 验证值
     * @param mixed $rule  验证规则
     * @return bool
     */
    static private function filter($value, $rule)
    {
        if (is_string($rule) && strpos($rule, ',')) {
            [$rule, $param] = explode(',', $rule);
        } elseif (is_array($rule)) {
            $param = $rule[1] ?? 0;
            $rule  = $rule[0];
        } else {
            $param = 0;
        }

        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
    }

    static public function __callStatic($name, $arguments)
    {
        //暂时先返回false
        //todo
        return false;
    }
}
