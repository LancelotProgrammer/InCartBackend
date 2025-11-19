<?php

namespace App\Traits;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

trait HasConcurrentEditingProtection
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['original_updated_at'] = $this->getRecordHash();
        
        Log::channel('app_log')->info('Traits(HasConcurrentEditingProtection): mutating form data before fill to create hash for concurrency check', [
            'data' => $data,
            'originalUpdatedAt' => $data['original_updated_at'],
        ]);

        return $data;
    }

    protected function beforeSave(): void
    {
        $originalUpdatedAt = $this->data['original_updated_at'] ?? null;
        $recordUpdatedAt = $this->getRecordHash();

        Log::channel('app_log')->info('Traits(HasConcurrentEditingProtection): checking concurrency', [
            'originalUpdatedAt' => $originalUpdatedAt,
            'recordUpdatedAt' => $recordUpdatedAt,
        ]);

        if ($originalUpdatedAt && $originalUpdatedAt !== $recordUpdatedAt) {
            Log::channel('app_log')->warning('Traits(HasConcurrentEditingProtection): concurrency detected', [
                'originalUpdatedAt' => $originalUpdatedAt,
                'recordUpdatedAt' => $recordUpdatedAt,
                'condition' => $originalUpdatedAt && $originalUpdatedAt !== $recordUpdatedAt,
            ]);

            Notification::make()
                ->title('Record Conflict')
                ->body('This record has been modified by another user. Please refresh and try again.')
                ->warning()
                ->send();

            $this->halt();
        }

        Log::channel('app_log')->info('Traits(HasConcurrentEditingProtection): concurrency check passed', [
            'originalUpdatedAt' => $originalUpdatedAt,
            'recordUpdatedAt' => $recordUpdatedAt,
        ]);

        unset($this->data['original_updated_at']);
    }

    protected function getRecordHash(): ?string
    {
        if (!$this->record) {
            return null;
        }

        $values = $this->getConcurrencyAttributes();

        return sha1(json_encode($values));
    }

    protected function getConcurrencyAttributes(): array
    {
        return [
            $this->record?->updated_at?->toDateTimeString(),
        ];
    }
}
