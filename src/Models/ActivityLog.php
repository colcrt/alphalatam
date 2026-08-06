<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Model;

class ActivityLog extends Model
{
    protected string $table = 'activity_log';

    protected array $fillable = [
        'log_name',
        'description',
        'subject_id',
        'subject_type',
        'causer_id',
        'causer_type',
        'properties',
    ];

    protected array $casts = [
        'properties' => 'array',
    ];

    public function subject(): ?Model
    {
        if (!isset($this->attributes['subject_type'], $this->attributes['subject_id'])) {
            return null;
        }
        $class = $this->attributes['subject_type'];
        return $class::find($this->attributes['subject_id']);
    }

    public function causer(): ?Model
    {
        if (!isset($this->attributes['causer_type'], $this->attributes['causer_id'])) {
            return null;
        }
        $class = $this->attributes['causer_type'];
        return $class::find($this->attributes['causer_id']);
    }
}
