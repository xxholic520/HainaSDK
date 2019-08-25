<?php


namespace Sammy1992\Haina\Core\Support;


use Sammy1992\Haina\Core\Exceptions\Exception;

class AES
{
    /**
     * @param $text
     * @param $aeskey
     * @return string
     * @throws Exception
     */
    public static function encrypt($text, $aeskey)
    {
        try {
            $aeskey = base64_decode($aeskey . "=");
            //获得16位随机字符串，填充到明文之前
            $randomStr = self::getRandomStr();
            $collector = $randomStr . pack("N", strlen($text)) . $text;
            // 网络字节序
//            $size   = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv     = substr($aeskey, 0, 16);
            // 使用自定义的填充方式对明文进行补位填充
            $encoded = PKCS7Encoder::encode($collector);
            mcrypt_generic_init($module, $aeskey, $iv);
            // 加密
            $encrypted = mcrypt_generic($module, $encoded);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);

            // 使用BASE64对加密后的字符串进行编码
            return base64_encode($encrypted);
        } catch (\Exception $e) {
            throw new Exception(ErrorCode::$IllegalAesKey);
        }
    }

    /**
     * @param $encrypted
     * @param $aeskey
     * @return bool|string
     * @throws Exception
     */
    public static function decrypt($encrypted, $aeskey)
    {
        try {
            $aeskey = base64_decode($aeskey . "=");
            //使用BASE64对需要解密的字符串进行解码
            $ciphertext_dec = base64_decode($encrypted);
            $module         = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv             = substr($aeskey, 0, 16);
            mcrypt_generic_init($module, $aeskey, $iv);

            // 解密
            $decrypted = mdecrypt_generic($module, $ciphertext_dec);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (\Exception $e) {
            throw new Exception(ErrorCode::$DecryptAESError);
        }

        try {
            // 去除补位字符
            $result = PKCS7Encoder::decode($decrypted);
            // 去除16位随机字符串，网络字节序
            if (strlen($result) < 16) return '';
            $content  = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $length   = $len_list[1];
            $text     = substr($content, 4, $length);
            return $text;
        } catch (\Exception $e) {
            throw new Exception(ErrorCode::$IllegalBuffer);
        }
    }

    /**
     * @return string
     */
    public static function getRandomStr()
    {

        $str     = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max     = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
}

/**
 * error code 说明.
 * <ul>
 *    <li>-40001: 签名验证错误</li>
 *    <li>-40002: sha加密生成签名失败</li>
 *    <li>-40003: encodingAesKey 非法</li>
 *    <li>-40004: aes 加密失败</li>
 *    <li>-40005: aes 解密失败</li>
 *    <li>-40006: 解密后得到的buffer非法</li>
 *    <li>-40007: base64加密失败</li>
 *    <li>-40008: base64解密失败</li>
 * </ul>
 */
class ErrorCode
{
    public static $OK                     = 0;
    public static $ValidateSignatureError = -40001;
    public static $ComputeSignatureError  = -40002;
    public static $IllegalAesKey          = -40003;
    public static $EncryptAESError        = -40004;
    public static $DecryptAESError        = -40005;
    public static $IllegalBuffer          = -40006;
    public static $EncodeBase64Error      = -40007;
    public static $DecodeBase64Error      = -40008;
}

class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对需要加密的明文进行填充补位
     *
     * @param string $text 需要进行填充补位操作的明文
     * @return string 补齐明文字符串
     */
    public static function encode($text)
    {
        $text_length = strlen($text);
        // 计算需要填充的位数
        $amount_to_pad = self::$block_size - ($text_length % self::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = self::$block_size;
        }
        // 获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp     = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     *
     * @param string $text 解密后的明文
     * @return string 删除填充补位后的明文
     */
    public static function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}