<?php
declare(strict_types=1);

abstract class MetadataAbstractCard extends DisplayCard {
    protected ?Project $project;
    protected array $template;
    protected array $metadata = [];


    public static function load(?Project $project, array $template): static {
        $instance = new static();
        $instance->project = $project;
        $instance->template = $template;
        return $instance;
    }

    abstract public function shouldDisplay(): bool;

    public function render(): string {
        error_log("[JG MetadataAbstractCard->render]");

        $output = '<div class="field-grid">';
        $keys = $this->keys();

        foreach ($keys as $key) {
            if (!isset($this->template[$key])) {
                continue;
            }
            $config = $this->template[$key];
            $field = $this->renderField($key, $config);
            $output .= "<div class='field-item'>{$field}</div>";
        }

        $output .= '</div>';
        return $this->wrap($output);
    }



    

    protected function renderField(string $key, array $config): string {
        $value = $this->project ? $this->project->get($key) : ($this->template[$key]['default'] ?? '');
        $type = $config['type'] ?? 'text';
        $label = ucfirst(str_replace('_', ' ', $key));
        $id = htmlspecialchars((string) $key);
        $value = is_scalar($value) || $value === null ? (string) $value : '';
        
        switch ($type) {
            case 'boolean':
                $checked = $value ? 'checked' : '';
                return "
                    <div class='active-toggle'>
                        <label for='{$id}'>{$label}</label>
                        <input type='checkbox' name='{$id}' id='{$id}' {$checked}>
                    </div>
                ";

            case 'array':
                $lines = is_array($value) ? implode("\n", $value) : '';
                return "<label for='{$id}'>{$label}</label><textarea name='{$id}' id='{$id}'>" . htmlspecialchars($lines) . "</textarea><br>";

            case 'dropdown':
                $options = $config['values'] ?? [];
            
                if ($key === 'type') {
                    $options = [];
                    foreach (ProjectType::concreteProjectTypes() as $typeClass) {
                        $display = method_exists($typeClass, 'getDisplayName')
                            ? (new $typeClass())->getDisplayName()
                            : (new \ReflectionClass($typeClass))->getShortName();
                        $options[$typeClass] = $display;
                    }
                } elseif ($key === 'owner') {
                    $groupInfo = posix_getgrnam('devs');
                    $options = [];
                    foreach ($groupInfo['members'] as $user) {
                        $options[$user] = ucfirst($user);
                    }
                }
            
                $out = "<label for='{$id}'>{$label}</label><select name='{$id}' id='{$id}'>";
                foreach ($options as $val => $desc) {
                    $selected = ($val === $value) ? 'selected' : '';
                    $out .= "<option value='" . htmlspecialchars($val) . "' {$selected}>" . htmlspecialchars($desc) . "</option>";
                }
                $out .= "</select><br>";
                return $out;
                

            default:
                return "<label for='{$id}'>{$label}</label><input type='text' name='{$id}' id='{$id}' value='" . htmlspecialchars($value) . "'><br>";
        }
    }
}
