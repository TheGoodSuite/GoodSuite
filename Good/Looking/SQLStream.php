<?php

//error_reporting(E_ALL); 

class SQLStream
{
    private $position;
    private $contents;
    private $id;
    private $lock;
    private $locks;
    private $lastChanged;
    private $modified;
    
    static private $connection;
    static private $table;
    
    public function __construct()
    {
    }
    
    static public function connect($host, $user, $pass, $name)
    {
        self::$connection = mysql_connect($host, $user, $pass);
        mysql_select_db($name, self::$connection);
    }
    
    static function setTable($table)
    {
        self::$table  = $table;
    }
    
    public function dir_closedir()
    {
    }
    
    public function dir_opendir($path, $options)
    {
        $this->position = 0;
        return true;
    }
    
    public function dir_readdir()
    {
        $result = mysql_query("SELECT name
                               FROM " . self::$table . "
                               LIMIT " . $this->position . ", 1",
                              self::$connection);
        
        if (mysql_num_rows($result) == 0)
        {
            return false;
        }
        
        $this->position++;
        $row = mysql_fetch_assoc($result);
        return $row['name'];
    }
    
    public function dir_rewinddir()
    {
        $this->position = 0;
        return true;
    }
    
    public function stream_cast($cast_as)
    {
        return false;
    }
    
    public function stream_close()
    {
        $this->stream_flush();
        mysql_close(self::$connection);
    }
    
    public function stream_eof()
    {
        return $this->position == strlen($this->contents);
    }
    
    public function stream_flush()
    {
        if (!$this->modified)
        {
            return true;
        }
        else
        {
            return mysql_query(sprintf("UPDATE " . self::$table . "
                                        SET contents = '%s',
                                            last_changed = NOW()
                                        WHERE id = %s",
                                        mysql_real_escape_string($this->contents),
                                        mysql_real_escape_string($this->id)),
                               self::$connection);
        }
    }
    
    public function stream_lock($operation)
    {
        if ($operation == LOCK_NB)
        {
            return true;
        }
        
        if ($operation == LOCK_UN)
        {
            mysql_query(sprintf("UPDATE " . self::$table . "
                                 SET lock_" . $this->lock . " = lock_" . $this->lock . " -1
                                 WHERE id = '%s'",
                                mysql_real_escape_string($id)),
                        self::$connection);
            
            $this->lock = null;
            
            echo "click (" + $this->lock + ") <br />";
            return true;
        }
        
        $result = mysql_query(sprintf("SELECT lock_sh, lock_ex
                                       FROM " . self::$table . "
                                       WHERE id = '%s'",
                                       mysql_real_escape_string($id)),
                              $connection);
        
        if (!$row = mysql_fetch_assoc($result))
        {
            return false;
        }
        
        $this->locks['sh'] = $row['lock_sh'];
        $this->locks['ex'] = $row['lock_ex'];
        
        if ($this->locks['ex'] > 0)
        {
            return false;
        }
        
        if ($operation == LOCK_EX && $this->locks['sh'] > 0)
        {
            return false;
        }
        
        if ($operation == LOCK_EX)
        {
            $this->lock = 'ex';
        }
        else
        {
            $this->lock = 'sh';
        }
        
        mysql_query(sprintf("UPDATE " . self::$table . "
                             SET lock_" . $this->lock . " = lock_" . $this->lock . " +1
                             WHERE id = '%s'",
                            mysql_real_escape_string($id)),
                    self::$connection);
            
        return true;
    }
    
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        if (isset($url['path']))
        {
            $path = $url['host'] . $url['path'];
        }
        else
        {
            $path = $url['host'];
        }
        
        $result = mysql_query(sprintf("SELECT id, contents, last_changed
                                       FROM " . self::$table . "
                                       WHERE name = '%s'",
                                       mysql_real_escape_string($path)),
                              self::$connection);
        
        if (mysql_num_rows($result) == 0)
        {
            if ($mode == 'r' || $mode == 'r+')
            {
                if ($options & STREAM_REPORT_ERRORS)
                {
                    trigger_error("Tried to open file that does not exist, while this is" .
                                      "in this mode", E_WARNING);
                }
                
                return false;
            }
            
            $res = mysql_query(sprintf("INSERT INTO " . self::$table . " (name, last_changed)
                                        VALUES ('%s', NOW())",
                                       mysql_real_escape_string($path)),
                               self::$connection);
            
            $this->contents = '';
            $this->id = mysql_insert_id(self::$connection);
            $this->lock = null;
            $this->lastChanged = 0;
        }
        else
        {
            if ($mode == 'x' || $mode == 'x+')
            {
                if ($options & STREAM_REPORT_ERRORS == STREAM_REPORT_ERRORS)
                {
                    trigger_error("Tried to create new file, while an old one with the same name " .
                                     "already exists, which is not allowed in this mode", E_WARNING);
                }
                
                return false;
            }
            
            $row = mysql_fetch_assoc($result);
            
            $this->contents = $row['contents'];
            $this->id = $row['id'];
            $this->lock = null;
            $this->lastChanged = strtotime($row['last_changed']);
        }
        
