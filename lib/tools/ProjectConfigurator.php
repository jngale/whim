<?php
declare(strict_types=1);

require_once LIB_DIR . 'Project.php';
require_once LIB_DIR . 'tools/GenUtil.php';

class ProjectConfigurator {
    private Project $project;

    public function __construct(Project $project) {
        $this->project = $project;
    }

    public function configure(): array {
        $output = [];

        $name = $this->project->getDomain() ?? $this->project->getName();
        $path = $this->project->getPath();

        $output[] = "[Configure] Creating vhost config for: $name";
        $conf = $this->getVHostBlock('443', $name, $path);

        $target = "/etc/apache2/sites-available/{$name}.conf";
        file_put_contents($target, $conf);
        $output[] = "✅ VHost config written to $target";

        $output[] = "[Configure] Ensuring SSL certs...";
        execCmd("sudo " . SCRIPTS_DIR . "gen_cert.sh");

        $output[] = "[Configure] Enabling site...";
        execCmd("sudo a2ensite {$name}.conf");

        $output[] = "[Configure] Reloading Apache...";
        execCmd("sudo systemctl reload apache2");

        $output[] = "[Configure] Fixing permissions...";
        execCmd("sudo " . SCRIPTS_DIR . "fixperms.sh " . escapeshellarg($this->project->getName()));

        return $output;
    }

    private function getVHostBlock(string $mode, string $name, string $path): string {
        switch ($mode) {
            case '443':
                return $this->get443VHostBlock($name, $path);
            case '80':
                return $this->get80VHostBlock($name, $path);
            case 'both':
            default:
                return $this->get443VHostBlock($name, $path) . "\n\n" . $this->get80VHostBlock($name, $path);
        }
    }

    private function get443VHostBlock(string $name, string $path): string {
        return <<<CONF
            <VirtualHost *:443>
                ServerName {$name}
                DocumentRoot {$path}

                <Directory {$path}>
                    AllowOverride All
                    Require all granted
                </Directory>

                SSLEngine on
                SSLCertificateFile /etc/apache2/ssl/whim-local.pem
                SSLCertificateKeyFile /etc/apache2/ssl/whim-local.key
            </VirtualHost>
        CONF;
    }

    private function get80VHostBlock(string $name, string $path): string {
        return <<<CONF
            <VirtualHost *:80>
                ServerName {$name}
                DocumentRoot {$path}

                <Directory {$path}>
                    AllowOverride All
                    Require all granted
                </Directory>
            </VirtualHost>
        CONF;
    }
}
