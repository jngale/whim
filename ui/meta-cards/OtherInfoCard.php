<?php
declare(strict_types=1);

class OtherInfoCard extends MetadataAbstractCard {
    protected string $title = 'Db Settings';

    public function shouldDisplay(): bool {
        return true;
    }
    
    public function keys(): array {
        return [
            'remote_web_root',
            'local_dev_root',
            'local_stage_root'
        ];
    }
}
