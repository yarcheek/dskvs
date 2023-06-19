<?php

/* 
 * Dead simple KVS for PHP. It's been designed with the opcache in mind which makes it superfast.
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

		public function lock() {
			$this->locked = true;			
			return true;
		}

		public function unlock() {
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
			if ($this->isLocked() || $this->isKeyLocked($key)) return false;
			$val = var_export($val, true);
			$file = $this->storageDir.'/'.$this->db.'/'.$key;
			file_put_contents($file, '<?php $val = ' . $val . ';', LOCK_EX);			
			touch($file, time()-2); // hack to overcome the default opcache.revalidate_freq=2 settings
			opcache_invalidate($file);
			opcache_compile_file($file);			
			@include $file; //warming up the file's opcache			
			return true;
		}
		
		public function get($key) {
			$file = $this->storageDir.'/'.$this->db.'/'.$key;
			@include $file;
			return isset($val) ? $val : null;
		}

	}

}


?>