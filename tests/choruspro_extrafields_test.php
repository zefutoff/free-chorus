<?php

$root = sys_get_temp_dir() . '/choruspro-dolibarr-stub-' . getmypid();
@mkdir($root . '/core/modules', 0777, true);
@mkdir($root . '/core/class', 0777, true);

file_put_contents($root . '/core/modules/DolibarrModules.class.php', <<<'PHPSTUB'
<?php
if (!class_exists('DolibarrModules')) {
    class DolibarrModules
    {
        public function _load_tables($path)
        {
            return 1;
        }

        public function _init($sql, $options = '')
        {
            return 1;
        }

        public function _remove($sql, $options = '')
        {
            return 1;
        }
    }
}
PHPSTUB);

file_put_contents($root . '/core/class/extrafields.class.php', <<<'PHPSTUB'
<?php
class ExtraFields
{
    public static $calls = array();

    public function __construct($db)
    {
    }

    public function addExtraField(...$args)
    {
        self::$calls[] = $args;
        return 1;
    }
}
PHPSTUB);

if (!defined('DOL_DOCUMENT_ROOT')) {
    define('DOL_DOCUMENT_ROOT', $root);
}

require __DIR__ . '/../htdocs/custom/choruspro/core/modules/modChorusPro.class.php';

$module = new modChorusPro(null);
$result = $module->init();
if ($result < 0) {
    fwrite(STDERR, "Module init failed.\n");
    exit(1);
}

if (count(ExtraFields::$calls) !== 7) {
    fwrite(STDERR, "Expected 7 ExtraFields calls, got " . count(ExtraFields::$calls) . ".\n");
    exit(1);
}

foreach (ExtraFields::$calls as $call) {
    if (!isset($call[9]) || $call[9] !== '') {
        fwrite(STDERR, "addExtraField param argument must be an empty string.\n");
        exit(1);
    }
    if (isset($call[9]) && is_array($call[9])) {
        fwrite(STDERR, "addExtraField param argument must not be an array.\n");
        exit(1);
    }
    if (!isset($call[12]) || $call[12] !== '1') {
        fwrite(STDERR, "addExtraField list argument must be string '1'.\n");
        exit(1);
    }
    if (!isset($call[16]) || $call[16] !== 'choruspro@choruspro') {
        fwrite(STDERR, "addExtraField langfile argument mismatch.\n");
        exit(1);
    }
}

@unlink($root . '/core/modules/DolibarrModules.class.php');
@unlink($root . '/core/class/extrafields.class.php');
@rmdir($root . '/core/modules');
@rmdir($root . '/core/class');
@rmdir($root . '/core');
@rmdir($root);

echo "ChorusPro extrafields arguments tests OK\n";
