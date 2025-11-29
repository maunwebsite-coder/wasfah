<?php

namespace App\Console\Commands;

use App\Models\Workshop;
use App\Services\UserGoogleDriveService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SyncWorkshopRecordings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workshops:sync-recordings
        {--workshop= : معرّف أو سلاق الورشة المطلوب مزامنتها فقط}
        {--dry-run : تشغيل تجريبي بدون حفظ أي بيانات}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'جلب تسجيلات Google Meet من Google Drive وتحديث رابط التسجيل للورشة.';

    public function __construct(protected UserGoogleDriveService $userGoogleDriveService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = $this->buildEligibleWorkshopsQuery();
        $this->applyWorkshopFilter($query);

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('لا توجد ورشات بحاجة إلى مزامنة التسجيل حالياً.');

            return Command::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $synced = 0;
        $processed = 0;
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        foreach ($query->lazyById(25) as $workshop) {
            ++$processed;
            $workshop->loadMissing('chef');
            $chef = $workshop->chef;
            $recordingUrl = null;

            if ($chef && $chef->hasGoogleDriveCredentials()) {
                $recordingUrl = $this->userGoogleDriveService->findRecordingUrl($chef, $workshop->meeting_code);
            }

            if (! $recordingUrl) {
                $progressBar->advance();

                continue;
            }

            if ($dryRun) {
                $this->newLine();
                $this->line(sprintf(
                    'سيتم ربط التسجيل بالورشة #%d (%s): %s',
                    $workshop->id,
                    $workshop->title,
                    $recordingUrl
                ));
            } else {
                $workshop->forceFill(['recording_url' => $recordingUrl])->save();

                Log::info('Workshop recording synced from Google Drive.', [
                    'workshop_id' => $workshop->id,
                    'meeting_code' => $workshop->meeting_code,
                    'recording_url' => $recordingUrl,
                ]);
            }

            ++$synced;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info(sprintf('تمت مزامنة %d من أصل %d ورشات مؤهلة.', $synced, $processed));

        if ($synced === 0) {
            $this->warn('لم يتم العثور على أي تسجيلات مطابقة. سيتم المحاولة مرة أخرى في الدورة القادمة.');
        }

        return Command::SUCCESS;
    }

    protected function buildEligibleWorkshopsQuery(): Builder
    {
        $cutoff = now()->subMinutes(30);

        return Workshop::query()
            ->with('chef')
            ->where('is_online', true)
            ->whereNotNull('meeting_code')
            ->where('meeting_code', '!=', '')
            ->whereNull('recording_url')
            ->where(function (Builder $builder) use ($cutoff) {
                $builder
                    ->where(function (Builder $inner) use ($cutoff) {
                        $inner->whereNotNull('end_date')
                            ->where('end_date', '<=', $cutoff);
                    })
                    ->orWhere(function (Builder $inner) use ($cutoff) {
                        $inner->whereNull('end_date')
                            ->whereNotNull('start_date')
                            ->where('start_date', '<=', $cutoff);
                    });
            });
    }

    protected function applyWorkshopFilter(Builder $query): void
    {
        $workshopOption = $this->option('workshop');

        if (! $workshopOption) {
            return;
        }

        $query->where(function (Builder $builder) use ($workshopOption) {
            if (is_numeric($workshopOption)) {
                $builder->where('id', (int) $workshopOption);

                return;
            }

            $builder->where('slug', (string) $workshopOption);
        });
    }
}
