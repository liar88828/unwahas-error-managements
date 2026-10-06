<?php

namespace Unwahas\ErrorRedirect\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $app
 * @property string $class
 * @property string $message
 * @property string $file
 * @property int $line
 * @property string $trace
 * @property string|null $url
 * @property string|null $method
 * @property int|null $user_id
 * @property string|null $ip
 * @property int $count
 * @property array<string, mixed>|null $context
 * @property Carbon|null $resolved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ErrorLog extends Model
{
    protected $table = 'error_logs';

    protected $guarded = ['id'];

    public function getTable(): string
    {
        return config('error-redirect.table', parent::getTable());
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'line' => 'integer',
            'user_id' => 'integer',
            'count' => 'integer',
            'context' => 'array',
            'resolved_at' => 'datetime',
        ];
    }
}
