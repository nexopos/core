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
    ├── CreateUsersTable.php
    ├── UpdateSettingsTable.php
    └── AddIndexesToOrdersTable.php
```

## Migration Naming Conventions

- **Create operations**: Start with "Create" (e.g., `CreateUsersTable.php`)
- **Update operations**: Start with "Update" (e.g., `UpdateSettingsTable.php`)
- **Other operations**: Descriptive names (e.g., `AddIndexesToOrdersTable.php`)

## Migration Structure

Module migrations follow Laravel conventions:

```php
<?php
namespace Modules\ModuleName\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExampleTable extends Migration
{
    public function up()
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

    public function down()
    {
        Schema::dropIfExists('module_example');
    }
}
```

## Safety Checks

Always include existence checks in your migrations:

### Table Creation
```php
public function up()
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
public function up()
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
public function up()
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
public function up()
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
public function up()
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
