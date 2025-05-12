<?php

class UserManager {
    protected static string $overrideFile = '/tmp/whim_active_user';

    // Detect active user
    public static function getCurrent(): string {
        // 1. Cookie override
        if (!empty($_COOKIE['whim_user'])) {
            return $_COOKIE['whim_user'];
        }
    
        // 2. File override (old behavior)
        if (file_exists(self::$overrideFile)) {
            return trim(file_get_contents(self::$overrideFile));
        }
    
        // 3. System fallback (default behavior)
        $user = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown';
    
        // 4. Ignore service accounts
        if (in_array($user, ['www-data', 'whim'])) {
            return 'system';
        }
    
        return $user;
    }
    
    

    // Manually override current user
    public static function setOverride(string $user): void {
        file_put_contents(self::$overrideFile, $user);
    }

    // Clear override
    public static function clearOverride(): void {
        if (file_exists(self::$overrideFile)) {
            unlink(self::$overrideFile);
        }
    }

    // Return Git-ready name/email for commits
    public static function getGitSignature(): array {
        $user = self::getCurrent();
        return [
            'name'  => ucfirst($user),
            'email' => "{$user}@whim.dev.local"
        ];
    }

    // Format Git commit prefix
    public static function gitPrefix(): string {
        return self::getCurrent() . ": ";
    }
}
