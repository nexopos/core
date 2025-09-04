---
applyTo: '**'
---

# Settings Pages in NexoPOS Core

Settings pages provide a structured way to create configuration forms with tabs, validation, and automatic option storage.

## Creating a Settings Class

Settings classes extend `Ns\Services\SettingsPage` and are stored in the `Settings/` directory:

```php
<?php
namespace Modules\ModuleName\Settings;

use Ns\Classes\FormInput;
use Ns\Classes\SettingForm;
use Ns\Services\SettingsPage;

class ModuleSettings extends SettingsPage
{
    const IDENTIFIER = 'module_settings';
    const AUTOLOAD = true;

    public function __construct()
    {
        $this->form = SettingForm::form(
            title: __m('Module Settings', 'ModuleName'),
            description: __m('Configure module settings.', 'ModuleName'),
            tabs: SettingForm::tabs(
                $this->getGeneralTab(),
                $this->getAdvancedTab(),
            )
        );
    }

    private function getGeneralTab()
    {
        return SettingForm::tab(
            label: __m('General', 'ModuleName'),
            identifier: 'general',
            fields: SettingForm::fields(
                FormInput::text(
                    name: 'module_name',
                    value: ns()->option->get('module_name'),
                    label: __m('Module Name', 'ModuleName'),
                    description: __m('The name of the module.', 'ModuleName'),
                    validation: 'required|string|max:255',
                ),
                FormInput::textarea(
                    name: 'module_description',
                    value: ns()->option->get('module_description'),
                    label: __m('Description', 'ModuleName'),
                    description: __m('Module description.', 'ModuleName'),
                ),
                FormInput::switch(
                    name: 'module_enabled',
                    value: ns()->option->get('module_enabled', false),
                    label: __m('Enable Module', 'ModuleName'),
                    description: __m('Enable or disable the module.', 'ModuleName'),
                ),
            )
        );
    }

    private function getAdvancedTab()
    {
        return SettingForm::tab(
            label: __m('Advanced', 'ModuleName'),
            identifier: 'advanced',
            fields: SettingForm::fields(
                FormInput::select(
                    name: 'module_mode',
                    value: ns()->option->get('module_mode', 'production'),
                    label: __m('Mode', 'ModuleName'),
                    description: __m('Module operating mode.', 'ModuleName'),
                    options: [
                        ['value' => 'development', 'label' => 'Development'],
                        ['value' => 'production', 'label' => 'Production'],
                    ],
                ),
                FormInput::number(
                    name: 'module_timeout',
                    value: ns()->option->get('module_timeout', 30),
                    label: __m('Timeout (seconds)', 'ModuleName'),
                    description: __m('Request timeout in seconds.', 'ModuleName'),
                    validation: 'required|integer|min:1|max:300',
                ),
            )
        );
    }
}
```

## Available Form Input Types

### Text Input
```php
FormInput::text(
    name: 'field_name',
    value: 'default_value',
    label: 'Field Label',
    description: 'Field description',
    validation: 'required|string|max:255',
    placeholder: 'Enter text...'
)
```

### Textarea
```php
FormInput::textarea(
    name: 'field_name',
    value: 'default_value',
    label: 'Field Label',
    description: 'Field description',
    rows: 5
)
```

### Number Input
```php
FormInput::number(
    name: 'field_name',
    value: 0,
    label: 'Field Label',
    description: 'Field description',
    validation: 'required|integer|min:0'
)
```

### Email Input
```php
FormInput::email(
    name: 'field_name',
    value: 'default@example.com',
    label: 'Email Address',
    description: 'Enter email address',
    validation: 'required|email'
)
```

### Password Input
```php
FormInput::password(
    name: 'field_name',
    label: 'Password',
    description: 'Enter password',
    validation: 'required|string|min:8'
)
```

### Switch/Toggle
```php
FormInput::switch(
    name: 'field_name',
    value: true,
    label: 'Enable Feature',
    description: 'Toggle feature on/off'
)
```

### Select Dropdown
```php
FormInput::select(
    name: 'field_name',
    value: 'option1',
    label: 'Select Option',
    description: 'Choose an option',
    options: [
        ['value' => 'option1', 'label' => 'Option 1'],
        ['value' => 'option2', 'label' => 'Option 2'],
    ]
)
```

### Multiselect
```php
FormInput::multiselect(
    name: 'field_name',
    value: ['option1', 'option2'],
    label: 'Multiple Options',
    description: 'Select multiple options',
    options: [
        ['value' => 'option1', 'label' => 'Option 1'],
        ['value' => 'option2', 'label' => 'Option 2'],
        ['value' => 'option3', 'label' => 'Option 3'],
    ]
)
```

### File Upload
```php
FormInput::file(
    name: 'field_name',
    label: 'Upload File',
    description: 'Select file to upload',
    accept: '.jpg,.png,.pdf'
)
```

### Date Input
```php
FormInput::date(
    name: 'field_name',
    value: '2024-01-01',
    label: 'Select Date',
    description: 'Choose a date'
)
```

### DateTime Input
```php
FormInput::datetime(
    name: 'field_name',
    value: '2024-01-01 12:00:00',
    label: 'Select Date & Time',
    description: 'Choose date and time'
)
```

### Color Picker
```php
FormInput::color(
    name: 'field_name',
    value: '#ff0000',
    label: 'Choose Color',
    description: 'Select a color'
)
```

## Form Structure

### Form Definition
```php
$this->form = SettingForm::form(
    title: 'Form Title',
    description: 'Form description',
    tabs: SettingForm::tabs(
        // tabs array
    )
);
```

### Tab Definition
```php
SettingForm::tab(
    label: 'Tab Label',
    identifier: 'tab_identifier',
    fields: SettingForm::fields(
        // fields array
    )
)
```

### Fields Collection
```php
SettingForm::fields(
    FormInput::text(...),
    FormInput::select(...),
    FormInput::switch(...),
    // more fields
)
```

## Validation

Use Laravel validation rules:

```php
FormInput::text(
    name: 'username',
    validation: 'required|string|min:3|max:50|unique:users,username'
)
```

## Accessing Settings Values

```php
// Get setting value
$value = ns()->option->get('setting_name', 'default_value');

// Set setting value
ns()->option->set('setting_name', 'new_value');

// Multiple settings
ns()->option->set([
    'setting1' => 'value1',
    'setting2' => 'value2',
]);
```

## Registration

Settings with `AUTOLOAD = true` are automatically discovered. For manual registration:

## Best Practices

1. **Use localization**: Always use `__m()` for labels and descriptions
2. **Group related settings**: Use tabs to organize settings logically
3. **Provide defaults**: Always provide default values for settings
4. **Validate input**: Use appropriate validation rules
5. **Document settings**: Include helpful descriptions
6. **Use proper naming**: Use descriptive setting names with module prefix
