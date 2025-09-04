---
applyTo: '**'
---

# Events and Listeners in NexoPOS Core

NexoPOS Core supports Laravel's event system with automatic listener discovery, making it easy to create decoupled, event-driven architecture.

## Creating Events

Events are stored in the `Events/` directory and follow Laravel conventions:

```php
<?php
namespace Modules\ModuleName\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEnrolledEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct( public $user, public $course )
    {
        // ...
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
            new PrivateChannel('course.' . $this->course->id),
        ];
    }
}
```

## Simple Event (Non-Broadcast)

```php
<?php
namespace Modules\ModuleName\Events;

class CourseCompletedEvent
{
    public function __construct( public $user, public $course )
    {
        // ...
    }
}
```

## Creating Listeners

Listeners are stored in the `Listeners/` directory and handle events automatically:

```php
<?php
namespace Modules\ModuleName\Listeners;

use Modules\ModuleName\Events\UserEnrolledEvent;
use Modules\ModuleName\Services\NotificationService;
use Modules\ModuleName\Services\CertificateService;

class UserEnrolledListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserEnrolledEvent $event): void
    {
        // Handle Event
    }
}
```

## NexoPOS Core Event Listener

Listening to NexoPOS Core events:

```php
<?php
namespace Modules\ModuleName\Listeners;

use Ns\Events\RenderFooterEvent;

class RenderFooterEventListener
{
    /**
     * Handle the event.
     */
    public function handle(RenderFooterEvent $event): void
    {
        // Only inject on specific pages
        if ($event->routeName === 'ns.dashboard.home') {
            $event->output->addView('ModuleName::widgets.dashboard-widget');
        }

        if (str_starts_with($event->routeName, 'modulename.')) {
            $event->output->addView('ModuleName::partials.module-scripts');
        }
    }
}
```

## Queued Event Listeners

For heavy processing, implement `ShouldQueue`:

```php
<?php
namespace Modules\ModuleName\Listeners;

use Modules\ModuleName\Events\CourseCompletedEvent;
use Modules\ModuleName\Services\CertificateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateCertificateListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public $backoff = [1, 5, 10];

    /**
     * Handle the event.
     */
    public function handle(CourseCompletedEvent $event): void
    {
        // Generate certificate (heavy operation)
        $certificate = CertificateService::generate(
            $event->user,
            $event->course,
            $event->completionData
        );

        // Send certificate via email
        \Mail::to($event->user)->send(
            new \Modules\ModuleName\Mail\CertificateMail($certificate)
        );
    }

    /**
     * Determine if the listener should be queued.
     */
    public function shouldQueue(CourseCompletedEvent $event): bool
    {
        // Only queue for certain courses
        return $event->course->requires_certificate;
    }
}
```

## Broadcasting Events

For real-time updates:

```php
<?php
namespace Modules\ModuleName\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveLessonStartedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct( public $lesson,  public $instructor)
    {
        // ...
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('lesson.' . $this->lesson->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'lesson.started';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'lesson_id' => $this->lesson->id,
            'lesson_title' => $this->lesson->title,
            'instructor_name' => $this->instructor->name,
            'started_at' => now()->toISOString(),
        ];
    }
}
```

## Common NexoPOS Core Events

### Core System Events
- `Ns\Events\RenderFooterEvent` - Footer rendering
- `Ns\Events\RenderHeaderEvent` - Header rendering
- `Ns\Events\ModulesLoadedEvent` - All modules loaded

### User Events
- `Ns\Events\UserCreatedEvent` - User created
- `Ns\Events\UserUpdatedEvent` - User updated
- `Ns\Events\UserDeletedEvent` - User deleted

### Module Events
- `Ns\Events\ModuleEnabledEvent` - Module enabled
- `Ns\Events\ModuleDisabledEvent` - Module disabled

## Event Discovery

NexoPOS Core automatically discovers event listeners. No manual registration required in service providers unless you need specific configuration.

## Best Practices

1. **Keep listeners focused**: One responsibility per listener
2. **Use queues for heavy work**: Implement `ShouldQueue` for time-consuming tasks
3. **Handle failures gracefully**: Implement `failed()` method for queued listeners
4. **Use descriptive names**: Events should clearly describe what happened
5. **Include relevant data**: Pass all necessary data in event constructor
6. **Document events**: Clearly document when events are dispatched
7. **Test thoroughly**: Write tests for both events and listeners