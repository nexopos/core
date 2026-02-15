---
applyTo: '**'
---

# NexoPOS CRUD System Guide (Accurate)

The NexoPOS CRUD (Create, Read, Update, Delete) system provides a powerful framework for managing data entities. This guide is based on actual CRUD implementations in the codebase.

## Architecture Overview

The CRUD system consists of:

- **CrudService**: Base service class (`vendor/nexopos/core/src/Services/CrudService.php`)
- **CrudEntry**: Wrapper class for individual data entries (`vendor/nexopos/core/src/Services/CrudEntry.php`)
- **CrudController**: HTTP controller (`vendor/nexopos/core/src/Http/Controllers/Dashboard/CrudController.php`)
- **CrudTable**: Helper for table configuration (`vendor/nexopos/core/src/Classes/CrudTable.php`)
- **CrudForm**: Helper for form configuration (`vendor/nexopos/core/src/Classes/CrudForm.php`)
- **FormInput**: Helper for field configuration (`vendor/nexopos/core/src/Classes/FormInput.php`)

## Core CRUD Class Structure

### Essential Constants and Properties

```php
<?php

namespace App\Crud;

use Ns\Services\CrudService;
use Ns\Services\CrudEntry;

class ExampleCrud extends CrudService
{
    /**
     * Auto-register CRUD class
     */
    const AUTOLOAD = true;

    /**
     * Unique identifier for the CRUD
     */
    const IDENTIFIER = 'ns.example';

    /**
     * Database table name
     */
    protected $table = 'nexopos_examples';

    /**
     * Route slug for dashboard URLs
     */
    protected $slug = 'examples';

    /**
     * Unique namespace identifier
     */
    protected $namespace = 'ns.examples';

    /**
     * Eloquent model class
     */
    protected $model = Example::class;

    /**
     * Permissions configuration
     */
    protected $permissions = [
        'create' => 'nexopos.create.examples',
        'read' => 'nexopos.read.examples',
        'update' => 'nexopos.update.examples',
        'delete' => 'nexopos.delete.examples',
    ];

    /**
     * Display options column before data columns
     */
    protected $prependOptions = false;

    /**
     * Show/hide the options column
     */
    protected $showOptions = true;
}
```

## Database Relations

### Standard Relations

Relations are defined in the `$relations` property:

```php
public $relations = [
    // Simple join
    ['users as author', 'nexopos_examples.author_id', '=', 'author.id'],
    
    // Left join
    'leftJoin' => [
        ['nexopos_categories as category', 'nexopos_examples.category_id', '=', 'category.id'],
    ],
];
```

### Picking Specific Columns

Use the `$pick` property to restrict columns from related tables:

```php
public $pick = [
    'author' => ['username', 'email'],
    'category' => ['name'],
];
```

**Without `$pick`**: All columns from related tables are included.  
**With `$pick`**: Only specified columns are retrieved, with prefixed names like `author_username`, `category_name`.

## Column Configuration

### Using CrudTable Helper

The `getColumns()` method defines table columns:

```php
use Ns\Classes\CrudTable;

public function getColumns(): array
{
    return CrudTable::columns(
        CrudTable::column(
            label: __('Name'),
            identifier: 'name',
            width: '200px'
        ),
        CrudTable::column(
            label: __('Status'),
            identifier: 'status',
            width: '100px'
        ),
        CrudTable::column(
            label: __('Author'),
            identifier: 'author_username'
        ),
        CrudTable::column(
            label: __('Created'),
            identifier: 'created_at',
            width: '150px'
        ),
    );
}
```

### Legacy Array Format

```php
public function getColumns(): array
{
    return [
        'name' => [
            'label' => __('Name'),
            '$direction' => '',      // Sort direction: '', 'asc', 'desc'
            '$sort' => false,        // Enable sorting
            'width' => '200px',      // Column width
        ],
        'status' => [
            'label' => __('Status'),
            '$direction' => '',
            '$sort' => true,
        ],
    ];
}
```

## Row Actions Configuration

### Using CrudEntry Methods

The `setActions()` method defines row-level actions:

