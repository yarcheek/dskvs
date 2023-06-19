# Dead Simple Key Value Store

DSKVS for PHP.
It's been designed with the opcache in mind, which makes it faster than Redis as suggested [here](https://medium.com/@dylanwenzlau/500x-faster-caching-than-redis-memcache-apc-in-php-hhvm-dcd26e8447ad).

If you need to store simple key-value pairs data and don't want to use a database, this can be a great option. 

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
# Settings when data are stored in /tmp/dskvs/users
$users = new DSKVS('users');
$users->set('martin_white', array(
  'name' => 'Martin White',
  'age' => 18
));
echo $users->get('martin_white')['age'];
```

```
# Settings when data are stored in /var/logs/myapp
$logs = new DSKVS('myapp', '/var/logs');
$logs->set(time(), array(
  'env' => 'prod',
  'type' => 'warning'
  'msg' => 'User limit reached'
));
```


## Technical notes
- Data is stored (by default) in the /tmp directory where each key is a separate file. This is a good configuration for a cached data. If you want to have data persisten also after a server reboot, set a different location such as /var/www/html, etc..
- When opcache is enabled, PHP caches thsese files which makes it so fast, otherwise is as fast as storing data in a file. Opcache by default won't recompile files 2s old at least, so we hack it when updating a value by setting the file date older.
- Make sure to have a storage directory structure prepared beforehand should you use a custom configuration.
