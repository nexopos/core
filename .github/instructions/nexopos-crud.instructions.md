---
applyTo: '**'
---

# NexoPOS CRUD System Guide

The NexoPOS CRUD (Create, Read, Update, Delete) system provides a powerful and flexible framework for managing data entities throughout the application. This comprehensive guide explains how the CRUD system works, its architecture, and how to implement custom CRUD resources.

## Architecture Overview

The NexoPOS CRUD system consists of several interconnected components:

- **CrudService**: Core service class that handles CRUD operations
- **CrudEntry**: Wrapper class for individual data entries
- **CrudController**: HTTP controller that handles CRUD requests
- **CrudScope**: Attribute for defining scoped CRUD resources
- **CrudForm**: Form configuration helper class
- **Vue Components**: Frontend components for rendering CRUD interfaces

## Core Components

### 1. CrudService

**Location**: `vendor/nexopos/core/src/Services/CrudService.php`

The CrudService is the backbone of the CRUD system. All CRUD classes must extend this service.

#### Key Properties

```php
protected $features = [
    'bulk-actions' => true,     // Enable bulk operations
    'single-action' => true,    // Enable single row actions
    'checkboxes' => true,       // Enable row selection
];

protected $table;              // Database table name
protected $model;              // Eloquent model class
protected $namespace;          // Unique CRUD identifier
protected $mainRoute;          // Base route name
protected $permissions = [];   // Required permissions
protected $columns = [];       // Table columns configuration
protected $actions = [];       // Row actions
protected $bulkActions = [];   // Bulk operations
protected $relations = [];     // Database relations
protected $pick = [];          // Fields to pick from relations
protected $fillable = [];      // Fillable fields for forms
protected $casts = [];         // Field casting configuration
```

#### Essential Constants

```php
const AUTOLOAD = true;        // Auto-register CRUD
const IDENTIFIER = 'unique.crud.name';  // Unique identifier
```

#### Core Methods

```php
// Get paginated entries
public function getEntries($config = []): array

// Get form configuration
public function getForm($entry = null): array

// Get table configuration
public function getTable(): array

// Submit form data
public function submit($inputs, $id = null)

// Handle permissions
public function allowedTo(string $permission): void
```

### 2. CrudEntry

**Location**: `vendor/nexopos/core/src/Services/CrudEntry.php`

Represents individual data rows with additional metadata and functionality.

#### Properties

```php
public $values;           // Row data
public $__raw;           // Raw database values
private $original;       // Original data
public $checked = false; // Selection state
public $toggled = false; // Expanded state
public $id;             // Record ID
public $cssClass;       // CSS classes
```

#### Methods

```php
// Add CSS class to entry
public function addClass(string $class): void

// Magic methods for property access
public function __get($index)
public function __set($index, $value)
public function __isset($index)
```

### 3. CrudController

**Location**: `vendor/nexopos/core/src/Http/Controllers/Dashboard/CrudController.php`

Handles HTTP requests for CRUD operations.

#### Key Methods

```php
// Delete single entry
public function crudDelete($namespace, $id)

// Create new entry
public function crudPost(string $identifier, CrudPostRequest $request)

// Update existing entry
public function crudPut(string $identifier, $id, CrudPutRequest $request)

// Bulk operations
public function crudBulkActions(Request $request, $namespace)

// Export data
public function crudExport(Request $request, $namespace)
```

### 4. CrudForm

**Location**: `vendor/nexopos/core/src/Classes/CrudForm.php`

Helper class for building form configurations.

#### Methods

```php
// Create form structure
public static function form($main, $tabs): array

// Create tabs configuration
public static function tabs(...$args): array

// Create individual tab
public static function tab(
    string $identifier,
    string $label,
    array $fields = [],
    array $notices = [],
    string $component = '',
    array $footer = [],
    ?callable $show = null
): array
```

### 5. Vue Components

#### ns-crud.vue

**Location**: `resources/ts/components/ns-crud.vue`

Main CRUD table component with features:
- Sortable columns
- Search functionality
- Bulk actions
- Pagination
- Row selection
- Export functionality
- Custom filters

#### ns-crud-form.vue

**Location**: `resources/ts/components/ns-crud-form.vue`

