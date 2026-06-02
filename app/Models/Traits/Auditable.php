<?php

namespace App\Models\Traits;

use App\Helpers\AuditHelper;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $model->auditCreate();
        });

        static::updated(function (Model $model) {
            $model->auditUpdate();
        });

        static::deleted(function (Model $model) {
            $model->auditDelete();
        });
    }

    protected function auditCreate(): void
    {
        AuditHelper::log(
            'CREATE',
            $this->getAuditEntityType(),
            $this->getKey(),
            class_basename($this) . ' created',
            null,
            $this->getAuditValues()
        );
    }

    protected function auditUpdate(): void
    {
        $dirty = $this->getDirty();
        if ($this->getAuditIgnoreAttributes($dirty)) {
            return;
        }

        AuditHelper::log(
            'UPDATE',
            $this->getAuditEntityType(),
            $this->getKey(),
            class_basename($this) . ' updated',
            $this->getOriginalAuditValues(),
            $this->getAuditValues()
        );
    }

    protected function auditDelete(): void
    {
        AuditHelper::log(
            'DELETE',
            $this->getAuditEntityType(),
            $this->getKey(),
            class_basename($this) . ' deleted',
            $this->getAuditValues(),
            null
        );
    }

    protected function getAuditEntityType(): string
    {
        return $this->auditEntityType ?? $this->getTable();
    }

    protected function getAuditValues(): ?array
    {
        return $this->toArray();
    }

    protected function getOriginalAuditValues(): ?array
    {
        return $this->getOriginal();
    }

    protected function getAuditIgnoreAttributes(array $dirty): bool
    {
        $ignored = ['updated_at', 'created_at', 'last_login'];
        return count(array_diff(array_keys($dirty), $ignored)) === 0;
    }
}