```php
public function setActions(CrudEntry $entry): CrudEntry
{
    // Edit action - navigate to edit page
    $entry->action(
        identifier: 'edit',
        label: '<i class="mr-2 las la-edit"></i> ' . __('Edit'),
        type: 'GOTO',
        url: nsUrl('/dashboard/' . $this->slug . '/edit/' . $entry->id)
    );

    // Preview in popup
    $entry->action(
        identifier: 'preview',
        label: '<i class="mr-2 las la-eye"></i> ' . __('Preview'),
        type: 'POPUP',
        url: nsUrl('/dashboard/' . $this->slug . '/preview/' . $entry->id)
    );

    // Delete with confirmation
    $entry->action(
        identifier: 'delete',
        label: '<i class="mr-2 las la-trash"></i> ' . __('Delete'),
        type: 'DELETE',
        url: nsUrl('/api/crud/' . self::IDENTIFIER . '/' . $entry->id),
        confirm: [
            'message' => __('Would you like to delete this entry?'),
        ]
    );

    // GET request with confirmation
    $entry->action(
        identifier: 'approve',
        label: '<i class="mr-2 las la-check"></i> ' . __('Approve'),
        type: 'GET',
        url: nsUrl('/api/examples/' . $entry->id . '/approve'),
        confirm: [
            'message' => __('Approve this entry?'),
        ]
    );

    return $entry;
}
```

### Action Types

- **`GOTO`**: Navigate to URL (no confirmation needed)
- **`POPUP`**: Open URL in popup modal
- **`DELETE`**: HTTP DELETE request (requires confirmation)
- **`GET`**: HTTP GET request (requires confirmation)
- **`POST`**: HTTP POST request (requires confirmation)

### Adding CSS Classes to Rows

```php
public function setActions(CrudEntry $entry): CrudEntry
{
    // Add CSS class based on status
    $entry->addClass(match($entry->status) {
        'active' => 'success border',
        'pending' => 'info border',
        'disabled' => 'error border',
        default => ''
    });

    // Or using $cssClass property
    $entry->{'$cssClass'} = 'border text-sm';

    return $entry;
}
```

### Accessing Raw Values

```php
public function setActions(CrudEntry $entry): CrudEntry
{
    // Access original database value before casting
    $rawStatus = $entry->getOriginalValue('status');
    
    // Store for conditional logic
    $entry->rawStatus = $rawStatus;
    
    // Apply conditional actions
    if ($rawStatus === 'pending') {
        $entry->action(
            identifier: 'approve',
            label: __('Approve'),
            // ...
        );
    }

    return $entry;
}
```

## Bulk Actions

### Definition

```php
public function getBulkActions(): array
{
    return [
        [
            'label' => __('Delete Selected'),
            'identifier' => 'delete_selected',
            'url' => ns()->route('ns.api.crud-bulk-actions', [
                'namespace' => $this->namespace,
            ]),
        ],
        [
            'label' => __('Export Selected'),
            'identifier' => 'export_selected',
            'url' => ns()->route('ns.api.crud-export', [
                'namespace' => $this->namespace,
            ]),
        ],
    ];
}
```

### Handling Bulk Actions

```php
use Illuminate\Http\Request;
use Ns\Exceptions\NotAllowedException;

public function bulkAction(Request $request): array
{
    $action = $request->input('action');
    
    if ($action === 'delete_selected') {
        // Check permissions
        if ($this->permissions['delete'] !== false) {
            ns()->restrict($this->permissions['delete']);
        } else {
            throw new NotAllowedException();
        }

        $status = [
            'success' => 0,
            'error' => 0,
        ];

        foreach ($request->input('entries') as $id) {
            $entity = $this->model::find($id);
            if ($entity instanceof Example) {
                $entity->delete();
                $status['success']++;
            } else {
                $status['error']++;
            }
        }

        return $status;
    }

    // Allow hook to catch custom actions
    return Hook::filter($this->namespace . '-catch-action', false, $request);
}
```

## Query Customization

### Hook Method

Customize the query before execution:

```php
public function hook($query): void
{
    // Default ordering
    $query->orderBy('updated_at', 'desc');
    
    // Conditional filtering
    if (!empty(request()->query('status'))) {
        $query->where('status', request()->query('status'));
    }
    
    // Filter by user
    if (!ns()->allowedTo('view.all.examples')) {
        $query->where('author_id', auth()->id());
    }
}
```

### Query Filters

Define dynamic filters for the UI:

```php
use Ns\Services\Helper;

public function __construct()
{
    parent::__construct();
    
    $this->queryFilters = [
        [
            'type' => 'daterangepicker',
            'name' => 'created_at',
            'label' => __('Created Between'),
            'description' => __('Filter by creation date range'),
        ],
        [
            'type' => 'select',
            'name' => 'status',
            'label' => __('Status'),
            'description' => __('Filter by status'),
            'options' => Helper::kvToJsOptions([
                'active' => __('Active'),
                'inactive' => __('Inactive'),
                'pending' => __('Pending'),
            ]),
        ],
        [
            'type' => 'text',
            'name' => 'name',
            'label' => __('Name'),
            'operator' => 'like',
            'description' => __('Search by name'),
        ],
    ];
}
```

## Lifecycle Hooks

### Create Hooks

```php
/**
 * Before creating entry
 */
public function beforePost(array $request): array
{
    $this->allowedTo('create');
    
    // Add automatic fields
    $request['author_id'] = auth()->id();
    $request['status'] = 'pending';
    
    return $request;
}

/**
 * After creating entry
 */
public function afterPost(array $request, Example $entry): array
{
    // Log activity
    // Send notifications
    // Create related records
    
    return $request;
}
```

### Update Hooks

```php
/**
 * Before updating entry
 */
public function beforePut(array $request, Example $entry): array
{
    $this->allowedTo('update');
    
    // Track changes
    $request['updated_by'] = auth()->id();
    
    return $request;
}

/**
 * After updating entry
 */
public function afterPut(array $request, Example $entry): array
{
    // Clear cache
    // Update related records
    
    return $request;
}
```

### Delete Hooks

```php
/**
 * Before deleting entry
 */
public function beforeDelete($namespace, $id, $model): void
{
    if ($namespace === self::IDENTIFIER) {
        if ($this->permissions['delete'] !== false) {
            ns()->restrict($this->permissions['delete']);
        } else {
            throw new NotAllowedException();
        }
        
        // Check for dependencies
        if ($model->hasRelatedRecords()) {
            return response()->json([
                'status' => 'error',
                'message' => __('Cannot delete entry with dependencies'),
            ], 403);
        }
    }
}
```

## CRUD API Endpoints and Request Structure

The NexoPOS CRUD system exposes RESTful API endpoints for all CRUD operations. The API routes are defined in `routes/api/crud.php`.

### API Routes

All CRUD operations use the following route pattern:

```
/api/crud/{namespace}        - Base CRUD endpoint
/api/crud/{namespace}/{id}   - Specific entry endpoint
```

**Available HTTP Methods:**

- **POST** `/api/crud/{namespace}` - Create new entry
- **GET** `/api/crud/{namespace}` - List entries (paginated)
- **GET** `/api/crud/{namespace}/{id}` - Get single entry
- **PUT** `/api/crud/{namespace}/{id}` - Update existing entry
- **DELETE** `/api/crud/{namespace}/{id}` - Delete entry

Where `{namespace}` is the CRUD's `IDENTIFIER` constant (e.g., `ns.products`, `ns.orders`).

### JSON Request Structure

The JSON structure sent to POST and PUT endpoints **directly correlates** with the form structure defined in your CRUD class's `getForm()` method.

#### Form Structure to JSON Mapping

The `getForm()` method defines two types of fields:

1. **Main Field** - Single field from `CrudForm::form(main: ...)` parameter
2. **Tab Fields** - Fields grouped under tabs from `CrudForm::tabs(...)` parameter

**JSON Structure Pattern:**

```json
{
  "main_field_name": "value",
  "tab_identifier": {
    "field_name_1": "value",
    "field_name_2": "value"
  }
}
```