Form component for creating/editing entries:
- Tab-based forms
- Field validation
- Dynamic field loading
- Popup support
- Auto-save functionality

## Creating a CRUD Resource

### Step 1: Create CRUD Class

Create a new CRUD class in `app/Crud/`:

```php
<?php

namespace App\Crud;

use Ns\Classes\CrudForm;
use Ns\Classes\FormInput;
use Ns\Models\Product;
use Ns\Services\CrudService;
use Ns\Services\CrudEntry;

class ProductCrud extends CrudService
{
    /**
     * Define the autoload status
     */
    const AUTOLOAD = true;

    /**
     * Define the identifier
     */
    const IDENTIFIER = 'ns.products';

    /**
     * Define the base table
     */
    protected $table = 'nexopos_products';

    /**
     * Define the namespace
     */
    protected $namespace = 'ns.products';

    /**
     * Model Used
     */
    protected $model = Product::class;

    /**
     * Define permissions
     */
    protected $permissions = [
        'create' => 'create.products',
        'read' => 'read.products',
        'update' => 'update.products',
        'delete' => 'delete.products',
    ];

    /**
     * Adding relations
     */
    public $relations = [
        ['users as author', 'nexopos_products.author', '=', 'author.id'],
        ['nexopos_categories as category', 'nexopos_products.category_id', '=', 'category.id'],
    ];

    /**
     * Pick fields from relations
     */
    public $pick = [
        'author' => ['username'],
        'category' => ['name'],
    ];

    /**
     * Fields which will be filled during post/put
     */
    public $fillable = ['name', 'description', 'category_id', 'status'];

    /**
     * Define exportable columns
     */
    protected $exportColumns = ['id', 'name', 'description', 'created_at'];

    /**
     * Get table configuration
     */
    public function getTable(): array
    {
        return [
            'id' => [
                'label' => __('ID'),
                '$direction' => '',
                '$sort' => false,
                'width' => '80px',
            ],
            'name' => [
                'label' => __('Name'),
                '$direction' => '',
                '$sort' => true,
            ],
            'category_name' => [
                'label' => __('Category'),
                '$direction' => '',
                '$sort' => false,
            ],
            'author_username' => [
                'label' => __('Author'),
                '$direction' => '',
                '$sort' => false,
                'width' => '150px',
            ],
            'created_at' => [
                'label' => __('Created'),
                '$direction' => '',
                '$sort' => true,
                'width' => '150px',
            ],
        ];
    }

    /**
     * Get actions configuration
     */
    public function getActions(): array
    {
        return [
            'edit' => [
                'label' => __('Edit'),
                'namespace' => 'edit',
                'type' => 'GOTO',
                'index' => 'id',
                'url' => ns()->url('/dashboard/products/edit/{id}'),
                'permissions' => 'update.products',
            ],
            'delete' => [
                'label' => __('Delete'),
                'namespace' => 'delete',
                'type' => 'DELETE',
                'url' => ns()->url('/api/crud/ns.products/{id}'),
                'permissions' => 'delete.products',
            ],
        ];
    }

    /**
     * Get bulk actions configuration
     */
    public function getBulkActions(): array
    {
        return [
            [
                'label' => __('Delete Selected'),
                'identifier' => 'delete_selected',
                'url' => ns()->route('ns.api.crud-bulk-actions', ['namespace' => 'ns.products']),
                'permissions' => 'delete.products',
            ],
        ];
    }

    /**
     * Get form configuration
     */
    public function getForm($entry = null): array
    {
        return CrudForm::form(
            main: FormInput::text(
                label: __('Product Name'),
                name: 'name',
                value: $entry->name ?? '',
                validation: 'required|string|max:255',
                description: __('Provide the product name.')
            ),
            tabs: CrudForm::tabs(
                CrudForm::tab(
                    identifier: 'general',
                    label: __('General'),
                    fields: [
                        FormInput::textarea(
                            label: __('Description'),
                            name: 'description',
                            value: $entry->description ?? '',
                            description: __('Provide a description for the product.')
                        ),
                        FormInput::searchSelect(
                            label: __('Category'),
                            name: 'category_id',
                            value: $entry->category_id ?? '',
                            validation: 'required',
                            description: __('Select a category for the product.'),
                            component: 'nsCrudForm',
                            props: [
                                'src' => ns()->url('/api/crud/ns.categories/form-config'),
                                'queryParams' => [
                                    'autoload' => true,
                                ],
                            ]
                        ),
                        FormInput::select(
                            label: __('Status'),
                            name: 'status',
                            value: $entry->status ?? 'available',
                            options: [
                                ['label' => __('Available'), 'value' => 'available'],
                                ['label' => __('Unavailable'), 'value' => 'unavailable'],
                            ],
                            validation: 'required',
                            description: __('Define the product status.')
                        ),
                    ]
                )
            )
        );
    }

    /**
     * Before Delete Hook
     */
    public function beforeDelete($namespace, $id, $model)
    {
        if ($model->orders()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => __('Cannot delete product with existing orders.')
            ], 403);
        }
    }

    /**
     * Hook for customizing entries
     */
    public function hook($query): void
    {
        // Add custom query modifications
        $query->where('status', '!=', 'deleted');
    }

    /**
     * Get bulk actions handler
     */
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $entries = $request->input('entries');

        switch ($action) {
            case 'delete_selected':
                return $this->bulkDelete($entries);
            default:
                return response()->json(['status' => 'error', 'message' => __('Unknown action')]);
        }
    }

    /**
     * Handle bulk delete
     */
    private function bulkDelete($entries)
    {
        $deleted = 0;

        foreach ($entries as $entry) {
            $product = Product::find($entry['id']);
            if ($product && $product->orders()->count() === 0) {
                $product->delete();
                $deleted++;
            }
        }

        return [
            'status' => 'success',
            'message' => sprintf(__('%d products deleted successfully.'), $deleted)
        ];
    }
}
```

