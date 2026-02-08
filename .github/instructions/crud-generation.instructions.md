---
applyTo: '**'
---

# CRUD Generation in NexoPOS Core

NexoPOS Core provides a command to generate complete CRUD (Create, Read, Update, Delete) functionality for modules.

## Command Usage

### Interactive Mode

```bash
php artisan make:crud {module}
```

### Non-Interactive Mode (for AI Agents)

```bash
php artisan make:crud MyModule \
    --resource="Product" \
    --table="mymodule_products" \
    --slug="products" \
    --identifier="mymodule.products" \
    --model="Modules\\MyModule\\Models\\Product" \
    --fillable="name,description,price,stock" \
    --no-interaction
```

### Parameters
- `{module}`: The module namespace where the CRUD will be generated

### Options
- `--resource=`: CRUD resource name (e.g., "Product", "Customer")
- `--table=`: Database table name (e.g., "mymodule_products")
- `--slug=`: URL-friendly route slug (e.g., "products")
- `--identifier=`: Unique CRUD identifier (e.g., "mymodule.products")
- `--model=`: Full model class name with namespace (e.g., "Modules\\MyModule\\Models\\Product")
- `--relations=`: Relations in format "foreign_table,foreign_key,local_key" (can be used multiple times)
- `--fillable=`: Comma-separated fillable columns (optional)
- `--no-interaction`: Run without any prompts (requires all required options)

### Examples

**Interactive mode:**
```bash
php artisan make:crud MyModule
# You'll be prompted for resource, table, slug, identifier, model, relations, and fillable columns
```

**Non-interactive mode without relations:**
```bash
php artisan make:crud BlogModule \
    --resource="Post" \
    --table="blog_posts" \
    --slug="posts" \
    --identifier="blog.posts" \
    --model="Modules\\BlogModule\\Models\\Post" \
    --fillable="title,content,excerpt,published_at" \
    --no-interaction
```

**Non-interactive mode with relations:**
```bash
php artisan make:crud BlogModule \
    --resource="Post" \
    --table="blog_posts" \
    --slug="posts" \
    --identifier="blog.posts" \
    --model="Modules\\BlogModule\\Models\\Post" \
    --relations="users,author_id,id" \
    --relations="categories,category_id,id" \
    --fillable="title,content,author_id,category_id" \
    --no-interaction
```

**Note:** When using `--model` option, make sure to escape backslashes properly in your shell:
- Bash/Linux: Use double backslashes `\\` or single quotes
- Windows CMD: Use single backslashes `\`
- PowerShell: Use backtick before backslash `` `\ `` or quoted strings

While providing the module namespace, you're launching a wizard that will guide you through the process of generating the CRUD functionality. The wizard will prompt you for the necessary information to create the CRUD structure.

- CRUD Single Resource: This is the main resource that will be created, which includes the model, controller, views, and routes.

- Table Name: The name of the database table that will be used for this resource.

- Slug: a URL friendly string used typically in URLs (for creating, editing, and viewing the resource).

- Identifier: a unique string used to identify the resource within the system.

- Model Name: The name of the model class that will represent the resource in the application.

- Relation: define the relationship between the resource and other models. This can be a one-to-one, one-to-many relationship as follow "foreign_table, foreign_key, local_key".

- Fillable Columns: These are the columns in the database table that can be mass assigned. You will be prompted to enter these columns comma-separated.

## Creating CRUD UI Controllers

After generating the CRUD class, you need to create a controller to render the UI views. The controller should be placed in the `Http/Controllers/Dashboard` directory of your module.

### Controller Pattern

Controllers for CRUD UI should follow this pattern:

```php
<?php

namespace Modules\YourModule\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Modules\YourModule\Crud\ProductCrud;
use Modules\YourModule\Models\Product;

class ProductCrudController extends Controller
{
    /**
     * Display all products (table view)
     */
    public function listProducts()
    {
        return ProductCrud::table();
    }

    /**
     * Show form to create a new product
     */
    public function createProduct()
    {
        return ProductCrud::form();
    }

    /**
     * Show form to edit a product
     */
    public function editProduct(Product $product)
    {
        return ProductCrud::form($product);
    }
}
```

### Key Points

- **List Method**: Calls `MyCrud::table()` to display the data table
- **Create Method**: Calls `MyCrud::form()` to display an empty creation form  
- **Edit Method**: Calls `MyCrud::form($model)` to display a pre-filled edit form
- **Model Binding**: Use route model binding for the edit method (e.g., `Product $product`)

## Registering the Routes

After creating the controller, register the routes in your module's `Routes/web.php` file:

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\YourModule\Http\Controllers\Dashboard\ProductCrudController;

Route::prefix('your-module')
    ->middleware(['web', 'auth'])
    ->name('yourmodule.')
    ->group(function () {
        
        // List all products (table view)
        Route::get('/products', [ProductCrudController::class, 'listProducts'])
            ->name('products.index')
            ->middleware('ns.restrict:yourmodule.read.products');
        
        // Show create form
        Route::get('/products/create', [ProductCrudController::class, 'createProduct'])
            ->name('products.create')
            ->middleware('ns.restrict:yourmodule.create.products');
        
        // Show edit form
        Route::get('/products/{product}/edit', [ProductCrudController::class, 'editProduct'])
            ->name('products.edit')
            ->middleware('ns.restrict:yourmodule.update.products');
    });
```

### Route Pattern

For each CRUD resource, define three routes:

1. **List Route**: `GET /resource` → Display table
2. **Create Route**: `GET /resource/create` → Display creation form
3. **Edit Route**: `GET /resource/{model}/edit` → Display edit form

### Important Notes

- Always use `{model}/edit` pattern for edit routes (not `{id}/edit`)
- Apply appropriate middleware for permission checking
- Use route model binding for cleaner code
- The create route must come before the edit route in route registration

## How POST, PUT, and DELETE are Handled

NexoPOS handles internal POST, PUT, and DELETE requests automatically via the API. There is no need to handle them manually.

- **POST (Create)**: When the form is submitted, NexoPOS automatically sends a POST request to create the resource
- **PUT (Update)**: When the edit form is submitted, NexoPOS automatically sends a PUT request to update the resource  
- **DELETE (Delete)**: When the delete action is triggered, NexoPOS automatically sends a DELETE request to remove the resource

The CRUD system handles these operations through the backend API routes automatically, so you only need to define the UI routes (list, create, edit) as shown above.