        $this->modified = false;
        
        if ($mode == 'w' || $mode == 'w+')
        {
            $this->contents = '';
            $this->modified = true;
        }
        
        if ($mode == 'a' || $mode == 'a+')
        {
            $this->position = strlen($this->contents);
        }
        else
        {
            $this->position = 0;
        }
        
        if ($options & STREAM_USE_PATH == STREAM_USE_PATH)
        {
            $opened_path = $path;
        }
        
        return true;
    }
    
    public function stream_read($count)
    {
        $output = substr($this->contents, $this->position, $count);
        
        if ($this->position + $count > strlen($this->contents))
        {
            $this->position = strlen($this->contents);
        }
        else
        {
            $this->position += $count;
        }
        
        return $output;
    }
    
    public function stream_seek($offset, $whence)
    {
        if ($whence == SEEK_SET)
        {
            $this->position = $offset;
        }
        else if ($whence == SEEK_CUR)
        {
            $this->position += $offset;
        }
        else if ($whence == SEEK_END)
        {
            $this->position = strlen($this->contents) + $offset;
        }
    }
    
    public function stream_set_option($option, $arg1, $arg2)
    {
        return false;
    }
    
    public function stream_stat()
    {
        $output = array('dev'     => 0,
                        'ino'     => 0,
                        'mode'    => 0,
                        'nlink'   => 1,
                        'uid'     => 0,
                        'gid'     => 0,
                        'rdev'    => 0,
                        'size'    => strlen($this->contents),
                        'atime'   => $this->lastChanged,
                        'mtime'   => $this->lastChanged,
                        'ctime'   => $this->lastChanged,
                        'blksize' => -1,
                        'blocks'  => -1);
        
        $i = 0;
        $output2 = array();
        foreach ($output as $elem)
        {
            $output2[$i] = $elem;
            $i++;
        }
        
        return $output + $output2;
    }
    
    public function stream_tell()
    {
        return $this->position;
    }
    
    public function stream_write($data)
    {
        if ($this->position > strlen($this->contents))
        {
            $this->position = strlen($this->contents);
        }
        
        $this->modified = true;
        
        $this->contents = substr($this->contents, 0, $this->position) .
                            $data . substr($this->contents, $this->position);
        
        return strlen($data);
    }
    
    public function url_stat($path, $flags)
    {
        $url = parse_url($path);
        
        if (isset($url['path']))
        {
            $path = $url['host'] . $url['path'];
        }
        else
        {
            $path = $url['host'];
        }
        
        
        $this->__construct();
        $result = mysql_query(sprintf("SELECT contents, last_changed
                                       FROM " . self::$table . "
                                       WHERE name = '%s'",
                                      mysql_real_escape_string($path)),
                              self::$connection);
        
        if (mysql_num_rows($result) != 1)
        {
            if ($flags & STREAM_URL_STAT_QUIET != STREAM_URL_STAT_QUIET)
            {
                trigger_error("No such file.", E_WARNING);
            }
            
            return false;
        }
        
        $row = mysql_fetch_assoc($result);
        
        $output = array('dev'     => 0,
                        'ino'     => 0,
                        'mode'    => 0,
                        'nlink'   => 1,
                        'uid'     => 0,
                        'gid'     => 0,
                        'rdev'    => 0,
                        'size'    => strlen($row['contents']),
                        'atime'   => strtotime($row['last_changed']),
                        'mtime'   => strtotime($row['last_changed']),
                        'ctime'   => strtotime($row['last_changed']),
                        'blksize' => -1,
                        'blocks'  => -1);
        
        $i = 0;
        $output2 = array();
        foreach ($output as $elem)
        {
            $output2[$i] = $elem;
            $i++;
        }
        
        return $output + $output2;
    }
}

SQLStream::connect("mysql8.000webhost.com", "a5400048_me", "passw3", "a5400048_LG");
SQLStream::setTable("SQLStream");

stream_wrapper_register('db', 'SQLStream');

?>