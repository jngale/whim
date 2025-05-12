<?php
declare(strict_types=1);

class User {
    public string $userName;
    public string $name;
    public string $email;
    public bool $admin = false;
    public string $password = '';
    public array $editableProjects = [];
    public array $deployableProjects = [];

    public static function load(): self {
        $override = $_COOKIE['whim_active_user'] ?? null;
        $systemUser = $override ?: get_current_user();
    
        $user = new self();
        $user->userName = $systemUser;

        switch ($systemUser) {
            case 'john':
                $user->name = 'John Gale';
                $user->email = 'john@example.com';
                $user->admin = true;
                $user->password = 'Ripple';
                $user->editableProjects = ['*'];    // Wildcard → can edit all
                $user->deployableProjects = ['*'];
                break;
            case 'chuck':
                $user->name = 'Chuck Gale';
                $user->email = 'chuck@example.com';
                $user->editableProjects = ['squarepegartstudio'];
                $user->deployableProjects = ['squarepegartstudio'];
                break;
            case 'www-data':
            case 'whim':
                $user->name = 'WHIM Bot';
                $user->email = 'whim@localhost';
                break;
            default:
                $user->name = ucfirst($systemUser);
                $user->email = "{$systemUser}@localhost";
        }
    
        return $user;
    }

    public static function fromArray(array $data): self {
        $user = new self();
        $user->userName = $data['name'] ?? '';
        $user->name = $data['name'] ?? '';
        $user->email = $data['email'] ?? '';
        $user->admin = (bool) ($data['is_admin'] ?? false);
        $user->password = $data['password'] ?? '';
        $user->editableProjects = [];  // DAO-backed version won’t track this
        $user->deployableProjects = [];
        return $user;
    }
    
    public function toArray(): array {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'is_admin' => $this->admin,
            'has_database_access' => true // assume yes for now
        ];
    }
    

    public function isAdmin(): bool {
        return $this->admin;
    }

    public function canEdit(string $projectName): bool {
        return in_array('*', $this->editableProjects) || in_array($projectName, $this->editableProjects, true);
    }

    public function canDeploy(string $projectName): bool {
        return in_array('*', $this->deployableProjects) || in_array($projectName, $this->deployableProjects, true);
    }

    // getters and setters
    public function getDisplayName(): string {
        return this->name;
    }
}
