<?php
/**
 * memcache 缓存 类
 * 
 * @category F
 * @package F_Cache
 * @subpackage F_Cache_Memcache
 * @author allen <allenifox@163.com>
 */
final class F_Cache_Memcache extends F_Cache_Abstract
{
    /**
     * 获取某服务的memcache缓存对象实例
     * 
     * 每个服务 $serviceName 都是单例模式
     * 
     * @param string $serviceName
     * @return F_Cache_Memcache
     */
    public static function getInstance($serviceName)
    {
        static $instances = array();
        
        if (!isset($instances[$serviceName])) {
            $instances[$serviceName] = new self($serviceName);
        }
        
        return $instances[$serviceName];
    }
    
    /**
     * 选项
     * 
     * @var array
     */
    protected $_options = array(
        'servers'  => array(array(
            'host'   => '127.0.0.1',
            'port'   => 11211,
            'weight' => 100,
        )),
        'client'   => array(),
        'lifetime' => 3600,
    );
    
    /**
     * Memcached 对象
     *
     * @var mixed memcached object
     */
    protected $_memcache = null;
    
    public function __construct($serviceName)
    {
        static $options = array();
        
        if (!extension_loaded('memcached')) {
            throw new F_Cache_Exception('memcached 扩展无法加载');
        }
        
        if (empty($options)) {
            F_Config::load('/configs/memcache.cfg.php');
            $options = F_Config::get('memcache.'.$serviceName);
        }

        // override default client options
        $this->_options['client'] = array(
            Memcached::OPT_DISTRIBUTION         => Memcached::DISTRIBUTION_CONSISTENT,
            Memcached::OPT_HASH                 => Memcached::HASH_MD5,
            Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
        );

        parent::__construct($options);

        if (isset($this->_options['servers'])) {
            $value = $this->_options['servers'];
            if (isset($value['host'])) {
                // in this case, $value seems to be a simple associative array (one server only)
                $value = array(0 => $value); // let's transform it into a classical array of associative arrays
            }
            $this->setOption('servers', $value);
        }
        $this->_memcache = new Memcached;

        // setup memcached client options
        foreach ($this->_options['client'] as $name => $value) {
            $optId = null;
            if (is_int($name)) {
                $optId = $name;
            } else {
                $optConst = 'Memcached::OPT_' . strtoupper($name);
                if (defined($optConst)) {
                    $optId = constant($optConst);
                } else {
                    throw new F_Cache_Exception("Unknown memcached client option '{$name}' ({$optConst})");
                }
            }
            if (null !== $optId) {
                if (!$this->_memcache->setOption($optId, $value)) {
                    throw new F_Cache_Exception("Setting memcached client option '{$optId}' failed");
                }
            }
        }

        // setup memcached servers
        $servers = array();
        foreach ($this->_options['servers'] as $server) {
            $servers[] = array($server['host'], $server['port'], $server['weight']);
        }

        $this->_memcache->addServers($servers);
    }
    
    /**
     * 从缓存中取多个key的数据
     * 
     * @param array $idAry
     * @return array | boolean
     *
     * $cache->getBackend()->loadMulti(array('dfdf1', 'dfdf2', 'dfdf3'))
     */
    public function loadMulti($idAry)
    {
        $result = $this->_memcache->getMulti($idAry, $cas);
        if ($this->_memcache->getResultCode() == Memcached::RES_NOTFOUND) {
            return false;
        }
        return $result;
    }

    /**
     * 从缓存中取单个key的数据
     *
     * @param  string  $id Cache id
     * @return string|false cached datas
     */
    public function load($id)
    {
        $result = $this->_memcache->get($id);
        if ($this->_memcache->getResultCode() == Memcached::RES_NOTFOUND) {
            return false;
        }
        return $result;
    }
    
    /**
     * 保存数据到缓存
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data             Datas to cache
     * @param  string $id               Cache id
     * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function save($data, $id, $specificLifetime = false)
    {
        $lifetime = $this->_getLifetime($specificLifetime);
        $result = @$this->_memcache->set($id, $data, $lifetime);
        if ($result === false) {
            $rsCode = $this->_memcache->getResultCode();
            $rsMsg  = $this->_memcache->getResultMessage();
            throw new F_Cache_Exception("Memcached::set() failed: [{$rsCode}] {$rsMsg}");
        }
        return $result;
    }

    /**
     * 删除指定key的缓存数据
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id)
    {
        $result = $this->_memcache->delete($id);
        return $result;
    }
    
    /**
     * 增量
     * 
     * @param  int $data             Datas to cache
     * @param  string $id               Cache id
     * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function increment($data, $id, $specificLifetime = false)
    {
        $lifetime = $this->getLifetime($specificLifetime);
        $result = $this->_memcache->increment($id, $data, 0, $specificLifetime);
        if ($result === false) {
            $rsCode = $this->_memcache->getResultCode();
            $rsMsg  = $this->_memcache->getResultMessage();
            throw new F_Cache_Exception("Memcached::set() failed: [{$rsCode}] {$rsMsg}");
        }
        return $result;
    }
    
    /**
     * 减量
     * 
     * @param  int $data             Datas to cache
     * @param  string $id               Cache id
     * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function decrement($data, $id, $specificLifetime = false)
    {
        $lifetime = $this->getLifetime($specificLifetime);
        $result = $this->_memcache->decrement($id, $data, 0, $lifetime);
        if ($result === false) {
            $rsCode = $this->_memcache->getResultCode();
            $rsMsg  = $this->_memcache->getResultMessage();
            throw new F_Cache_Exception("Memcached::set() failed: [{$rsCode}] {$rsMsg}");
        }
        return $result;
    }
    
    public function getByCas($id, &$cas)
    {
        $result = $this->_memcache->get($id, null, $cas);
        if ($this->_memcache->getResultCode() == Memcached::RES_NOTFOUND) {
            return false;
        }
        return $result;
    }

    public function cas($data, $id, $cas, $specificLifetime = false)
    {
        $lifetime = $this->_getLifetime($specificLifetime);
        $result = $this->_memcache->cas($cas, $id, $data, $lifetime);
        if ($result === false) {
            $rsCode = $this->_memcache->getResultCode();
            $rsMsg  = $this->_memcache->getResultMessage();
            throw new F_Cache_Exception("Memcached::set() failed: [{$rsCode}] {$rsMsg}");
        }

        return $result;
    }
    
    /**
     * 添加一个新内容，如果key不存在，可作为原子性操作检测
     * 
     * @param mixed $data
     * @param string $id
     * @param mixed $specificLifetime
     * @return boolean
     */
    public function add($data, $id, $specificLifetime = false)
    {
        $lifetime = $this->getLifetime($specificLifetime);
        $result = $this->_memcache->add($id, $data, $lifetime);
        if ($result === false) {
            $rsCode = $this->_memcache->getResultCode();
            $rsMsg  = $this->_memcache->getResultMessage();
            throw new F_Cache_Exception("Memcached::set() failed: [{$rsCode}] {$rsMsg}");
        }
        return $result;
    }
    
    public function getResultCode()
    {
        return $this->_memcache->getResultCode();
    }
    
    /**
     * 获取生命周期
     * 
     * @param mixed<int|boolean> $specificLifetime
     * @return int
     */
    private function _getLifetime($specificLifetime)
    {
        if ($specificLifetime === false) {
            return $this->_options['lifetime'];
        }
        if (is_null($specificLifetime)) {
            return 0;
        }
        return $specificLifetime;
    }
}
