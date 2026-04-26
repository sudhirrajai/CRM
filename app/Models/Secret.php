<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Secret extends Model
{
    use HasUuids;

    protected $fillable = [
        'name', 'type', 'encrypted_data', 'tags', 'url',
        'is_favorite', 'category_id', 'created_by', 'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'encrypted_data' => 'encrypted:array',
            'is_favorite' => 'boolean',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(SecretCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get secret type configuration
     */
    public static function typeConfig(): array
    {
        return [
            'password' => [
                'label' => 'Login / Password',
                'icon' => 'ti-key',
                'color' => '#6366f1',
                'fields' => [
                    ['key' => 'username', 'label' => 'Username', 'type' => 'text'],
                    ['key' => 'password', 'label' => 'Password', 'type' => 'password'],
                    ['key' => 'url', 'label' => 'Website URL', 'type' => 'text'],
                    ['key' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'database' => [
                'label' => 'Database Credentials',
                'icon' => 'ti-database',
                'color' => '#f59e0b',
                'fields' => [
                    ['key' => 'db_type', 'label' => 'Database Type', 'type' => 'select', 'options' => ['MySQL', 'PostgreSQL', 'MongoDB', 'SQLite', 'Redis', 'MSSQL']],
                    ['key' => 'host', 'label' => 'Host', 'type' => 'text'],
                    ['key' => 'port', 'label' => 'Port', 'type' => 'text'],
                    ['key' => 'database', 'label' => 'Database Name', 'type' => 'text'],
                    ['key' => 'username', 'label' => 'Username', 'type' => 'text'],
                    ['key' => 'password', 'label' => 'Password', 'type' => 'password'],
                    ['key' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'email' => [
                'label' => 'Email Account',
                'icon' => 'ti-mail',
                'color' => '#ec4899',
                'fields' => [
                    ['key' => 'email', 'label' => 'Email Address', 'type' => 'text'],
                    ['key' => 'password', 'label' => 'Password', 'type' => 'password'],
                    ['key' => 'smtp_host', 'label' => 'SMTP Host', 'type' => 'text'],
                    ['key' => 'smtp_port', 'label' => 'SMTP Port', 'type' => 'text'],
                    ['key' => 'imap_host', 'label' => 'IMAP Host', 'type' => 'text'],
                    ['key' => 'imap_port', 'label' => 'IMAP Port', 'type' => 'text'],
                    ['key' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'ssh_key' => [
                'label' => 'SSH / Server Login',
                'icon' => 'ti-terminal-2',
                'color' => '#10b981',
                'fields' => [
                    ['key' => 'host', 'label' => 'Host / IP', 'type' => 'text'],
                    ['key' => 'port', 'label' => 'Port', 'type' => 'text'],
                    ['key' => 'username', 'label' => 'Username', 'type' => 'text'],
                    ['key' => 'password', 'label' => 'Password', 'type' => 'password'],
                    ['key' => 'ssh_command', 'label' => 'SSH Command', 'type' => 'text'],
                    ['key' => 'private_key', 'label' => 'Private Key / PEM Content', 'type' => 'textarea'],
                    ['key' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'api_key' => [
                'label' => 'API Key / Token',
                'icon' => 'ti-api',
                'color' => '#8b5cf6',
                'fields' => [
                    ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password'],
                    ['key' => 'api_secret', 'label' => 'API Secret', 'type' => 'password'],
                    ['key' => 'endpoint', 'label' => 'API Endpoint URL', 'type' => 'text'],
                    ['key' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'command' => [
                'label' => 'Command / Script',
                'icon' => 'ti-code',
                'color' => '#06b6d4',
                'fields' => [
                    ['key' => 'command', 'label' => 'Command', 'type' => 'textarea'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'text'],
                    ['key' => 'notes', 'label' => 'Notes', 'type' => 'textarea'],
                ],
            ],
            'note' => [
                'label' => 'Secure Note',
                'icon' => 'ti-note',
                'color' => '#f97316',
                'fields' => [
                    ['key' => 'content', 'label' => 'Note Content', 'type' => 'textarea'],
                ],
            ],
            'custom' => [
                'label' => 'Custom Secret',
                'icon' => 'ti-lock',
                'color' => '#64748b',
                'fields' => [], // dynamic fields
            ],
        ];
    }
}
