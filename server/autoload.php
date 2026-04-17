<?php
/**
 * PSR-4 Compatible Autoloader - Pure PHP (No Composer Dependencies)
 */
spl_autoload_register(function ($class) {
    // PSR-4: LKSCore\ → server/ root
    if (strpos($class, 'LKSCore\\') === 0) {
        $relativeClass = substr($class, 8);  // Remove 'LKSCore\'
        $file = __DIR__ . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
?>
