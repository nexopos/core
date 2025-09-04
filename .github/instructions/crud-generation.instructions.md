---
applyTo: '**'
---

# CRUD Generation in NexoPOS Core

NexoPOS Core provides a command to generate complete CRUD (Create, Read, Update, Delete) functionality for modules.

## Command Usage

```bash
php artisan make:crud {module}
```

### Parameters
- `{module}`: The module namespace where the CRUD will be generated

While providing the module namespace, you're launching a wizard that will guide you through the process of generating the CRUD functionality. The wizard will prompt you for the necessary information to create the CRUD structure.

- CRUD Single Resource: This is the main resource that will be created, which includes the model, controller, views, and routes.

- Table Name: The name of the database table that will be used for this resource.

- Slug: a URL friendly string used typically in URLs (for creating, editing, and viewing the resource).

- Identifier: a unique string used to identify the resource within the system.

- Model Name: The name of the model class that will represent the resource in the application.

- Relation: define the relationship between the resource and other models. This can be a one-to-one, one-to-many relationship as follow "foreign_table, foreign_key, local_key".

- Fillable Columns: These are the columns in the database table that can be mass assigned. You will be prompted to enter these columns comma-separated.

## Rendering The Table
To render the table, you'll need to use the method "table" of your crud class as follows:

```php
namespace Modules\YourModule\Http\Controllers;

use Modules\YourModule\Crud\MyCrud;
use Ns\Http\Controllers\DashboardController;

class YourCrudController extends DashboardController
{
    public function index()
    {
        return MyCrud::table();
    }
}

```

## Rendering The Form
To render the form, you can use the method "form" of your crud class as follows:

```php
namespace Modules\YourModule\Http\Controllers;

use Modules\YourModule\Crud\MyCrud;
use Moduiles\YourModule\Models\CrudModel;
use Ns\Http\Controllers\DashboardController;

class YourCrudController extends DashboardController
{
    public function create()
    {
        return MyCrud::form();
    }

    public function edit( CrudModel $id)
    {
        return MyCrud::form($id);
    }
}
```

## Registering the Routes
To register the routes for your CRUD, you'll have to register it on your web.php file. Here, you'll point your route to the controller defined.&

```php
use Illuminate\Support\Facades\Route;
use Modules\YourModule\Http\Controllers\YourCrudController;

Route::get( 'your-crud', [YourCrudController::class, 'create']);
Route::edit( 'your-crud/{id}', [YourCrudController::class, 'edit']);
```

## How POST and PUT are Handled
NexoPOS handle internal POST and PUT requests. There is no need to handle them manually. The form will automatically handle the POST request when creating a new resource and the PUT request when updating an existing resource.