### Step 2: Register Routes

Add routes in `routes/web.php` or appropriate route file:

```php
Route::get('/dashboard/products', [ProductController::class, 'list'])
    ->name('ns.dashboard.products')
    ->middleware('ns.permission:read.products');

Route::get('/dashboard/products/create', [ProductController::class, 'create'])
    ->name('ns.dashboard.products.create')
    ->middleware('ns.permission:create.products');

Route::get('/dashboard/products/edit/{product}', [ProductController::class, 'edit'])
    ->name('ns.dashboard.products.edit')
    ->middleware('ns.permission:update.products');
```

### Step 3: Create Controller

```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Crud\ProductCrud;
use Ns\Http\Controllers\DashboardController;

class ProductController extends DashboardController
{
    public function list()
    {
        return ProductCrud::table();
    }

    public function create()
    {
        return ProductCrud::form();
    }

    public function edit(Product $product)
    {
        return ProductCrud::form($product);
    }
}
```

## CRUD Configuration

### Table Configuration

The `getTable()` method defines how data is displayed:

```php
public function getTable(): array
{
    return [
        'column_name' => [
            'label' => __('Display Name'),           // Column header
            '$direction' => '',                      // Sort direction. choices: '', 'asc', 'desc'
            '$sort' => true,                        // Enable sorting
            'width' => '150px',                     // Column width
            'maxWidth' => '200px',                  // Maximum width
            'minWidth' => '100px',                  // Minimum width
        ],
    ];
}
```

### Actions Configuration

Define row-level actions:

```php
// ...
use Ns\Classes\CrudEntry; // must import the class on the top.
// ...

public function getActions( CrudEntry $entry ): CrudEntry
{
    $entry->action(
        label: __('Edit'),
        namespace: 'edit',
        type: 'GOTO',                      // Action type
        url: ns()->url('/edit/{id}'),       // URL pattern
        permissions: 'update.resource'      // Required permission. It's optional, but recommended
    ); 

    $entry->action(
        label: __('Delete'),
        namespace: 'delete',
        type: 'DELETE',                     // HTTP DELETE
        url: ns()->url('/api/crud/resource/{id}'),
        permissions: 'delete.resource',
        confirm: [ // confirmation is required for "GET" and "DELETE" actions
            'message' => __('Are you sure you want to delete this entry?'),
        ]
    );

    // This action is managed by an external script that handle custom actions.
    $entry->action(
        label: __('Custom Action'),
        namespace: 'custom',
        type: 'POPUP',                      // Open in popup
        url: ns()->url('/custom/{id}'),
        permissions: 'custom.resource'
    );

    return $entry;
}
```

