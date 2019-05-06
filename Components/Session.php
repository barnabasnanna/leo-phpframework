<?php

namespace Leo\Components;

use Leo\ObjectBase;
/**
 * Session allows you manage $_SESSION
 *
 * @author barnabasnanna
 */
class Session extends ObjectBase
{
    /**
     * Start or resume session
     * @return bool
     */
    public function start($name = '')
    {
        if($name)
        {
            $this->setName($name);
        }
        
        return session_start();
    }

    /**
     * Destroy session
     * @return bool 
     */
    public function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {

            // Unset all of the session variables.
            $_SESSION = array();

            // If it's desired to kill the session, also delete the session cookie.
            // Note: This will destroy the session, and not just the session data!
            if (ini_get("session.use_cookies"))
            {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
                );
            }

            // Finally, destroy the session.
            return session_destroy();
        }
    }

    /**
     * Store a variable in session. Overrides if already exists
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function get($name, $defaultValue = null)
    {

        return $this->exists($name) ? $_SESSION[$name] : $defaultValue;
    }

    /**
     * Adds a variable to session. Throws exceptions if the variable already exists
     * @param string $name
     * @param mixed $value
     * @throws \Exception
     */
    public function add($name, $value)
    {
        if ($this->exists($name))
        {
            throw new \Exception($name . ' already exists in session. use set() if you want to override');
        }

        $this->set($name, $value);
    }

    public function exists($name)
    {
        if(!$_SESSION) return false;
        return array_key_exists($name, $_SESSION);
    }

    /**
     * Remove variable from session
     * @param string $name
     */
    public function remove($name)
    {
        if ($this->exists($name))
        {
            unset($_SESSION[$name]);
        }
    }

    public function setName($name)
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            throw new \Exception('Session has already started');
        }

        session_name($name);
    }
    
    public function status()
    {
        return session_status();
    }
    
    public function setPath($path = '')
    {
        if(!is_dir($path))
        {
            throw new \Exception($path .' does not exist');
        }
        
        if($this->status() === PHP_SESSION_ACTIVE)
        {
            throw new \Exception('Session has alreay been started');
        }
        
        session_save_path($path);
    }

}
