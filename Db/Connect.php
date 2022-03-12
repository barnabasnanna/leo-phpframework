<?php
namespace Leo\Db;

use Exception;
use Leo\Leo;
use PDO;
use PDOException;

class Connect extends \Leo\ObjectBase
{
    
    protected $domain;

    public static $db = null;
    private $pdo = null;
    private $dsn = null;
    private $driver = 'mysql';
    /**
     *
     * @var string which dsn connection should be used
     */
    private $connectionName = 'default';

    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
        return $this;
    }

    /**
     * Name of dsn connection you want loaded
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Returns PDO object
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    public function close()
    {
        $this->setPdo(null);
    }

    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function connect()
    {
        $dsn = $this->getDatabaseSettings();
        $host = $dsn['host'];
        $pass = $dsn['password'];
        $user = $dsn['username'];
        $dbname = $dsn['database'];

        try
        {
            switch ($this->driver)
            {
                case 'mssql':
                    $PDO = new \PDO("mssql:host=$host;dbname=$dbname, $user, $pass");
                    break;

                case 'sybase':
                    $PDO = new \PDO("sybase:host=$host;dbname=$dbname, $user, $pass");
                    break;

                case 'mysql':
                    // MySQL with PDO_MYSQL
                    $PDO = new \PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
                    break;

                case 'sqlite':
                    //SQLite Database
                    $PDO = new \PDO("sqlite:my/database/path/database.db");
                    break;

                default:
                    throw new PDOException($this->driver . ' not supported');
            }

            //setting PDO to throw exceptions
            if($PDO){
                $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            else
            {
                throw new \Exception('Database connection failed. Check connection details');
            }

            $this->setPdo($PDO);
        }
        catch (PDOException $e)
        {
            Leo::log('Could not connect to the database', LOG_TYPE_FATAL);
            Leo::log($e->getMessage(), LOG_TYPE_FATAL);
            die('Could not connect to the database');
        }

    }

    protected function getHost()
    {
        $dsn = $this->getDatabaseSettings();

        return $dsn['host'];
    }

    protected function getUserName()
    {
        $dsn = $this->getDatabaseSettings();

        return $dsn['username'];
    }

    protected function getPassword()
    {
        $dsn = $this->getDatabaseSettings();

        return $dsn['password'];
    }

    public function getDatabase()
    {
        $dsn = $this->getDatabaseSettings();

        return $dsn['database'];
    }

    /**
     * Get db connection credentials
     * @return array
     * @throws Exception
     */
    public function getDatabaseSettings()
    {
        if($this->dsn===null)
        {

            Leo::log('Fetching database connection credentials with name ('.$this->connectionName.') for domain ('.$this->domain.')');
            
            $dbConfig = \leo()->getDomainManager()->getDbConnection($this->connectionName,$this->getDomain());

            if(!is_array($dbConfig))
            {
                throw new \Exception('Database config not found for domain '. $this->getDomain());
            }

            $this->setDsn($dbConfig);
        }

        return $this->dsn;
    }

    public function setDsn(array $database_login_information)
    {
        $this->dsn = $database_login_information;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }


    public function _start_()
    {
        $this->setDomain($this->domain?:leo()->getDomainManager()->getBase());
        $this->connect();
    }
    

}
