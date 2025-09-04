---
applyTo: '**'
---

# Form Validation in NexoPOS Core

NexoPOS Core includes a robust form validation system that handles client-side and server-side validation with Vue 3 integration.

## Form Validation Class Overview

The form validation class is located at `vendor/nexopos/core/resources/ts/libraries/form-validation.ts` and provides comprehensive validation functionality for forms with tabs and complex field structures.

## Basic Form Validation

### Creating Fields
While creating your array of fields, it's necessary to pass it through the method "createFields". This method will add necessary properties such as the "errors" used to display an error on the form.

```typescript
import FormValidation from './form-validation';

const formValidation = new FormValidation();
const fields = formValidation.createFields([
    {
        name: 'username',
        value: '',
        validation: 'required|string|min:3|max:50',
        errors: []
    },
    {
        name: 'email',
        value: '',
        validation: 'required|email',
        errors: []
    }
]);
```

### Simple Field Validation

```typescript
import FormValidation from './form-validation';

const formValidation = new FormValidation();

// Validate individual field
const field = {
    name: 'email',
    value: 'user@example.com',
    validation: 'required|email',
    errors: []
};

const isValid = formValidation.validateField(field);
console.log(isValid); // true or false
console.log(field.errors); // Array of error messages
```

### Multiple Fields Validation

```typescript
const fields = [
    {
        name: 'username',
        value: 'john',
        validation: 'required|string|min:3|max:50',
    },
    {
        name: 'email',
        value: 'john@example.com',
        validation: 'required|email',
    },
    {
        name: 'password',
        value: 'secret123',
        validation: 'required|string|min:8',
    }
];

const allValid = formValidation.validateFields(fields);
console.log(allValid); // true if all fields are valid
```

## Form with Tabs Validation

While dealing with form object it's necessary to pass it through the method "createForm". This method will add necessary properties such as the "errors" on the tabs used to display an error on the form. For each fields, it also call the method "createFields" to add the necessary properties.

```typescript
import FormValidation from './form-validation';
const formValidation = new FormValidation();
const form = formValidation.createForm({
    tabs: {
        personal: {
            label: 'Personal Information',
            active: true,
            fields: [
                {
                    name: 'first_name',
                    value: '',
                    validation: 'required|string|max:255',
                },
                {
                    name: 'last_name',
                    value: '',
                    validation: 'required|string|max:255',
                }
            ]
        },
        contact: {
            label: 'Contact Information',
            active: false,
            fields: [
                {
                    name: 'email',
                    value: '',
                    validation: 'required|email',
                },
                {
                    name: 'phone',
                    value: '',
                    validation: 'nullable|string|max:20',
                }
            ]
        }
    }
});
```

### Form Structure

```typescript
const form = {
    main: {
        // Main form fields (optional)
    },
    tabs: {
        personal: {
            label: 'Personal Information',
            active: true,
            errors: [],
            fields: [
                {
                    name: 'first_name',
                    value: 'John',
                    validation: 'required|string|max:255',
                    errors: []
                },
                {
                    name: 'last_name',
                    value: 'Doe',
                    validation: 'required|string|max:255',
                    errors: []
                }
            ]
        },
        contact: {
            label: 'Contact Information',
            active: false,
            errors: [],
            fields: [
                {
                    name: 'email',
                    value: 'john@example.com',
                    validation: 'required|email',
                    errors: []
                },
                {
                    name: 'phone',
                    value: '+1234567890',
                    validation: 'nullable|string|max:20',
                    errors: []
                }
            ]
        }
    }
};

// Validate entire form
const formErrors = formValidation.validateForm(form);
console.log(formErrors); // Array of all validation errors
```

### Initialize Tabs

```typescript
// Initialize tabs with proper structure
const initializedTabs = formValidation.initializeTabs(form.tabs);
```

## Vue 3 Component Integration

### Basic Form Component

