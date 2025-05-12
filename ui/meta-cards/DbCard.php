<?php
declare(strict_types=1);

class DbCard extends MetadataAbstractCard {
    protected string $title = 'Db Settings';

    public function shouldDisplay(): bool {
        return true;
    }
    
    public function keys(): array {
        return [
            'db_prefix',
            'remote_db_name',
            'remote_db_user',
            'remote_db_pass',
            'staging_db_name',
            'local_db_name',
            'local_db_user',
            'local_db_pass'
        ];
    }


}
