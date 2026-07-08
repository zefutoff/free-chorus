<?php

if (!defined('DOL_DOCUMENT_ROOT')) {
    define('DOL_DOCUMENT_ROOT', __DIR__ . '/htdocs');
}

if (!defined('DOL_DATA_ROOT')) {
    define('DOL_DATA_ROOT', __DIR__ . '/documents');
}

if (!defined('DOL_VERSION')) {
    define('DOL_VERSION', '23.0.0');
}

if (!defined('DOL_URL_ROOT')) {
    define('DOL_URL_ROOT', '');
}

if (!defined('MAIN_DB_PREFIX')) {
    define('MAIN_DB_PREFIX', 'llx_');
}

if (!class_exists('DoliDB')) {
    class DoliDB
    {
        /**
         * @return void
         */
        public function close()
        {
        }
    }
}

if (!class_exists('Conf')) {
    class Conf
    {
        /** @var object */
        public $global;

        public function __construct()
        {
            $this->global = new stdClass();
        }
    }
}

if (!class_exists('User')) {
    class User
    {
        /** @var int */
        public $admin = 0;

        /** @var object */
        public $rights;

        public function __construct()
        {
            $this->rights = new stdClass();
        }
    }
}

if (!class_exists('DolibarrModules')) {
    class DolibarrModules
    {
        /**
         * @param string $name Property name
         * @param mixed  $value Property value
         * @return void
         */
        public function __set($name, $value)
        {
        }
    }
}

if (!class_exists('Translate')) {
    class Translate
    {
        /**
         * @param array $domains Language domains
         * @return void
         */
        public function loadLangs(array $domains)
        {
        }

        /**
         * @param string $key Translation key
         * @return string
         */
        public function trans($key)
        {
            return $key;
        }
    }
}

$GLOBALS['conf'] = new Conf();
$GLOBALS['user'] = new User();
$GLOBALS['db'] = new DoliDB();
$GLOBALS['langs'] = new Translate();

if (!function_exists('GETPOST')) {
    /**
     * @param string $param Parameter name
     * @param string $type  Filter type
     * @return string
     */
    function GETPOST($param, $type = '')
    {
        return '';
    }
}

if (!function_exists('accessforbidden')) {
    /**
     * @return void
     */
    function accessforbidden()
    {
    }
}

if (!function_exists('dol_escape_htmltag')) {
    /**
     * @param string $value Raw value
     * @return string
     */
    function dol_escape_htmltag($value)
    {
        return htmlspecialchars($value, ENT_QUOTES);
    }
}

if (!function_exists('newToken')) {
    /**
     * @return string
     */
    function newToken()
    {
        return 'token';
    }
}

if (!function_exists('llxHeader')) {
    /**
     * @param string $head Optional head
     * @param string $title Page title
     * @return void
     */
    function llxHeader($head = '', $title = '')
    {
    }
}

if (!function_exists('llxFooter')) {
    /**
     * @return void
     */
    function llxFooter()
    {
    }
}

if (!function_exists('load_fiche_titre')) {
    /**
     * @param string $title Title
     * @param string $link  Back link
     * @param string $picto Picto
     * @return string
     */
    function load_fiche_titre($title, $link = '', $picto = '')
    {
        return $title;
    }
}

if (!function_exists('img_picto')) {
    /**
     * @param string $alt   Alt text
     * @param string $picto Picto name
     * @return string
     */
    function img_picto($alt, $picto)
    {
        return '';
    }
}

if (!function_exists('setEventMessages')) {
    /**
     * @param string     $message Message
     * @param array|null $errors  Errors
     * @param string     $style   Style
     * @return void
     */
    function setEventMessages($message, $errors = null, $style = 'mesgs')
    {
    }
}

if (!function_exists('dol_syslog')) {
    /**
     * @param string $message Message
     * @return void
     */
    function dol_syslog($message)
    {
    }
}

if (!function_exists('dolibarr_set_const')) {
    /**
     * @return int
     */
    function dolibarr_set_const(...$args)
    {
        return 1;
    }
}

if (!function_exists('dolibarr_del_const')) {
    /**
     * @return int
     */
    function dolibarr_del_const(...$args)
    {
        return 1;
    }
}
