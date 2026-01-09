<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CollectiveAccessGraphQLService;
use Survos\JsonlBundle\IO\JsonlWriter;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('ca:fetch', 'Fetch objects from CollectiveAccess GraphQL API and write to JSONL')]
final class CaFetchCommand
{
    private const DEFAULT_BUNDLES = [
        // Core identification
        'ca_objects.preferred_labels.name',
        'ca_objects.nonpreferred_labels',
        'ca_objects.idno',
        'ca_objects.type_id',

        // Visibility & status
        'ca_objects.access',
        'ca_objects.status',

        // Descriptions
        'ca_objects.description',
        'ca_objects.descriptionSet',
        'ca_objects.internal_notes',

        // Dates
        'ca_objects.object_date',
        'ca_objects.primaryDateSet',

        // Physical attributes
        'ca_objects.dimensions',
        'ca_objects.georeference',
        'ca_objects.geonames',

        // Keywords & subject headings
        'ca_objects.RHS_keywords_list',
        'ca_objects.lcsh_terms',

        // Rights & source
        'ca_objects.rightsSet',
        'ca_objects.sourceSet',

        // Related records
        'ca_entities.preferred_labels.displayname',
        'ca_places.preferred_labels.name',
        'ca_collections.preferred_labels.name',
        'ca_list_items.preferred_labels.name_plural',

        // Media
        'ca_object_representations.media.small.url',
        'ca_object_representations.media.medium.url',
        'ca_object_representations.media.original.url',

        // Links
        'ca_objects.external_link',
    ];

    public function __construct(
        private readonly CollectiveAccessGraphQLService $collectiveAccess,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Base URL of CollectiveAccess instance (default: CA_BASE_URL)')]
        string $baseUrl = '',
        #[Argument('Output JSONL file path')]
        string $output = 'var/ca_objects.jsonl',
        #[Option('CA username/email (default: CA_USERNAME)')]
        string $username = '',
        #[Option('CA password (default: CA_PASSWORD)')]
        string $password = '',
        #[Option('Records per page')]
        int $limit = 100,
        #[Option('Maximum total records (0 = all)')]
        int $max = 0,
        #[Option('Search query')]
        string $search = '*',
        #[Option('Bundles to fetch (comma-separated, empty = defaults)')]
        string $bundles = '',
    ): int {
        $io->title('CollectiveAccess GraphQL Fetcher');

        // Parse bundles
        $bundleList = $bundles !== ''
            ? array_map('trim', explode(',', $bundles))
            : self::DEFAULT_BUNDLES;

        $io->info(sprintf('Fetching %d bundle types', count($bundleList)));

        // Get total count first
        $io->section('Fetching object count...');

        try {
            $countResult = $this->collectiveAccess->searchObjects(
                search: $search,
                bundles: $bundleList,
                start: 0,
                limit: 1,
                baseUrl: $baseUrl,
                username: $username,
                password: $password,
            );
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        if (!isset($countResult['data']['search']['count'])) {
            $io->error('Failed to get count: ' . json_encode($countResult['errors'] ?? $countResult));
            return Command::FAILURE;
        }

        $totalCount = (int) $countResult['data']['search']['count'];

        if ($totalCount === 0) {
            $io->warning('No objects found');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d objects', $totalCount));

        $fetchCount = $max > 0 ? min($max, $totalCount) : $totalCount;
        $io->info(sprintf('Will fetch %d objects', $fetchCount));

        // Open JSONL writer
        $writer = JsonlWriter::open($output);

        $start = 0;
        $fetched = 0;
        $progressBar = $io->createProgressBar($fetchCount);
        $progressBar->start();

        while ($fetched < $fetchCount) {
            $batchLimit = min($limit, $fetchCount - $fetched);

            try {
                $result = $this->collectiveAccess->searchObjects(
                    search: $search,
                    bundles: $bundleList,
                    start: $start,
                    limit: $batchLimit,
                    baseUrl: $baseUrl,
                    username: $username,
                    password: $password,
                );
            } catch (\Throwable $e) {
                $io->newLine();
                $io->warning('Request failed at offset ' . $start . ': ' . $e->getMessage());
                break;
            }

            if (!isset($result['data']['search']['results'][0]['result'])) {
                $io->newLine();
                $io->warning('No results at offset ' . $start . ': ' . json_encode($result['errors'] ?? []));
                break;
            }

            $records = $result['data']['search']['results'][0]['result'];

            if (empty($records)) {
                break;
            }

            foreach ($records as $record) {
                $writer->write($this->flattenRecord($record));
                $fetched++;
                $progressBar->advance();

                if ($fetched >= $fetchCount) {
                    break;
                }
            }

            $start += $batchLimit;
        }

        $progressBar->finish();
        $writer->close();

        $io->newLine(2);
        $io->success(sprintf('Wrote %d records to %s', $fetched, $output));

        return Command::SUCCESS;
    }

    /**
     * Flatten the nested bundles structure into a simple key-value array
     */
    private function flattenRecord(array $record): array
    {
        $flat = [
            'id' => $record['id'],
            'idno' => $record['idno'],
            'table' => $record['table'],
        ];

        if (!isset($record['bundles'])) {
            return $flat;
        }

        foreach ($record['bundles'] as $bundle) {
            $code = $bundle['code'] ?? $bundle['name'];
            $dataType = $bundle['dataType'] ?? null;
            $values = [];

            foreach ($bundle['values'] ?? [] as $val) {
                $value = $val['value'] ?? null;
                if ($value !== null && $value !== '') {
                    $values[] = $value;
                }
            }

            if (empty($values)) {
                continue;
            }

            // Simplify key name (remove ca_objects. prefix)
            $key = preg_replace('/^ca_objects\./', '', $code);

            // Store as single value if only one, otherwise as array
            $flat[$key] = count($values) === 1 ? $values[0] : $values;
        }

        return $flat;
    }
}
