---
applyTo: '**'
---

# Models in NexoPOS Core

Models in NexoPOS Core modules follow Laravel conventions but extend `Ns\Models\NsModel` instead of the standard Eloquent model to enable module-specific functionality.

## Creating Module Models

Models are stored in the `Models/` directory and extend `NsModel`:

```php
<?php
namespace Modules\ModuleName\Models;

use Ns\Models\NsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends NsModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'module_courses';

    protected $fillable = [
        'title',
        'description',
        'slug',
        'instructor_id',
        'category_id',
        'level',
        'duration_hours',
        'price',
        'status',
        'featured_image',
        'video_url',
        'requirements',
        'learning_outcomes',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_hours' => 'integer',
        'requirements' => 'array',
        'learning_outcomes' => 'array',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'published_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = \Str::slug($course->title);
            }
        });
    }
}
```

## Relationships

Define relationships following Laravel conventions:

```php
<?php
namespace Modules\ModuleName\Models;

use Ns\Models\NsModel;
use Modules\ModuleName\Models\Category;
use Modules\ModuleName\Models\Enrollment;
use Modules\ModuleName\Models\Lesson;
use Ns\Models\User;

class Course extends NsModel
{
    // ... previous code ...

    /**
     * Get the instructor of the course
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the category of the course
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the enrollments for the course
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the lessons for the course
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    /**
     * Get enrolled users
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments')
                    ->withPivot(['enrolled_at', 'completed_at', 'progress_percentage'])
                    ->withTimestamps();
    }

    /**
     * Get completed enrollments
     */
    public function completedEnrollments()
    {
        return $this->hasMany(Enrollment::class)->where('status', 'completed');
    }
}
```

## Scopes

Add query scopes for common filtering:

```php
<?php
namespace Modules\ModuleName\Models;

use Ns\Models\NsModel;

class Course extends NsModel
{
    // ... previous code ...

    /**
     * Scope: Active courses
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Published courses
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope: Featured courses
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: By level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope: Search by title or description
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }
}
```

## Accessors and Mutators

Add accessors and mutators for data formatting:

```php
<?php
namespace Modules\ModuleName\Models;

use Ns\Models\NsModel;

class Course extends NsModel
{
    // ... previous code ...

    /**
     * Get the formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    /**
     * Get the formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_hours);
        $minutes = ($this->duration_hours - $hours) * 60;
        
        if ($minutes > 0) {
            return "{$hours}h {$minutes}m";
        }
        
        return "{$hours}h";
    }

    /**
     * Get the course excerpt
     */
    public function getExcerptAttribute()
    {
        return \Str::limit(strip_tags($this->description), 150);
    }

    /**
     * Get the course URL
     */
    public function getUrlAttribute()
    {
        return route('modulename.courses.show', $this->slug);
    }

    /**
     * Set the title attribute
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = \Str::slug($value);
    }

    /**
     * Set the requirements attribute
     */
    public function setRequirementsAttribute($value)
    {
        $this->attributes['requirements'] = is_array($value) ? $value : explode("\n", $value);
    }

    /**
     * Set the learning outcomes attribute
     */
    public function setLearningOutcomesAttribute($value)
    {
        $this->attributes['learning_outcomes'] = is_array($value) ? $value : explode("\n", $value);
    }
}
```

## Model Methods

Add business logic methods:

```php
<?php
namespace Modules\ModuleName\Models;

use Ns\Models\NsModel;

class Course extends NsModel
{
    // ... previous code ...

    /**
     * Check if course is published
     */
    public function isPublished()
    {
        return $this->status === 'published' 
            && $this->published_at !== null 
            && $this->published_at <= now();
    }

    /**
     * Check if course is free
     */
    public function isFree()
    {
        return $this->price == 0;
    }

    /**
     * Get enrollment count
     */
    public function getEnrollmentCount()
    {
        return $this->enrollments()->count();
    }

    /**
     * Get completion rate
     */
    public function getCompletionRate()
    {
        $totalEnrollments = $this->enrollments()->count();
        
        if ($totalEnrollments === 0) {
            return 0;
        }

        $completedEnrollments = $this->completedEnrollments()->count();
        
        return round(($completedEnrollments / $totalEnrollments) * 100, 1);
    }

    /**
     * Check if user is enrolled
     */
    public function isUserEnrolled($userId)
    {
        return $this->enrollments()
                    ->where('user_id', $userId)
                    ->exists();
    }

    /**
     * Get user's enrollment
     */
    public function getUserEnrollment($userId)
    {
        return $this->enrollments()
                    ->where('user_id', $userId)
                    ->first();
    }

    /**
     * Calculate average rating
     */
    public function getAverageRating()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    /**
     * Publish the course
     */
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish the course
     */
    public function unpublish()
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
```

## Model Factories

Create factories for testing:

```php
<?php
namespace Modules\ModuleName\Database\Factories;

use Modules\ModuleName\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(3, true),
            'slug' => $this->faker->slug,
            'instructor_id' => 1, // Adjust as needed
            'category_id' => 1, // Adjust as needed
            'level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'duration_hours' => $this->faker->numberBetween(1, 40),
            'price' => $this->faker->randomFloat(2, 0, 500),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'requirements' => $this->faker->sentences(3),
            'learning_outcomes' => $this->faker->sentences(5),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 year', '+1 month'),
        ];
    }

    /**
     * Indicate that the course is published.
     */
    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
                'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ];
        });
    }

    /**
     * Indicate that the course is free.
     */
    public function free()
    {
        return $this->state(function (array $attributes) {
            return [
                'price' => 0,
            ];
        });
    }
}
```

## Shared Resource Models

For shared resources like Users, use the standard Laravel model:

```php
<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Courses this user instructs
     */
    public function instructedCourses()
    {
        return $this->hasMany(\Modules\ModuleName\Models\Course::class, 'instructor_id');
    }

    /**
     * Courses this user is enrolled in
     */
    public function enrolledCourses()
    {
        return $this->belongsToMany(\Modules\ModuleName\Models\Course::class, 'enrollments')
                    ->withPivot(['enrolled_at', 'completed_at', 'progress_percentage'])
                    ->withTimestamps();
    }
}
```

## NsModel Benefits

By extending `NsModel`, your models get:

1. **Module-aware table names**: Other modules can filter table names
2. **Enhanced caching**: Built-in caching mechanisms
3. **Event integration**: Better integration with NexoPOS Core events
4. **Permission integration**: Built-in permission checking capabilities

## Best Practices

1. **Use meaningful names**: Model names should clearly represent the entity
2. **Follow conventions**: Use Laravel naming conventions for everything
3. **Add relationships**: Define all necessary relationships
4. **Use scopes**: Create scopes for common queries
5. **Add business logic**: Keep business logic in models, not controllers
6. **Use factories**: Create factories for testing and seeding
7. **Document methods**: Add clear docblocks for complex methods
8. **Validate data**: Use form requests or model validation
9. **Use soft deletes**: For data that shouldn't be permanently deleted
10. **Cache appropriately**: Cache expensive queries and calculations