```vue
<template>
    <form @submit.prevent="submitForm">
        <div v-for="field in fields" :key="field.name" class="form-group">
            <label :for="field.name">{{ field.label }}</label>
            
            <input 
                :id="field.name"
                v-model="field.value"
                :type="field.type || 'text'"
                :class="['form-control', { 'is-invalid': field.errors.length > 0 }]"
                @blur="validateSingleField(field)"
                @input="clearFieldErrors(field)"
            />
            
            <div v-if="field.errors.length > 0" class="invalid-feedback">
                <div v-for="error in field.errors" :key="error">{{ error }}</div>
            </div>
        </div>
        
        <button type="submit" :disabled="!isFormValid">Submit</button>
    </form>
</template>

<script>
import FormValidation from './form-validation';

export default {
    data() {
        return {
            formValidation: new FormValidation(),
            fields: [
                {
                    name: 'username',
                    label: 'Username',
                    value: '',
                    validation: 'required|string|min:3|max:50',
                    errors: []
                },
                {
                    name: 'email',
                    label: 'Email',
                    value: '',
                    validation: 'required|email',
                    errors: []
                }
            ]
        };
    },
    computed: {
        isFormValid() {
            return this.formValidation.fieldsValid(this.fields);
        }
    },
    methods: {
        validateSingleField(field) {
            this.formValidation.validateField(field);
        },
        
        clearFieldErrors(field) {
            field.errors = [];
        },
        
        submitForm() {
            const isValid = this.formValidation.validateFields(this.fields);
            
            if (isValid) {
                // Submit form data
                this.sendFormData();
            } else {
                // Show validation errors
                this.showValidationErrors();
            }
        },
        
        sendFormData() {
            const formData = {};
            this.fields.forEach(field => {
                formData[field.name] = field.value;
            });
            
            // Send data to server
            nsHttpClient.post('/api/your-endpoint', formData)
                .subscribe({
                    next: (response) => {
                        // Handle success
                        nsSnackBar.success('Form submitted successfully!');
                    },
                    error: (error) => {
                        // Handle server validation errors
                        this.handleServerErrors(error);
                    }
                });
        },
        
        handleServerErrors(error) {
            if (error.errors) {
                // Map server errors to form fields
                this.fields.forEach(field => {
                    if (error.errors[field.name]) {
                        field.errors = error.errors[field.name];
                    }
                });
            }
        }
    }
};
</script>
```

### Tabbed Form Component