### Form Configuration

Use FormInput and CrudForm classes for consistent forms:

```php
public function getForm($entry = null): array
{
    return CrudForm::form(
        main: FormInput::text(
            label: __('Name'),
            name: 'name',
            value: $entry->name ?? '',
            validation: 'required'
        ),
        tabs: CrudForm::tabs(
            CrudForm::tab(
                identifier: 'general',
                label: __('General Information'),
                fields: [
                    FormInput::email(
                        label: __('Email'),
                        name: 'email',
                        value: $entry->email ?? '',
                        validation: 'required|email'
                    ),
                    FormInput::select(
                        label: __('Status'),
                        name: 'status',
                        options: [
                            ['label' => __('Active'), 'value' => 'active'],
                            ['label' => __('Inactive'), 'value' => 'inactive'],
                        ],
                        value: $entry->status ?? 'active'
                    ),
                ]
            ),
            CrudForm::tab(
                identifier: 'advanced',
                label: __('Advanced Options'),
                fields: [
                    FormInput::textarea(
                        label: __('Notes'),
                        name: 'notes',
                        value: $entry->notes ?? ''
                    ),
                ]
            )
        )
    );
}
```

### JSON Format for API Submissions

When submitting forms to CRUD API endpoints for creating or updating records, the JSON payload follows a specific structure based on the form configuration:

#### Structure Rules

1. **Main field**: Submitted directly at the root level with its field name as the key
2. **Tab fields**: Submitted as an object where the tab identifier is the key, containing all fields from that tab

#### Example Form Configuration

Given the following `getForm()` configuration:

```php
public function getForm($entry = null): array
{
    return CrudForm::form(
        main: FormInput::text(
            label: __('Name'),
            name: 'name',
            validation: 'required',
            value: $entry ? $entry->name : null,
        ),
        tabs: CrudForm::tabs(
            CrudForm::tab(
                identifier: 'general',
                label: __('General'),
                fields: CrudForm::fields(
                    FormInput::searchSelect(
                        label: __('Registration Role'),
                        name: 'registration_role',
                        options: Helper::toJsOptions(Role::get(), ['id', 'name']),
                        validation: 'required',
                        value: $entry ? $entry->registration_role : null,
                    ),
                    FormInput::media(
                        label: __('Thumb'),
                        name: 'thumb',
                        value: $entry ? $entry->thumb : null,
                    ),
                    FormInput::text(
                        label: __('Slug'),
                        name: 'slug',
                        value: $entry ? $entry->slug : null,
                    ),
                    FormInput::switch(
                        label: __('Active'),
                        name: 'is_active',
                        options: Helper::kvToJsOptions([
                            '1' => __('Yes'),
                            '0' => __('No'),
                        ]),
                        value: $entry ? $entry->is_active : null,
                    ),
                    FormInput::textarea(
                        label: __('Description'),
                        name: 'description',
                        value: $entry ? $entry->description : null,
                    ),
                )
            ),
            CrudForm::tab(
                identifier: 'advanced',
                label: __('Advanced Options'),
                fields: CrudForm::fields(
                    FormInput::number(
                        label: __('Priority'),
                        name: 'priority',
                        value: $entry ? $entry->priority : 0,
                    ),
                    FormInput::switch(
                        label: __('Featured'),
                        name: 'is_featured',
                        options: Helper::kvToJsOptions([
                            '1' => __('Yes'),
                            '0' => __('No'),
                        ]),
                        value: $entry ? $entry->is_featured : null,
                    ),
                )
            )
        )
    );
}
```

#### Corresponding JSON Payload

**POST to `/api/crud/{namespace}` (Create) or PUT to `/api/crud/{namespace}/{id}` (Update):**

```json
{
  "name": "Springfield University",
  "general": {
    "registration_role": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
    "thumb": "12345",
    "slug": "springfield-university",
    "is_active": "1",
    "description": "A premier educational institution dedicated to excellence in teaching and research."
  },
  "advanced": {
    "priority": 10,
    "is_featured": "0"
  }
}
```

#### Field Value Types by Input Type