- The **main field** appears at the root level of the JSON
- Each **tab's fields** are nested under an object with the tab's `identifier` as the key
- Field names come from the `name` parameter in `FormInput::*()` methods
- Field values must match the expected data type (string, number, boolean, array)

### Complete Example: School CRUD

#### Form Definition

```php
public function getForm(School | null $entry = null): array
{
    return CrudForm::form(
        main: FormInput::text(
            label: __m('Name', 'SGUniversity'),
            name: 'name',  // ← Root-level field
            validation: 'required',
            value: $entry ? $entry->name : null,
        ),
        tabs: CrudForm::tabs(
            CrudForm::tab(
                identifier: 'general',  // ← Tab identifier becomes JSON key
                label: __m('General', 'SGUniversity'),
                fields: CrudForm::fields(
                    FormInput::searchSelect(
                        label: __m('Registration Role', 'SGUniversity'),
                        name: 'registration_role',  // ← Nested under 'general'
                        options: Helper::toJsOptions(Role::get(), ['id', 'name']),
                        validation: 'required',
                        value: $entry ? $entry->registration_role : null,
                    ),
                    FormInput::text(
                        label: __m('Slug', 'SGUniversity'),
                        name: 'slug',  // ← Nested under 'general'
                        value: $entry ? $entry->slug : null,
                    ),
                    FormInput::switch(
                        label: __m('School Active', 'SGUniversity'),
                        name: 'is_active',  // ← Nested under 'general'
                        options: Helper::kvToJsOptions([
                            '1' => __m('Yes', 'SGUniversity'),
                            '0' => __m('No', 'SGUniversity'),
                        ]),
                        value: $entry ? $entry->is_active : null,
                    ),
                    FormInput::switch(
                        label: __m('Enable Registration', 'SGUniversity'),
                        name: 'allow_registration',  // ← Nested under 'general'
                        options: Helper::kvToJsOptions([
                            '1' => __m('Yes', 'SGUniversity'),
                            '0' => __m('No', 'SGUniversity'),
                        ]),
                        value: $entry ? $entry->allow_registration : null,
                    ),
                    FormInput::textarea(
                        label: __m('Description', 'SGUniversity'),
                        name: 'description',  // ← Nested under 'general'
                        value: $entry ? $entry->description : null,
                    ),
                )
            )
        )
    );
}
```

#### Corresponding JSON Request

**POST** `/api/crud/ns.schools` or **PUT** `/api/crud/ns.schools/123`

```json
{
  "name": "Base School",
  "general": {
    "registration_role": 1,
    "slug": "base-school",
    "is_active": 1,
    "allow_registration": 1,
    "description": "Innovation Starts Here"
  }
}
```

**Request Structure Explanation:**

- `"name"` - Root level because it's the **main** field in `CrudForm::form(main: ...)`
- `"general"` - Object key matches the **tab identifier** `'general'`
- All fields inside `"general"` object correspond to fields in the 'general' tab
- Field names (`registration_role`, `slug`, etc.) match the `name` parameter in `FormInput` methods
- Values must match the expected types:
  - Text fields: strings
  - Switch fields: integers (0 or 1) or booleans
  - Select fields: values from the options
  - Textarea fields: strings

### Multiple Tabs Example

If your form has multiple tabs:

```php
return CrudForm::form(
    main: FormInput::text(name: 'title', label: 'Title'),
    tabs: CrudForm::tabs(
        CrudForm::tab(
            identifier: 'general',
            fields: CrudForm::fields(
                FormInput::text(name: 'subtitle', label: 'Subtitle'),
            )
        ),
        CrudForm::tab(
            identifier: 'advanced',
            fields: CrudForm::fields(
                FormInput::number(name: 'priority', label: 'Priority'),
            )
        )
    )
);
```

**Corresponding JSON:**

```json
{
  "title": "Main Title",
  "general": {
    "subtitle": "Page Subtitle"
  },
  "advanced": {
    "priority": 5
  }
}
```

### POST vs PUT Behavior

**POST** `/api/crud/{namespace}` - **Create New Entry**

