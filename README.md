# Dead Simple Key Value Store

DSKVS for PHP. If you need to store simple key-value pairs data and don't want to use a database, this can be a great option.

It stores PHP objects (instead of serialised data like other tools) which in combination with opcache makes it faster than Redis or Memcache (if you run PHP 7+). 
This causes 2-3x higher memmory consumtion, so it may not the best solution for large datasets.


## Why to use it
- dead simple (just few basic functions)
- easy to implement (single file with a single class)
- very short codebase easy to understand
- high performence (caches data in memmory via opcache)

## When not to use it
- you have a distributed system (because data is stored localy)
- you need to search the dataset

## How to use it

```
# Default settings when data are stored in /tmp/dskvs/default.
# You can store strings, integers, bool as well as arrays
$storage = new DSKVS();
$storage->set('display', 'none');
$storage->set('production', true);
$storage->set('colors', array('green', 'red', 'blue'));
echo $storage->get('production');

# Locks / unlocks the whole storage preventing adding and updating values
$storage->lock();
$storage->unlock();
$storage->isLocked();


# Locks / unlocks a specific key. This applies only for the duration of the script as the flag is not permanent.
$storage->lockKey('progress');
$storage->lockKey(['progress', 'source', 'destination']);

$storage->unlockKey('progress');
$storage->unlockKey(['progress', 'source', 'destination']);

$storage->isKeyLocked('progress');

# Name of the used database
$storage->currentDB();


```

```
# Custom database name, data is stored in /tmp/dskvs/users
$users = new DSKVS('users');
$users->set('martin_white', array(
  'name' => 'Martin White',
  'age' => 18
));
echo $users->get('martin_white')['age'];
```

```
# Custom database name and custom store location, data is stored in /var/logs/myapp
$logs = new DSKVS('myapp', '/var/logs');
$logs->set(time(), array(
  'env' => 'prod',
  'type' => 'warning'
  'msg' => 'User limit reached'
));
```


## Technical notes
Data is stored (by default) in the /tmp directory where each key is a separate file. This is a good configuration for a cache type of data. If you want to have data persisten also after a server reboot, set a different location such as /var/www/html, etc.. Make sure to have a storage directory structure prepared beforehand should you use a custom configuration.
 
## Opcache notes
Your opcache.memory_consumption setting needs to be larger than the size of all your code files plus all the data you plan to store in the cache.
Your opcache.max_accelerated_files setting needs to be larger than your total number of code files plus the total number of keys you plan to cache.
Your opcache.interned_strings_buffer settings may need to be larger than default 8 (use 16 or 32 for instance) should you store larger datasets.
If those settings aren’t high enough, the storage will still work, but its performance may suffer.

It's not so great to use this technique for data that gets rewritten often. Opcache does not defragment or free up old data, it simply marks it as "wasted." Eventually you will fill up to your limit of wasted memory, which will trigger a opcache reset. Not a bad idea to use opcache file caching on the permanent scripts to speed that up. Pre-loading as well. 