| Input Type | Value Type | Example |
|------------|-----------|---------|
| `text`, `email`, `url` | String | `"John Doe"` |
| `number`, `integer` | Number | `42`, `3.14` |
| `textarea` | String | `"Long text content..."` |
| `switch` | String "1" or "0" | `"1"` for Yes, `"0"` for No |
| `select` | String (option value) | `"active"` |
| `searchSelect` | String (ID/UUID) | `"3fa85f64-5717-4562-b3fc-2c963f66afa6"` |
| `media` | String (media ID) | `"12345"` |
| `checkbox` | Boolean | `true` or `false` |
| `hidden` | Any (depends on type) | `false`, `"value"`, `123` |
| `date`, `datetime` | String (ISO 8601) | `"2026-02-16"`, `"2026-02-16T18:30:00Z"` |
| `multiselect` | Array of values | `["option1", "option2"]` |

#### Important Notes

1. **Switch fields**: Always use string values `"1"` or `"0"`, not boolean `true`/`false`
2. **Tab identifiers**: Must exactly match the identifier used in `CrudForm::tab()`
3. **Field names**: Must exactly match the `name` attribute in FormInput definitions
4. **Null values**: Can be omitted from the JSON (handled by `filterPostInputs()` or `filterPutInputs()`)
5. **Validation**: Applies server-side based on the `validation` parameter in form fields

#### Processing the Submission

The submitted JSON is processed through these methods in your CRUD class:

```php
// For POST (Create)
public function filterPostInputs($inputs): array
{
    // Flatten tab data and process
    // $inputs will contain both main field and tab objects
    
    // Example: Extract and process nested tab data
    if (isset($inputs['general'])) {
        $inputs = array_merge($inputs, $inputs['general']);
        unset($inputs['general']);
    }
    
    if (isset($inputs['advanced'])) {
        $inputs = array_merge($inputs, $inputs['advanced']);
        unset($inputs['advanced']);
    }
    
    // Process specific fields
    if (empty($inputs['slug'])) {
        $inputs['slug'] = Str::slug($inputs['name']);
    }
    
    return $inputs;
}

// For PUT (Update)
public function filterPutInputs(array $inputs, $entry)
{
    // Same processing as filterPostInputs
    // $entry contains the existing model instance
    
    return $inputs;
}
```

**Note**: The CRUD system automatically flattens tab objects before passing to `filterPostInputs()` or `filterPutInputs()`, so you receive a single-level array with all field names as keys.

## Advanced Features

### Custom Field Casting

Define how data is processed and displayed:

```php
protected $casts = [
    'created_at' => DateCast::class,
    'price' => CurrencyCast::class,
    'status' => StatusCast::class,
];
```

### Database Relations

Include related data in CRUD operations:

```php
public $relations = [
    ['users as author', 'products.author_id', '=', 'author.id'],
    ['categories as category', 'products.category_id', '=', 'category.id'],
];

public $pick = [
    'author' => ['username', 'email'],
    'category' => ['name'],
];
```

### Query Filters

Add dynamic filtering capabilities:

```php
protected $queryFilters = [
    'status' => [
        'label' => __('Status'),
        'type' => 'select',
        'options' => [
            ['label' => __('All'), 'value' => ''],
            ['label' => __('Active'), 'value' => 'active'],
            ['label' => __('Inactive'), 'value' => 'inactive'],
        ],
    ],
    'category' => [
        'label' => __('Category'),
        'type' => 'searchSelect',
        'component' => 'nsCrudForm',
        'props' => [
            'src' => '/api/categories',
        ],
    ],
];
```

### Bulk Actions

Enable operations on multiple entries:

```php
public function getBulkActions(): array
{
    return [
        [
            'label' => __('Delete Selected'),
            'identifier' => 'delete_selected',
            'url' => ns()->route('ns.api.crud-bulk-actions', [
                'namespace' => $this->namespace
            ]),
            'permissions' => 'delete.resource',
        ],
        [
            'label' => __('Export Selected'),
            'identifier' => 'export_selected',
            'url' => ns()->route('ns.api.crud-export', [
                'namespace' => $this->namespace
            ]),
            'permissions' => 'read.resource',
        ],
    ];
}
```

### Lifecycle Hooks

Customize behavior at different stages:

```php
// Before creating
public function beforePost($inputs)
{
    $inputs['created_by'] = auth()->id();
    return $inputs;
}

// After creating
public function afterPost($inputs, $entry)
{
    // Send notification, log activity, etc.
}

// Before updating
public function beforePut($inputs, $entry)
{
    $inputs['updated_by'] = auth()->id();
    return $inputs;
}

// After updating
public function afterPut($inputs, $entry)
{
    // Clear cache, update related records, etc.
}

// Before deleting
public function beforeDelete($namespace, $id, $model)
{
    if ($model->hasRelatedRecords()) {
        return response()->json([
            'status' => 'error',
            'message' => __('Cannot delete record with dependencies.')
        ], 403);
    }
}

// After deleting
public function afterDelete($namespace, $id, $model)
{
    // Clean up related data, files, etc.
}
```

## Frontend Integration

### Using CRUD in Blade Templates

**Table View:**
```blade
@extends('layout.dashboard')

@section('layout.dashboard.body')
<div class="h-full flex-auto flex flex-col">
    @include('common.dashboard-header')
    <div class="px-4 flex-auto flex flex-col" id="crud-table-container">
        <ns-crud
            src="{{ $src }}"
            create-url="{{ $createUrl }}"
            namespace="{{ $namespace }}">
        </ns-crud>
    </div>
</div>
@endsection
```

**Form View:**
```blade
@extends('layout.dashboard')

@section('layout.dashboard.body')
<div class="h-full flex-auto flex flex-col">
    @include('common.dashboard-header')
    <div class="px-4 flex-auto flex flex-col">
        <ns-crud-form
            src="{{ $src }}"
            submit-url="{{ $submitUrl }}"
            submit-method="{{ $submitMethod }}"
            return-url="{{ $returnUrl }}">
        </ns-crud-form>
    </div>
</div>
@endsection
```

### Vue Component Events

Listen for CRUD events in custom components:

```javascript
// In parent component
export default {
    methods: {
        handleCrudUpdate(event) {
            // Handle entry update
            console.log('Entry updated:', event);
        },
        
        handleCrudDelete(event) {
            // Handle entry deletion
            console.log('Entry deleted:', event);
        },
        
        handleFormSave(event) {
            // Handle form submission
            console.log('Form saved:', event);
        }
    }
}
```

## Best Practices

### 1. Security

- Always define proper permissions
- Validate inputs using Laravel validation rules
- Use middleware for route protection
- Implement proper authorization checks

```php
protected $permissions = [
    'create' => 'create.resource',
    'read' => 'read.resource',
    'update' => 'update.resource',
    'delete' => 'delete.resource',
];
```

### 2. Performance

- Use database indexes on frequently queried columns
- Implement proper pagination
- Cache expensive queries
- Optimize database relations

```php
// Use select to limit returned columns
protected $exportColumns = ['id', 'name', 'email', 'created_at'];

// Cache table columns
if (!empty(Cache::get('table-columns-' . $table))) {
    $columns = Cache::get('table-columns-' . $table);
}
```

### 3. User Experience

- Provide clear column labels
- Add helpful descriptions to form fields
- Implement proper error handling
- Use appropriate field types

```php
FormInput::text(
    label: __('Clear Label'),
    name: 'field_name',
    description: __('Helpful description for the user'),
    validation: 'required|string|max:255'
)
```

### 4. Maintainability

- Use consistent naming conventions
- Document complex logic
- Keep CRUD classes focused and single-purpose
- Use meaningful identifiers and namespaces

```php
const IDENTIFIER = 'ns.products';  // Clear, namespaced identifier
protected $namespace = 'ns.products';  // Consistent with identifier
```

## Troubleshooting

### Common Issues

**1. CRUD not loading:**
- Check if AUTOLOAD is set to true
- Verify the identifier is unique
- Ensure proper permissions are set

**2. Form not submitting:**
- Validate form configuration
- Check submit URL and method
- Verify CSRF token handling

**3. Actions not working:**
- Check action permissions
- Verify URL patterns
- Ensure proper HTTP methods

**4. Relations not loading:**
- Check relation syntax
- Verify table aliases
- Ensure pick configuration is correct

This comprehensive guide covers all aspects of the NexoPOS CRUD system, enabling developers to create powerful, consistent, and maintainable data management interfaces.