- Returns HTTP 201 Created on success
- Returns newly created entry with `id`
- Triggers `beforePost()` and `afterPost()` hooks
- Example Response:
  ```json
  {
    "status": "success",
    "message": "Entry created successfully",
    "data": {
      "id": 123,
      "name": "Base School",
      "...": "..."
    }
  }
  ```

**PUT** `/api/crud/{namespace}/{id}` - **Update Existing Entry**

- Returns HTTP 200 OK on success
- Returns updated entry data
- Triggers `beforePut()` and `afterPut()` hooks
- Entry must exist or returns 404
- Example Response:
  ```json
  {
    "status": "success",
    "message": "Entry updated successfully",
    "data": {
      "id": 123,
      "name": "Updated School Name",
      "...": "..."
    }
  }
  ```

### Field Type Handling

Different `FormInput` types expect different JSON value formats:

| FormInput Type | JSON Value Type | Example |
|----------------|-----------------|---------|
| `text()` | String | `"base-school"` |
| `textarea()` | String | `"Long description..."` |
| `number()` | Number | `100` |
| `switch()` | Integer (0/1) or Boolean | `1` or `true` |
| `select()` | String/Number | `"value"` or `5` |
| `searchSelect()` | String/Number/Array | `1` or `[1, 2, 3]` |
| `hidden()` | String/Number/Boolean | `false` or `0` |
| `date()` | String (ISO format) | `"2024-01-15"` |
| `datetime()` | String (ISO format) | `"2024-01-15T14:30:00Z"` |
| `media()` | String (URL) or Number (ID) | `"https://..."` or `123` |

### Validation and Error Responses

