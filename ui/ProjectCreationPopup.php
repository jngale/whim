<?php
declare(strict_types=1);

class ProjectCreationPopup {
    public static function load(): self {
        return new self();
    }

    public function render(): string {
        $types = ProjectType::concreteProjectTypes();

        ob_start();
        ?>
        <div class="modal-overlay active" id="projectModal">
            <div class="modal-content modal-fill">
                <div class="modal-header">
                    <h2 class="modal-title">Create New Project</h2>
                </div>

                <form id="create-project-form" class="card" method="post">
                    <div class="modal-body-scrollable">
                        <fieldset>
                            <legend>New Project</legend>
                            <?php
                                $template = json_decode(file_get_contents(REF_META_FILE), true);
                                $project = Project::fromArray([
                                    'name' => '',
                                    'domain' => '',
                                    'active' => true,
                                    'type' => '',
                                    'backup_path' => '',
                                    'hosting_provider' => '',
                                    'git_repo_url' => '',
                                ]);
                                $card = ProjectSettingsCard::load($project, $template);
                                echo $card->render();
                            ?>
                        </fieldset>
                    </div>
                        <div class="modal-footer">
                        <div class="spinner-wrapper">
                            <div id="project-spinner" class="spinner hidden"></div>
                        </div>
                        <button type="submit" class="button" id="create-project-button">
                            <span class="button-text">Create Project</span>
                            <span class="button-spinner hidden"></span>
                        </button>
                        <button type="button" class="button secondary" onclick="closeModal()">Cancel</button>
                    </div>
                    <div id="create-spinner" class="spinner-overlay hidden">
                        <div class="spinner"></div>
                    </div>
                </form>
            </div>
        </div>
        <script src="js/create_project.js?v=<?= time() ?>"></script>


        <?php
        return ob_get_clean();
    }
}
