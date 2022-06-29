<?php

declare(strict_types=1);
namespace App\Service;

use App\Contract\AbstractService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Context\Context;
use Hyperf\Utils\Coroutine;
use Psr\SimpleCache\CacheInterface;
use Wayhood\HyperfAction\Contract\TokenInterface;

class TokenService extends AbstractService implements TokenInterface
{
    #[Inject]
    public CacheInterface $cache;

    private $token_preifx = 'c:token:';

    private $user_token_prefix = 'c:user_token:';

    private $timeout = 86400 * 30;

    private $update_interval = 86400;

    private $pk_name = 'school_id';

    private $_randomFile;

    /**
     * 验证token 0 token失效 -1 被踢  1正常.
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @return int
     */
    public function verify(string $token)
    {
        $value = $this->getRaw($token);
        if (is_array($value)) {
            Context::set($token, $value);
        }

        if (empty($value)) {
            return 0;
        }
        if (is_array($value)) {
            return 1;
        }
        if (intval($value) == -1) {
            return -1;
        }
        return 1;
    }

    public function has(string $token)
    {
        // TODO: Implement has() method.
    }

    public function generator(array $value)
    {
        $this->logger->debug('========> token generator');
        $this->deleteUserToken($value[$this->pk_name]);
        $token = $this->generateRandomString(64);

        $newValue = [
            'update' => time(),
            'value' => $value,
        ];

        $timeout = $this->getTimeout();
        $this->cache->set($this->tokenKey($token), $newValue, $timeout);

        if (isset($value[$this->pk_name])) {
            $this->cache->set($this->userTokenKey($value[$this->pk_name]), $token, $timeout);
        }
        return $token;
    }

    /**
     * 删除token.
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function del(string $token)
    {
        if ($this->cache->delete($this->tokenKey($token))) {
            return true;
        }
        return false;
    }

    /**
     * 更新token.
     * @param mixed $value
     * @return string
     */
    public function set(string $token)
    {
        $value = $this->getInContext($token);
        if ((time() - $value['update']) > $this->update_interval) {
            $value['update'] = time();
            Context::set($token, $value);
            $timeout = $this->getTimeout();
            $this->cache->set($this->tokenKey($token), $value, $timeout);
            $this->cache->set($this->userTokenKey($value['value'][$this->pk_name]), $token, $timeout);
        }
        return $token;
    }

    public function setUserToken($userId, $token)
    {
        $timeout = $this->getTimeout();
        $this->cache->set($this->userTokenKey($userId), $token, $timeout);
    }

    public function getUserToken($userId)
    {
        return $this->cache->get($this->userTokenKey($userId));
    }

    public function deleteUserToken($userId)
    {
        // 获取这个用户的token
        $token = $this->getUserToken($userId); // 有token
        $this->logger->debug('========> last token: ' . $token);
        if (! empty($token)) {
            $this->cache->set($this->tokenKey($token), -1, -1);
            // $this->del($token);
        }
        $this->cache->set($this->userTokenKey($userId), -1, -1);
    }

    /**
     * 获得token内容.
     * @param mixed $value
     * @return string
     */
    public function get(string $token)
    {
        $ret = $this->getInContext($token);
        if (is_null($ret)) {
            $ret = $this->cache->get($this->tokenKey($token));
            if (! empty($ret)) {
                return $ret['value'];
            }
            return null;
        }
        return $ret['value'];
    }

    public function generateRandomString($length = 32)
    {
        $bytes = $this->generateRandomKey($length);
        return substr(strtr(base64_encode($bytes), '+/', '-_'), 0, $length);
    }

    public function generateRandomKey($length = 32)
    {
        if (! is_int($length)) {
            $length = 32;
        }

        if ($length < 1) {
            $length = 32;
        }

        // always use random_bytes() if it is available
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }

