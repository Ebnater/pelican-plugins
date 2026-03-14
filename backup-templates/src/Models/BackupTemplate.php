<?php

namespace Ebnater\BackupTemplates\Models;

use App\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property bool $is_default
 * @property string|null $ignored
 * @property Server $server
 */
class BackupTemplate extends Model
{
    protected $fillable = [
        'server_id',
        'name',
        'is_default',
        'ignored',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'bool',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $backupTemplate): void {
            if (!$backupTemplate->is_default) {
                return;
            }

            self::query()
                ->where('server_id', $backupTemplate->server_id)
                ->whereKeyNot($backupTemplate->getKey())
                ->where('is_default', true)
                ->update(['is_default' => false]);
        });
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }
}
