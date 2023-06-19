<?php

/* 
 * Dead simple KVS for PHP. It's been designed with the opcache in mind which makes it superfast.
 * Inpired by https://medium.com/@dylanwenzlau/500x-faster-caching-than-redis-memcache-apc-in-php-hhvm-dcd26e8447ad
 * Copyright © 2023 Martin Jarcik <martin.jarcik@gmail.com>. All rights reserved.
 */
 
if (!class_exists("DSKVS")) {

	class DSKVS {
		
		private $locked = false;
		private $lockedKeys = [];
		private $db;
		private $storageDir;
				
		function __construct($db = 'default', $dir = '/tmp/dskvs') {
			$this->storageDir = rtrim($dir, '/');
			$this->db = $db;
			$db_dir = $this->storageDir.'/'.$this->db;			
			if (!is_dir($dir) && !mkdir($dir)) error_log('Cannot access the KVS directory '.$this->storageDir);			
			if (!is_dir($db_dir) && !mkdir($db_dir)) error_log('Cannot create the KVS database directory '.$db_dir);
		}

		public function lock($lock) {
			if (!is_bool($lock)) return false;			
			$this->locked = true;			
			return true;
		}

		public function unlock($lock) {
			if (!is_bool($lock)) return false;			
			$this->locked = false;			
			return true;
		}
		
		public function isLocked() {
			return $this->locked;
		}

		public function lockKey($keys) {
			if (!is_array($keys) && !is_string($keys)) return false;			
			$this->lockedKeys += (array)$keys;			
			return true;
		}

		public function unlockKey($keys) {
			if (!is_array($keys) && !is_string($keys)) return false;	
			$this->lockedKeys = array_diff($this->lockedKeys, (array)$keys);
			return true;
		}
		
		public function isKeyLocked($key) {
			if (!is_string($key)) return null;
			return in_array($key, $this->lockedKeys);
		}
		
		public function currentDB() {
			return $this->db;
		}	

		public function set($key, $val) {
			if ($this->locked || in_array($key, $this->lockedKeys)) return false;
			$val = var_export($val, true);
			$file = $this->storageDir.'/'.$this->db.'/'.$key;
			file_put_contents($file, '<?php $val = ' . $val . ';', LOCK_EX);
			touch($file, time()-2);
			opcache_invalidate($file);
			opcache_compile_file($file);
			return truel
		}
		
		public function get($key) {
			$file = $this->storageDir.'/'.$this->db.'/'.$key;
			@include $file;
			return isset($val) ? $val : null;
		}

	}

}


?>