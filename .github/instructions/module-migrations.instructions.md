---
applyTo: '**'
---

# Module Migrations in NexoPOS Core

NexoPOS Core provides a dedicated command to run migrations for specific modules.

## Command Usage

```bash
php artisan module:migrate {module}
```

### Parameters
- `{module}`: The module namespace (e.g., `MyModule`)

## Migration Location

Module migrations are stored in the `Migrations/` directory within each module:

```
modules/ModuleName/
└── Migrations/
    ├── 2024_10_31_120000_create_users_table.php
    ├── 2024_10_31_120001_update_settings_table.php
    └── 2024_10_31_120002_add_indexes_to_orders_table.php
```

## Migration Naming Conventions

Migration files **MUST** follow Laravel's standard timestamped naming convention:

```
YYYY_MM_DD_HHMMSS_descriptive_migration_name.php
```

**Examples:**

- **Create operations**: `2024_10_31_120000_create_{table_name}_table.php`
- **Update operations**: `2024_10_31_120001_add_{column}_to_{table}_table.php`
- **Modify operations**: `2024_10_31_120002_modify_{column}_on_{table}_table.php`
- **Data migrations**: `2024_10_31_120003_migrate_{description}_data.php`

## Migration Structure

Module migrations follow Laravel 11+ conventions using **anonymous classes**:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('module_example')) {
            Schema::create('module_example', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_example');
    }
};
```

**Key Requirements:**
- ✅ Use timestamp-based filenames (YYYY_MM_DD_HHMMSS_description.php)
- ✅ Return anonymous class instance
- ✅ Extend `Migration` class
- ✅ Implement both `up()` and `down()` methods
- ✅ Use type hints (`: void`)

## Safety Checks

Always include existence checks in your migrations:

### Table Creation
```php
public function up(): void
{
    if (!Schema::hasTable('table_name')) {
        Schema::create('table_name', function (Blueprint $table) {
            // table definition
        });
    }
}
```

### Column Addition
```php
public function up(): void
{
    Schema::table('existing_table', function (Blueprint $table) {
        if (!Schema::hasColumn('existing_table', 'new_column')) {
            $table->string('new_column')->nullable();
        }
    });
}
```

### Index Creation
```php
public function up(): void
{
    Schema::table('table_name', function (Blueprint $table) {
        if (!$this->indexExists('table_name', 'index_name')) {
            $table->index('column_name', 'index_name');
        }
    });
}

private function indexExists($table, $index)
{
    $sm = Schema::getConnection()->getDoctrineSchemaManager();
    $indexes = $sm->listTableIndexes($table);
    return array_key_exists($index, $indexes);
}
```

## Migration Tracking

- Module migrations are tracked separately from main application migrations
- Migration status is stored in the `module_migrations` table
- Each module maintains its own migration history

## Best Practices

1. **Always check existence** before creating/modifying database objects
2. **Use descriptive names** that clearly indicate the operation
3. **Include rollback logic** in the `down()` method
4. **Test migrations** thoroughly before deployment
5. **Use transactions** for complex operations:

```php
public function up(): void
{
    DB::transaction(function () {
        // Multiple related operations
    });
}
```

## Common Patterns

### Foreign Key Constraints
```php
Schema::table('child_table', function (Blueprint $table) {
    if (!$this->foreignKeyExists('child_table', 'parent_id_foreign')) {
        $table->foreign('parent_id')->references('id')->on('parent_table');
    }
});
```

### Seeding Data
```php
public function up(): void
{
    // Create table first
    if (!Schema::hasTable('settings')) {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
        });
    }
    
    // Seed default data
    DB::table('settings')->insertOrIgnore([
        ['key' => 'module_enabled', 'value' => 'true'],
        ['key' => 'module_version', 'value' => '1.0.0'],
    ]);
}
```

## Generating Migration Files

You can generate timestamped migration files using Laravel's artisan command:

```bash
# Create a new migration
php artisan make:migration create_example_table --path=modules/ModuleName/Migrations

# Create a migration to modify a table  
php artisan make:migration add_status_to_products_table --path=modules/ModuleName/Migrations
```

This automatically creates files with proper timestamps and anonymous class structure.
