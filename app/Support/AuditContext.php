<?php

namespace App\Support;

/**
 * Per-request "friendly label" for the audit log. Bound as a singleton in
 * AppServiceProvider so a tag set in a controller method is visible to the
 * AuditLogger observer later in the same request. Relies on a fresh container
 * per request (true today, no Octane/Swoole) -- would need an explicit reset
 * per request if that ever changes.
 */
class AuditContext
{
    private ?string $action = null;
    private ?string $description = null;

    public function tag(string $action, ?string $description = null): void
    {
        $this->action = $action;
        $this->description = $description;
    }

    public function consume(): ?object
    {
        if ($this->action === null) {
            return null;
        }

        return (object) ['action' => $this->action, 'description' => $this->description];
    }
}