```vue
<template>
    <div class="form-container">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs">
            <li v-for="(tab, key) in form.tabs" :key="key" class="nav-item">
                <a 
                    :class="['nav-link', { active: tab.active, 'text-danger': tab.errors.length > 0 }]"
                    @click="switchTab(key)"
                    href="#"
                >
                    {{ tab.label }}
                    <span v-if="tab.errors.length > 0" class="badge badge-danger ms-2">
                        {{ tab.errors.length }}
                    </span>
                </a>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <div v-for="(tab, key) in form.tabs" :key="key" 
                 v-show="tab.active" class="tab-pane">
                
                <div v-for="field in tab.fields" :key="field.name" class="form-group">
                    <label :for="field.name">{{ field.label }}</label>
                    
                    <input 
                        :id="field.name"
                        v-model="field.value"
                        :type="field.type || 'text'"
                        :class="['form-control', { 'is-invalid': field.errors.length > 0 }]"
                        @blur="validateField(field)"
                    />
                    
                    <div v-if="field.errors.length > 0" class="invalid-feedback">
                        <div v-for="error in field.errors" :key="error">{{ error }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" @click="validateAndSubmit" :disabled="submitting">
                {{ submitting ? 'Submitting...' : 'Submit' }}
            </button>
        </div>
    </div>
</template>

<script>
import FormValidation from './form-validation';

export default {
    data() {
        return {
            formValidation: new FormValidation(),
            submitting: false,
            form: {
                tabs: {
                    personal: {
                        label: 'Personal Info',
                        active: true,
                        errors: [],
                        fields: [
                            {
                                name: 'first_name',
                                label: 'First Name',
                                value: '',
                                validation: 'required|string|max:255',
                                errors: []
                            }
                        ]
                    },
                    contact: {
                        label: 'Contact Info',
                        active: false,
                        errors: [],
                        fields: [
                            {
                                name: 'email',
                                label: 'Email',
                                value: '',
                                validation: 'required|email',
                                errors: []
                            }
                        ]
                    }
                }
            }
        };
    },
    mounted() {
        // Initialize tabs
        this.form.tabs = this.formValidation.initializeTabs(this.form.tabs);
    },
    methods: {
        switchTab(tabKey) {
            // Deactivate all tabs
            Object.keys(this.form.tabs).forEach(key => {
                this.form.tabs[key].active = false;
            });
            
            // Activate selected tab
            this.form.tabs[tabKey].active = true;
        },
        
        validateField(field) {
            this.formValidation.validateField(field);
        },
        
        validateAndSubmit() {
            const errors = this.formValidation.validateForm(this.form);
            
            if (errors.length === 0) {
                this.submitForm();
            } else {
                // Focus on first tab with errors
                this.focusFirstErrorTab();
                nsSnackBar.error('Please correct the validation errors.');
            }
        },
        
        focusFirstErrorTab() {
            for (const [key, tab] of Object.entries(this.form.tabs)) {
                if (tab.errors.length > 0) {
                    this.switchTab(key);
                    break;
                }
            }
        },
        
        submitForm() {
            this.submitting = true;
            
            // Collect form data
            const formData = this.collectFormData();
            
            nsHttpClient.post('/api/submit-form', formData)
                .subscribe({
                    next: (response) => {
                        nsSnackBar.success('Form submitted successfully!');
                        this.submitting = false;
                    },
                    error: (error) => {
                        this.handleSubmissionError(error);
                        this.submitting = false;
                    }
                });
        },
        
        collectFormData() {
            const data = {};
            
            Object.values(this.form.tabs).forEach(tab => {
                tab.fields.forEach(field => {
                    data[field.name] = field.value;
                });
            });
            
            return data;
        },
        
        handleSubmissionError(error) {
            if (error.errors) {
                // Map server validation errors back to form
                Object.values(this.form.tabs).forEach(tab => {
                    tab.fields.forEach(field => {
                        if (error.errors[field.name]) {
                            field.errors = error.errors[field.name];
                        }
                    });
                });
                
                // Re-validate form to update tab error indicators
                this.formValidation.validateForm(this.form);
                this.focusFirstErrorTab();
            }
            
            nsSnackBar.error('Validation errors occurred. Please check your input.');
        }
    }
};
</script>
```

## Available Validation Rules

The form validation supports Laravel-style validation rules:

- `required` - Field must have a value
- `email` - Must be a valid email address
- `string` - Must be a string
- `numeric` - Must be numeric
- `integer` - Must be an integer
- `min:3` - Minimum length/value
- `max:255` - Maximum length/value
- `confirmed` - Must match field_confirmation
- `unique:table,column` - Must be unique in database
- `exists:table,column` - Must exist in database
- `regex:/pattern/` - Must match regex pattern
- `in:value1,value2` - Must be one of the specified values
- `between:1,100` - Must be between specified values
- `alpha` - Only alphabetic characters
- `alpha_num` - Only alphanumeric characters
- `url` - Must be a valid URL

## Best Practices

1. **Real-time validation**: Validate fields on blur for better UX
2. **Clear errors on input**: Clear validation errors when user starts typing
3. **Visual indicators**: Show validation state with CSS classes
4. **Tab error indicators**: Show error counts on tabs
5. **Server error handling**: Map server validation errors back to form fields
6. **Accessibility**: Use proper ARIA labels for screen readers
7. **Performance**: Debounce validation for expensive operations
8. **User feedback**: Provide clear, helpful error messages
