<?php

namespace App\Traits;

use Filament\Notifications\Notification;

trait HasConcurrentEditingProtection
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['original_updated_at'] = $this->getRecordHash();

        return $data;
    }

    protected function beforeSave(): void
    {
        $originalUpdatedAt = $this->data['original_updated_at'] ?? null;
        $recordUpdatedAt = $this->getRecordHash();

        if ($originalUpdatedAt && $originalUpdatedAt !== $recordUpdatedAt) {
            Notification::make()
                ->title('Record Conflict')
                ->body('This record has been modified by another user. Please refresh and try again.')
                ->warning()
                ->send();

            $this->halt();
        }

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
