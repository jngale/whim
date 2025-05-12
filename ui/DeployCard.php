<?php
declare(strict_types=1);

class DeployCard extends DisplayCard {
    protected Project $project;

    public static function load(Project $project): static {
        $instance = new static();
        $instance->project = $project;
        return $instance;
    }

    public function render(): string {
        $name = $this->project->get('name');
        $typeName = $this->project->get('type');
        $type = $this->project->getProjectType();

        $buttons = $type->getAdminButtons($this->project);

        ob_start();
        ?>
        <div class="card">
            <h2><?= htmlspecialchars($name) ?></h2>
            <p>Type: <?= htmlspecialchars($typeName) ?></p>

            <div class="card-buttons">
                <?php foreach ($buttons as $label => $url): ?>
                    <?php if (str_starts_with($url, 'vscode:')): ?>
                        <button class="button" onclick="openVSCode('<?= htmlspecialchars(substr($url, 7)) ?>')">
                            <?= htmlspecialchars($label) ?>
                        </button>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($url) ?>" class="button" target="_blank"><?= htmlspecialchars($label) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>            
            </div>

        </div>
        <?php
        return ob_get_clean();
    }
}