If validation fails, the API returns HTTP 422 Unprocessable Entity:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "general.slug": [
      "The slug has already been taken."
    ],
    "general.is_active": [
      "The is active field must be 0 or 1."
    ]
  }
}
```

**Error Key Format:**
- Root fields: `"fieldname"`
- Tab fields: `"tab_identifier.fieldname"`

### GET Requests

**List Entries** - `GET /api/crud/{namespace}`

Returns paginated list with metadata:

```json
{
  "data": [
    { "id": 1, "name": "School 1", "..." : "..." },
    { "id": 2, "name": "School 2", "..." : "..." }
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 10,
  "total": 47
}
```

**Get Single Entry** - `GET /api/crud/{namespace}/{id}`

Returns single entry:

```json
{
  "data": {
    "id": 123,
    "name": "Base School",
    "general": {
      "slug": "base-school",
      "is_active": 1,
      "description": "Innovation Starts Here"
    }
  }
}
```

### DELETE Request

**Delete Entry** - `DELETE /api/crud/{namespace}/{id}`

Returns confirmation:

```json
{
  "status": "success",
  "message": "Entry deleted successfully"
}
```

- Triggers `beforeDelete()` hook
- HTTP 204 No Content on success
- HTTP 403 if user lacks permission
- HTTP 404 if entry not found

### Using the API in Frontend

**With nsHttpClient:**

```typescript
// Create entry
nsHttpClient.post('/api/crud/ns.schools', {
    name: 'New School',
    general: {
        slug: 'new-school',
        is_active: 1
    }
}).subscribe({
    next: (response) => {
        nsSnackBar.success('School created successfully');
        console.log('Created ID:', response.data.id);
    },
    error: (error) => {
        nsSnackBar.error(error.message);
        // Handle validation errors
        if (error.errors) {
            Object.keys(error.errors).forEach(field => {
                console.error(`${field}: ${error.errors[field][0]}`);
            });
        }
    }
});

// Update entry
nsHttpClient.put('/api/crud/ns.schools/123', {
    name: 'Updated School',
    general: {
        is_active: 0
    }
}).subscribe({
    next: (response) => {
        nsSnackBar.success('School updated successfully');
    },
    error: (error) => {
        nsSnackBar.error('Update failed');
    }
});

// Delete entry
nsHttpClient.delete('/api/crud/ns.schools/123')
    .subscribe({
        next: () => {
            nsSnackBar.success('School deleted');
        },
        error: (error) => {
            nsSnackBar.error('Deletion failed');
        }
    });
```

### Best Practices

1. **Match Form Structure**: Always ensure JSON structure matches your `getForm()` definition
2. **Validate Before Sending**: Use client-side validation matching the `validation` rules
3. **Handle Errors Properly**: Display validation errors to users in a clear format
4. **Use Correct Types**: Send booleans as integers (0/1) for switch fields
5. **Include All Required Fields**: Check `validation: 'required'` in form definition
6. **Test with Postman**: Use Postman to test API endpoints during development
7. **Check Permissions**: Ensure CRUD permissions are properly configured
8. **Handle Tab Nesting**: Remember to nest tab fields under their identifier

## Labels Configuration

Define UI labels:

```php
use Ns\Classes\CrudTable;

public function getLabels(): array
{
    return CrudTable::labels(
        list_title: __('Examples List'),
        list_description: __('Display all examples.'),
        no_entry: __('No examples found'),
        create_new: __('Add New Example'),
        create_title: __('Create Example'),
        create_description: __('Register a new example'),
        edit_title: __('Edit Example'),
        edit_description: __('Modify example details'),
        back_to_list: __('Return to Examples')
    );
}
```

## Links Configuration

Define navigation URLs:

```php
public function getLinks(): array
{
    return [
        'list' => nsUrl('dashboard/' . $this->slug),
        'create' => nsUrl('dashboard/' . $this->slug . '/create'),
        'edit' => nsUrl('dashboard/' . $this->slug . '/edit/'),
        'post' => nsUrl('api/crud/' . $this->namespace),
        'put' => nsUrl('api/crud/' . $this->namespace . '/{id}'),
    ];
}
```

## Data Casting

Use casts to format data for display:

```php
use Ns\Casts\CurrencyCast;
use Ns\Casts\DateCast;

protected $casts = [
    'price' => CurrencyCast::class,
    'created_at' => DateCast::class,
    'status' => StatusCast::class,
];
```

## Complete Example: OrderCrud

Based on the actual `OrderCrud` implementation:

```php
<?php

namespace App\Crud;

use Ns\Casts\CurrencyCast;
use Ns\Casts\DateCast;
use Ns\Casts\OrderDeliveryCast;
use Ns\Casts\OrderPaymentCast;
use Ns\Classes\CrudTable;
use Ns\Exceptions\NotAllowedException;
use Ns\Models\Order;
use Ns\Services\CrudEntry;
use Ns\Services\CrudService;
use Ns\Services\Helper;
use Illuminate\Http\Request;
use TorMorten\Eventy\Facades\Events as Hook;

class OrderCrud extends CrudService
{
    const AUTOLOAD = true;
    const IDENTIFIER = 'ns.orders';

    protected $table = 'nexopos_orders';
    protected $mainRoute = 'ns.orders';
    protected $namespace = 'ns.orders';
    protected $model = Order::class;
    protected $prependOptions = true;

    public $relations = [
        ['users as author', 'nexopos_orders.author', '=', 'author.id'],
        ['users as customer', 'nexopos_orders.customer_id', '=', 'customer.id'],
    ];

    public $pick = [
        'author' => ['username'],
        'customer' => ['first_name', 'phone'],
    ];

    protected $permissions = [
        'create' => 'nexopos.create.orders',
        'read' => 'nexopos.read.orders',
        'update' => 'nexopos.update.orders',
        'delete' => 'nexopos.delete.orders',
    ];

    protected $casts = [
        'total' => CurrencyCast::class,
        'tax_value' => CurrencyCast::class,
        'delivery_status' => OrderDeliveryCast::class,
        'payment_status' => OrderPaymentCast::class,
        'created_at' => DateCast::class,
    ];

    public function getColumns(): array
    {
        return CrudTable::columns(
            CrudTable::column(
                label: __('Code'),
                identifier: 'code',
                width: '170px'
            ),
            CrudTable::column(
                label: __('Type'),
                identifier: 'type',
                width: '100px'
            ),
            CrudTable::column(
                label: __('Customer'),
                identifier: 'customer_first_name',
                width: '100px'
            ),
            CrudTable::column(
                label: __('Payment'),
                identifier: 'payment_status',
                width: '150px'
            ),
            CrudTable::column(
                label: __('Total'),
                identifier: 'total',
                width: '100px'
            ),
            CrudTable::column(
                label: __('Created At'),
                identifier: 'created_at',
                width: '150px'
            ),
        );
    }

    public function setActions(CrudEntry $entry): CrudEntry
    {
        // Apply CSS class based on payment status
        $entry->{'$cssClass'} = match($entry->__raw->payment_status) {
            Order::PAYMENT_PAID => 'success border text-sm',
            Order::PAYMENT_UNPAID => 'danger border text-sm',
            Order::PAYMENT_VOID => 'error border text-sm',
            default => ''
        };

        $entry->action(
            identifier: 'invoice',
            label: '<i class="mr-2 las la-file-invoice-dollar"></i> ' . __('Invoice'),
            url: nsUrl('/dashboard/orders/invoice/' . $entry->id),
        );

        $entry->action(
            identifier: 'receipt',
            label: '<i class="mr-2 las la-receipt"></i> ' . __('Receipt'),
            url: nsUrl('/dashboard/orders/receipt/' . $entry->id),
        );

        $entry->action(
            identifier: 'delete',
            label: '<i class="mr-2 las la-trash"></i> ' . __('Delete'),
            type: 'DELETE',
            url: nsUrl('/api/crud/ns.orders/' . $entry->id),
            confirm: [
                'message' => __('Would you like to delete this order?'),
            ],
        );

        return $entry;
    }

    public function hook($query): void
    {
        if (empty(request()->query('direction'))) {
            $query->orderBy('id', 'desc');
        }
    }

    public function bulkAction(Request $request): array
    {
        if ($request->input('action') === 'delete_selected') {
            if ($this->permissions['delete'] !== false) {
                ns()->restrict($this->permissions['delete']);
            } else {
                throw new NotAllowedException();
            }

            $status = [
                'success' => 0,
                'error' => 0,
            ];

            foreach ($request->input('entries') as $id) {
                $entity = $this->model::find($id);
                if ($entity instanceof Order) {
                    $entity->delete();
                    $status['success']++;
                } else {
                    $status['error']++;
                }
            }

            return $status;
        }

        return Hook::filter($this->namespace . '-catch-action', false, $request);
    }
}
```

## Best Practices

### 1. Always Use Type Hints

```php
public function setActions(CrudEntry $entry): CrudEntry
public function beforePost(array $request): array
public function bulkAction(Request $request): array
```

### 2. Permission Checking

```php
// Use allowedTo helper for cleaner code
$this->allowedTo('create');

// Or manual check
if ($this->permissions['delete'] !== false) {
    ns()->restrict($this->permissions['delete']);
} else {
    throw new NotAllowedException();
}
```

### 3. Consistent Identifiers

```php
const IDENTIFIER = 'ns.examples';
protected $namespace = 'ns.examples';
```

### 4. Use CrudTable and CrudEntry Helpers

```php
// Modern approach
return CrudTable::columns(
    CrudTable::column(label: __('Name'), identifier: 'name')
);

$entry->action(identifier: 'edit', label: __('Edit'), ...);
```

### 5. Filter Hooks

```php
// Allow external customization
return Hook::filter($this->namespace . '-bulk', $actions);
return Hook::filter($this->namespace . '-catch-action', false, $request);
```

## Integration with Frontend

### Blade Template

```blade
@extends('layout.dashboard')

@section('layout.dashboard.body')
<div class="h-full flex-auto flex flex-col">
    @include('common.dashboard-header')
    <div class="px-4 flex-auto flex flex-col" id="crud-table-container">
        <ns-crud
            src="{{ nsUrl('api/crud/' . $namespace) }}"
            create-url="{{ $createUrl }}"
            namespace="{{ $namespace }}">
        </ns-crud>
    </div>
</div>
@endsection
```

### Route Definition

```php
Route::get('/dashboard/examples', [ExampleController::class, 'list'])
    ->name('ns.dashboard.examples')
    ->middleware('ns.permission:read.examples');
```

This guide is based on actual CRUD implementations in NexoPOS and reflects the true patterns used in production code.
