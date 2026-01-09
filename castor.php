<?php

use Castor\Attribute\AsTask;

use function Castor\{io,run,capture,import};
import('.castor/vendor/tacman/castor-tools/castor.php');

import(__DIR__ . '/src/Command/CaFetchCommand.php');

#[AsTask('build', description: 'setup for testing')]
function build(): void
{
//    run('bin/console doctrine:schema:validate');
    forte(50);


}

#[AsTask('fetch', description: 'Import the CA dataset')]
function forte(
    #[\Castor\Attribute\AsOption(description: 'import limit')] int $limit = 500
): void
{
    // fetches the one inst
    run('bin/console ca:fetch');

    // @todo
//    run('bin/console state:iterate Inst --marking new -t download -v --limit 3 --sync');
}



#[AsTask(description: 'Welcome to Castor!')]
function hello(): void
{
    $currentUser = capture('whoami');

    io()->title(sprintf('Hello %s!', $currentUser));
}
