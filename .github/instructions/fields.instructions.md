---
applyTo: '**'
---

# Fields in NexoPOS Core

Fields provide a simpler alternative to Forms when you need basic field definitions without the complexity of tabs or elaborate structure.

## Creating a Field Class

Field classes are stored in the `Fields/` directory and define an array of field configurations:

```php
<?php
namespace Modules\ModuleName\Fields;

use Ns\Classes\FormInput;
use Ns\Services\FieldsService;

class UserProfileFields extends FieldsService
{
    const IDENTIFIER = 'user_profile_fields';

    const AUTOLOAD = true;

    public function get()
    {
        return [
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
                validation: 'required|email|unique:users,email',
                placeholder: 'john@example.com'
            ),
            FormInput::text(
                name: 'phone',
                label: __m('Phone Number', 'ModuleName'),
                description: __m('Your phone number (optional).', 'ModuleName'),
                validation: 'nullable|string|max:20',
                placeholder: '+1 (555) 123-4567'
            ),
            FormInput::switch(
                name: 'notifications',
                value: true,
                label: __m('Email Notifications', 'ModuleName'),
                description: __m('Receive email notifications.', 'ModuleName'),
            ),
        ];
    }
}
```

A Field class must include the IDENTIFIER constant, which is a unique string used to identify the field collection. The AUTOLOAD constant can be set to true if you want the fields to be automatically loaded by the system. The `get()` method returns an array of field definitions using the `FormInput` class, which provides various input types like text, email, textarea, select, and switch.

## Simple Field Definition

For very basic field collections:

```php
<?php
namespace Modules\ModuleName\Fields;

use Ns\Classes\FormInput;
use Ns\Services\FieldsService;

class QuickContactFields extends FieldsService
{
    public static function get()
    {
        return [
            FormInput::text(
                name: 'name',
                label: __m('Name', 'ModuleName'),
                validation: 'required|string|max:255'
            ),
            FormInput::email(
                name: 'email',
                label: __m('Email', 'ModuleName'),
                validation: 'required|email'
            ),
            FormInput::textarea(
                name: 'message',
                label: __m('Message', 'ModuleName'),
                validation: 'required|string|min:10',
            ),
        ];
    }
}
```

## Dynamic Fields with Filters

You can use filters to allow other modules to extend your fields:

```php
<?php
namespace Modules\ModuleName\Fields;

use Ns\Classes\FormInput;
use Ns\Classes\Hook;
use Ns\Services\FieldsService;

class AuthRegisterFields extends FIeldsService
{
    public static function get()
    {
        $fields = [
            FormInput::text(
                name: 'username',
                label: __m('Username', 'ModuleName'),
                description: __m('Choose a unique username.', 'ModuleName'),
                validation: 'required|string|min:3|max:50|unique:users,username',
                placeholder: 'johndoe'
            ),
            FormInput::email(
                name: 'email',
                label: __m('Email Address', 'ModuleName'),
                description: __m('Your email address.', 'ModuleName'),
                validation: 'required|email|unique:users,email',
                placeholder: 'john@example.com'
            ),
            FormInput::password(
                name: 'password',
                label: __m('Password', 'ModuleName'),
                description: __m('Choose a strong password.', 'ModuleName'),
                validation: 'required|string|min:8|confirmed'
            ),
            FormInput::password(
                name: 'password_confirmation',
                label: __m('Confirm Password', 'ModuleName'),
                description: __m('Confirm your password.', 'ModuleName'),
                validation: 'required|string|min:8'
            ),
        ];

        // Allow other modules to modify registration fields
        return Hook::filter('ns.register.fields', $fields);
    }
}
```

## Conditional Fields

Fields can be conditionally included based on certain criteria:

```php
<?php
namespace Modules\ModuleName\Fields;

use Ns\Classes\FormInput;
use Ns\Services\FieldsService;

class PaymentFields extends FieldsService
{
    public static function getFields($paymentMethod = 'credit_card')
    {
        $baseFields = [
            FormInput::select(
                name: 'payment_method',
                value: $paymentMethod,
                label: __m('Payment Method', 'ModuleName'),
                options: [
                    ['value' => 'credit_card', 'label' => 'Credit Card'],
                    ['value' => 'paypal', 'label' => 'PayPal'],
                    ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ],
                validation: 'required|in:credit_card,paypal,bank_transfer'
            ),
        ];

        // Add method-specific fields
        switch ($paymentMethod) {
            case 'credit_card':
                $baseFields = array_merge($baseFields, self::getCreditCardFields());
                break;
            case 'bank_transfer':
                $baseFields = array_merge($baseFields, self::getBankTransferFields());
                break;
            case 'paypal':
                // PayPal doesn't need additional fields
                break;
        }

        return $baseFields;
    }

    private static function getCreditCardFields()
    {
        return [
            FormInput::text(
                name: 'card_number',
                label: __m('Card Number', 'ModuleName'),
                validation: 'required|string|size:16',
                placeholder: '1234567890123456'
            ),
            FormInput::text(
                name: 'card_holder',
                label: __m('Card Holder Name', 'ModuleName'),
                validation: 'required|string|max:255',
                placeholder: 'John Doe'
            ),
            FormInput::text(
                name: 'expiry_date',
                label: __m('Expiry Date', 'ModuleName'),
                validation: 'required|string|size:5',
                placeholder: 'MM/YY'
            ),
            FormInput::text(
                name: 'cvv',
                label: __m('CVV', 'ModuleName'),
                validation: 'required|string|size:3',
                placeholder: '123'
            ),
        ];
    }

    private static function getBankTransferFields()
    {
        return [
            FormInput::text(
                name: 'bank_name',
                label: __m('Bank Name', 'ModuleName'),
                validation: 'required|string|max:255'
            ),
            FormInput::text(
                name: 'account_number',
                label: __m('Account Number', 'ModuleName'),
                validation: 'required|string|max:50'
            ),
            FormInput::text(
                name: 'routing_number',
                label: __m('Routing Number', 'ModuleName'),
                validation: 'required|string|max:20'
            ),
        ];
    }
}
```

## Using Fields in Controllers

```php
<?php
namespace Modules\ModuleName\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ModuleName\Fields\UserProfileFields;

class ProfileController extends Controller
{
    public function edit()
    {
        $object = new UserProfileFields;
        $fields = $object->get();
        
        // Populate fields with current user data
        $user = auth()->user();
        
        foreach ($fields as &$field) {
            if (isset($user->{$field['name']})) {
                $field['value'] = $user->{$field['name']};
            }
        }

        return view('ModuleName::profile.edit', compact('fields'));
    }

    public function update(Request $request)
    {
        $object = new UserProfileFields;
        $fields = $object->get();

        // Extract validation rules from fields
        $rules = [];
        foreach ($fields as $field) {
            if (isset($field['validation'])) {
                $rules[$field['name']] = $field['validation'];
            }
        }

        $validatedData = $request->validate($rules);

        // Update user profile
        auth()->user()->update($validatedData);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}
```

## Rendering Fields in Views
fields can be renderer using the Vue component `ns-field`. It's possible to retrieve the fields through an API call to the endpoint `/api/fields/{identifier}`. Here "identifier" is the constant `IDENTIFIER` defined in your field class.

Note that the vue compoenent `ns-field` is globally available on the the application view.

## When to Use Fields vs Forms vs Settings

- **Fields**: Simple field collections without complex structure
- **Forms**: Complete forms with tabs and elaborate organization
- **Settings**: Configuration options that need automatic storage/retrieval

## Best Practices

1. **Keep it simple**: Fields are for basic collections
2. **Use filters wisely**: Allow extension only when necessary  
3. **Validate consistently**: Include proper validation rules
4. **Document thoroughly**: Provide clear descriptions
5. **Localize everything**: Use `__m()` for all text
6. **Structure logically**: Group related fields together
