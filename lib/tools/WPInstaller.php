<?php
declare(strict_types=1);

class WPInstaller {
    protected static $name;
    protected static $path;
    protected static $dbUser = 'root';
    protected static $dbPass = 'Ripple'; // Adjust as needed

    public static function load(string $name, string $path, bool $forceResetDb = false): void {
        self::$name = $name;
        self::$path = $path;
        $url = "http://{$name}.dev.local";
        $dbName = "wp_{$name}";
        $configPath = self::$path . '/wp-config.php';
    
        // ✅ (1) If wp-config.php exists before anything, delete it
        if (file_exists($configPath)) {
            error_log("[WHIM WPInstaller] Found old wp-config.php before download, removing...");
            unlink($configPath);
        }
    
        // (2) Download WordPress core
        execCmd("wp core download --skip-content", self::$path);
    
        // ✅ (3) Again after download, just in case wp-config.php got created
        if (file_exists($configPath)) {
            error_log("[WHIM WPInstaller] Found wp-config.php after download, removing...");
            unlink($configPath);
        }
    
        // (4) Now safe to create wp-config.php
        execCmd("wp config create --dbname={$dbName} --dbuser=" . self::$dbUser . " --dbpass=" . self::$dbPass, self::$path);
    
        // (5) Handle database
        if ($forceResetDb) {
            execCmd("mysql -u" . self::$dbUser . " -p" . self::$dbPass . " -e 'DROP DATABASE IF EXISTS `{$dbName}`; CREATE DATABASE `{$dbName}`;'");
        } else {
            execCmd("mysql -u" . self::$dbUser . " -p" . self::$dbPass . " -e 'CREATE DATABASE IF NOT EXISTS `{$dbName}`;'");
        }
    
        // (6) Install WordPress
        execCmd("wp core install --url={$url} --title={$name} --admin_user=admin --admin_password=password --admin_email=admin@{$name}.dev.local --skip-email", self::$path);
    
        // (7) Clean up plugins
        execCmd("rm -rf wp-content/plugins/*", self::$path);
    }
    
}
