<?php
declare(strict_types=1);

class ProjectCard extends DisplayCard {
    protected Project $project;

    public static function load(Project $project): static {
        $instance = new static();
        $instance->project = $project;
        return $instance;
    }

    public function render(): string {
        $name = $this->project->get('name');
        $typeName = $this->project->get('type');

        // 🔧 Call statically
        if (!class_exists($typeName)) {
            error_log("[ProjectCard] ❌ Unknown project type: $typeName");
            return "<div class='card error'>Unknown type for $name</div>";
        }

        $buttonGroups = $typeName::getProjectButtons($this->project);

        ob_start();
        ?>
        <div class="card">
            <h2><?= htmlspecialchars($name) ?></h2>
            <p>Type: <?= htmlspecialchars($typeName) ?></p>

            <?php foreach ($buttonGroups as $groupName => $buttons): ?>
                <fieldset class="button-group">
                    <legend><?= ucfirst($groupName) ?></legend>
                    <?php foreach ($buttons as $buttonHtml): ?>
                        <?= $buttonHtml ?>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
