---
applyTo: '**'
---

# Forms in NexoPOS Core

Forms in NexoPOS Core provide a structured way to create dynamic forms for various purposes beyond settings, such as login forms, registration forms, contact forms, etc.

## Creating a Form Class

Form classes are stored in the `Forms/` directory and can be used to define form structure:

```php
<?php
namespace Modules\ModuleName\Forms;

use Ns\Classes\FormInput;
use Ns\Classes\Form;

class ContactForm extends Form
{
    public function __construct()
    {
        $this->form = $this->getFormStructure();
    }

    private function getFormStructure()
    {
        return Form::form(
            title: __m('Contact Us', 'ModuleName'),
            description: __m('Get in touch with us.', 'ModuleName'),
            tabs: Form::tabs(
                $this->getContactTab(),
                $this->getMessageTab(),
            )
        );
    }

    private function getContactTab()
    {
        return Form::tab(
            label: __m('Contact Information', 'ModuleName'),
            identifier: 'contact',
            fields: Form::fields(
                FormInput::text(
                    name: 'first_name',
                    label: __m('First Name', 'ModuleName'),
                    description: __m('Enter your first name.', 'ModuleName'),
                    validation: 'required|string|max:255',
                    placeholder: 'John'
                ),
                FormInput::text(
                    name: 'last_name',
                    label: __m('Last Name', 'ModuleName'),
                    description: __m('Enter your last name.', 'ModuleName'),
                    validation: 'required|string|max:255',
                    placeholder: 'Doe'
                ),
                FormInput::email(
                    name: 'email',
                    label: __m('Email Address', 'ModuleName'),
                    description: __m('Your email address.', 'ModuleName'),
                    validation: 'required|email',
                    placeholder: 'john@example.com'
                ),
                FormInput::text(
                    name: 'phone',
                    label: __m('Phone Number', 'ModuleName'),
                    description: __m('Your phone number (optional).', 'ModuleName'),
                    validation: 'nullable|string|max:20',
                    placeholder: '+1234567890'
                ),
            )
        );
    }

    private function getMessageTab()
    {
        return Form::tab(
            label: __m('Message', 'ModuleName'),
            identifier: 'message',
            fields: Form::fields(
                FormInput::select(
                    name: 'subject',
                    label: __m('Subject', 'ModuleName'),
                    description: __m('Select the subject of your inquiry.', 'ModuleName'),
                    validation: 'required|string',
                    options: [
                        ['value' => 'general', 'label' => __m('General Inquiry', 'ModuleName')],
                        ['value' => 'support', 'label' => __m('Technical Support', 'ModuleName')],
                        ['value' => 'billing', 'label' => __m('Billing Question', 'ModuleName')],
                        ['value' => 'other', 'label' => __m('Other', 'ModuleName')],
                    ]
                ),
                FormInput::textarea(
                    name: 'message',
                    label: __m('Message', 'ModuleName'),
                    description: __m('Enter your message.', 'ModuleName'),
                    validation: 'required|string|min:10|max:1000',
                    rows: 6,
                    placeholder: 'Enter your message here...'
                ),
                FormInput::switch(
                    name: 'newsletter',
                    value: false,
                    label: __m('Subscribe to Newsletter', 'ModuleName'),
                    description: __m('Receive updates and news.', 'ModuleName'),
                ),
            )
        );
    }

    public function getFormData()
    {
        return $this->form;
    }
}
```

## Simple Single-Tab Form

For simpler forms without tabs:

```php
<?php
namespace Modules\ModuleName\Forms;

use Ns\Classes\FormInput;
use Ns\Classes\Form;

class LoginForm extends Form
{
    public function __construct()
    {
        $this->form = Form::form(
            title: __m('Login', 'ModuleName'),
            description: __m('Sign in to your account.', 'ModuleName'),
            fields: Form::fields(
                FormInput::email(
                    name: 'email',
                    label: __m('Email Address', 'ModuleName'),
                    validation: 'required|email',
                    placeholder: 'user@example.com'
                ),
                FormInput::password(
                    name: 'password',
                    label: __m('Password', 'ModuleName'),
                    validation: 'required|string|min:8'
                ),
                FormInput::switch(
                    name: 'remember',
                    value: false,
                    label: __m('Remember Me', 'ModuleName'),
                ),
            )
        );
    }
}
```

## Dynamic Forms with Data Source

Forms can be populated with data from various sources:

```php
<?php
namespace Modules\ModuleName\Forms;

use Ns\Classes\FormInput;
use Ns\Classes\Form;
use Modules\ModuleName\Models\User;
use Modules\ModuleName\Models\Role;

class UserForm extends Form
{
    private $user;

    public function __construct($user = null)
    {
        $this->user = $user;
        $this->form = $this->buildForm();
    }

    private function buildForm()
    {
        return Form::form(
            title: $this->user ? __m('Edit User', 'ModuleName') : __m('Create User', 'ModuleName'),
            description: __m('User information form.', 'ModuleName'),
            fields: Form::fields(
                FormInput::text(
                    name: 'name',
                    value: $this->user->name ?? '',
                    label: __m('Name', 'ModuleName'),
                    validation: 'required|string|max:255'
                ),
                FormInput::email(
                    name: 'email',
                    value: $this->user->email ?? '',
                    label: __m('Email', 'ModuleName'),
                    validation: 'required|email|unique:users,email,' . ($this->user->id ?? 'NULL')
                ),
                FormInput::select(
                    name: 'role_id',
                    value: $this->user->role_id ?? '',
                    label: __m('Role', 'ModuleName'),
                    options: $this->getRoleOptions(),
                    validation: 'required|exists:roles,id'
                ),
                FormInput::switch(
                    name: 'active',
                    value: $this->user->active ?? true,
                    label: __m('Active', 'ModuleName'),
                ),
            )
        );
    }

    private function getRoleOptions()
    {
        return Role::all()->map(function ($role) {
            return [
                'value' => $role->id,
                'label' => $role->name
            ];
        })->toArray();
    }
}
```

## Using Forms in Controllers

```php
<?php
namespace Modules\ModuleName\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ModuleName\Forms\ContactForm;

class ContactController extends Controller
{
    public function create()
    {
        return ContactForm::renderForm();
    }
}
```

## Key Differences from Settings

1. **Data Source**: Forms can pull data from any source (database, API, etc.), while Settings pull from the options table
2. **Purpose**: Forms are for general data collection, Settings are specifically for configuration
3. **Processing**: Forms require custom processing logic, Settings are automatically saved to options
4. **Validation**: Forms use custom validation logic, Settings have built-in validation handling

## Best Practices

1. **Separate concerns**: Use Forms for data collection, Settings for configuration
2. **Validate properly**: Always implement proper validation rules
3. **Use localization**: Make forms multilingual with `__m()`
4. **Handle errors**: Provide clear error messages
5. **Structure logically**: Group related fields in tabs
6. **Document fields**: Provide helpful descriptions for complex fields