        // Since 5.4.0, openssl_random_pseudo_bytes() reads from CryptGenRandom on Windows instead
        // of using OpenSSL library. LibreSSL is OK everywhere but don't use OpenSSL on non-Windows.
        if (function_exists('openssl_random_pseudo_bytes')
            && ($this->_useLibreSSL
                || (
                    DIRECTORY_SEPARATOR !== '/'
                    && substr_compare(PHP_OS, 'win', 0, 3, true) === 0
                ))
        ) {
            $key = openssl_random_pseudo_bytes($length, $cryptoStrong);
            if ($cryptoStrong === false) {
                throw new \Exception(
                    'openssl_random_pseudo_bytes() set $crypto_strong false. Your PHP setup is insecure.'
                );
            }
            if ($key !== false && mb_strlen($key, '8bit') === $length) {
                return $key;
            }
        }

        // mcrypt_create_iv() does not use libmcrypt. Since PHP 5.3.7 it directly reads
        // CryptGenRandom on Windows. Elsewhere it directly reads /dev/urandom.
        if (function_exists('mcrypt_create_iv')) {
            $key = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if (mb_strlen($key, '8bit') === $length) {
                return $key;
            }
        }

        // If not on Windows, try to open a random device.
        if ($this->_randomFile === null && DIRECTORY_SEPARATOR === '/') {
            // urandom is a symlink to random on FreeBSD.
            $device = PHP_OS === 'FreeBSD' ? '/dev/random' : '/dev/urandom';
            // Check random device for special character device protection mode. Use lstat()
            // instead of stat() in case an attacker arranges a symlink to a fake device.
            $lstat = @lstat($device);
            if ($lstat !== false && ($lstat['mode'] & 0170000) === 020000) {
                $this->_randomFile = fopen($device, 'rb') ?: null;

                if (is_resource($this->_randomFile)) {
                    // Reduce PHP stream buffer from default 8192 bytes to optimize data
                    // transfer from the random device for smaller values of $length.
                    // This also helps to keep future randoms out of user memory space.
                    $bufferSize = 8;

                    if (function_exists('stream_set_read_buffer')) {
                        stream_set_read_buffer($this->_randomFile, $bufferSize);
                    }
                    // stream_set_read_buffer() isn't implemented on HHVM
                    if (function_exists('stream_set_chunk_size')) {
                        stream_set_chunk_size($this->_randomFile, $bufferSize);
                    }
                }
            }
        }

        if (is_resource($this->_randomFile)) {
            $buffer = '';
            $stillNeed = $length;
            while ($stillNeed > 0) {
                $someBytes = fread($this->_randomFile, $stillNeed);
                if ($someBytes === false) {
                    break;
                }
                $buffer .= $someBytes;
                $stillNeed -= mb_strlen($someBytes, '8bit');
                if ($stillNeed === 0) {
                    // Leaving file pointer open in order to make next generation faster by reusing it.
                    return $buffer;
                }
            }
            fclose($this->_randomFile);
            $this->_randomFile = null;
        }

        throw new \Exception('Unable to generate a random key');
    }

    public static function get_uuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        return substr($chars, 0, 8) . '-'
                . substr($chars, 8, 4) . '-'
                . substr($chars, 12, 4) . '-'
                . substr($chars, 16, 4) . '-'
                . substr($chars, 20, 12);
    }

    /**
     * 用户token.
     * @param $user_id
     * @return string
     */
    private function userTokenKey($user_id)
    {
        return $this->user_token_prefix . ':' . $user_id;
    }

    /**
     * token数据.
     * @param $token
     * @return string
     */
    private function tokenKey($token)
    {
        return $this->token_preifx . ':' . $token;
    }

    private function getRaw(string $token)
    {
        return $this->cache->get($this->tokenKey($token));
    }

    private function getInContext(string $token)
    {
        if (Coroutine::inCoroutine()) { // 协程内
            $ret = Context::get($token); // 本协程内取token
            $this->logger->debug('Context Token inCoroutine ' . json_encode($ret));
            if (is_null($ret)) { // 如果没有，去父协程内取
                $ret = Context::get($token, null, Coroutine::parentId());
                if (! is_null($ret)) {
                    Context:set($token, $ret);
                }
            }
            return $ret;
        }
        $ret = Context::get($token);
        $this->logger->debug('Context Token ' . json_encode($ret));
        return $ret;
    }

    private function getTimeout()
    {
        $timeout = $this->timeout;
        if ($timeout > time()) {
            $timeout = time() + $timeout;
        }
        return $timeout;
    }
}
