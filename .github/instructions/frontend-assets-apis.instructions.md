---
applyTo: '**'
---

# Frontend Assets and APIs in NexoPOS Core

NexoPOS Core provides a comprehensive set of frontend assets, utilities, and APIs for building rich user interfaces with Vue 3, TypeScript, and modern web technologies.

## Global Objects and APIs

### HTTP Client (`nsHttpClient`)

The HTTP client uses RxJS for asynchronous operations and provides methods for all HTTP verbs.

```typescript
// GET request
nsHttpClient.get('/api/users')
    .subscribe({
        next: (users) => {
            console.log('Users:', users);
        },
        error: (error) => {
            console.error('Error:', error);
        }
    });

// POST request with data
nsHttpClient.post('/api/users', {
    name: 'John Doe',
    email: 'john@example.com'
})
    .subscribe({
        next: (response) => {
            console.log('User created:', response);
        },
        error: (error) => {
            console.error('Creation failed:', error);
        }
    });

// PUT request for updates
nsHttpClient.put('/api/users/1', {
    name: 'Jane Doe',
    email: 'jane@example.com'
})
    .subscribe({
        next: (response) => {
            console.log('User updated:', response);
        },
        error: (error) => {
            console.error('Update failed:', error);
        }
    });

// DELETE request
nsHttpClient.delete('/api/users/1')
    .subscribe({
        next: (response) => {
            console.log('User deleted:', response);
        },
        error: (error) => {
            console.error('Deletion failed:', error);
        }
    });
```

**Important**: `nsHttpClient` returns RxJS Subscriptions, NOT Promises. Never use `async/await` with these methods.

### Popup System

#### Alert Popup (`nsAlertPopup`)

```typescript
Popup.show(nsAlertPopup, {
    title: 'Warning',
    message: 'You are not allowed to perform this action.'
});
```

#### Prompt Popup (`nsPromptPopup`)

```typescript
Popup.show(nsPromptPopup, {
    title: 'Enter Your Age',
    message: 'Before you proceed, we need to know your age.',
    onAction: (age) => {
        if (age && parseInt(age) >= 18) {
            console.log('User is old enough');
        } else {
            nsSnackBar.error('You must be 18 or older');
        }
    }
});
```

#### Confirm Popup (`nsConfirmPopup`)

```typescript
Popup.show(nsConfirmPopup, {
    title: 'Confirm Deletion',
    message: 'Are you sure you want to delete this item? This action cannot be undone.',
    onAction: (confirmed) => {
        if (confirmed) {
            // Proceed with deletion
            deleteItem();
        }
    }
});
```

### Notifications

#### SnackBar (Toast Messages)

```typescript
// Success message
nsSnackBar.success('Operation completed successfully!');

// Error message
nsSnackBar.error('Something went wrong. Please try again.');

// Warning message
nsSnackBar.warning('This action may have unexpected consequences.');

// Info message
nsSnackBar.info('New updates are available.');
```

#### Notice (Elaborate Notifications)

```typescript
nsNotice.success(
    'Email Sent Successfully',
    'Your message has been delivered to the recipient.',
    {
        actions: {
            okay: {
                label: 'Okay',
                onClick: (notice) => {
                    notice.close();
                }
            },
            viewMessage: {
                label: 'View Message',
                onClick: (notice) => {
                    // Navigate to sent messages
                    window.location.href = '/messages/sent';
                    notice.close();
                }
            }
        }
    }
);

// Error notice with retry option
nsNotice.error(
    'Upload Failed',
    'The file could not be uploaded due to a network error.',
    {
        actions: {
            retry: {
                label: 'Retry',
                onClick: (notice) => {
                    retryUpload();
                    notice.close();
                }
            },
            cancel: {
                label: 'Cancel',
                onClick: (notice) => {
                    notice.close();
                }
            }
        }
    }
);
```

### Vue Component System

#### Global Component Registry (`nsExtraComponents`)

```typescript
// Register a global component
nsExtraComponents.myCustomWidget = defineComponent({
    name: 'MyCustomWidget',
    template: `
        <div class="widget">
            <h3>{{ title }}</h3>
            <p>{{ description }}</p>
        </div>
    `,
    props: {
        title: String,
        description: String
    }
});

// Register a form component
nsExtraComponents.advancedForm = defineComponent({
    name: 'AdvancedForm',
    template: `
        <form @submit.prevent="submitForm">
            <div v-for="field in fields" :key="field.name">
                <label>{{ field.label }}</label>
                <input v-model="field.value" :type="field.type" />
            </div>
            <button type="submit">Submit</button>
        </form>
    `,
    props: {
        fields: Array
    },
    methods: {
        submitForm() {
            this.$emit('submit', this.fields);
        }
    }
});
```

#### Component Definition Helper (`defineComponent`)

```typescript
// Define a Vue 3 component
const MyComponent = defineComponent({
    name: 'MyComponent',
    template: `
        <div class="my-component">
            <h2>{{ title }}</h2>
            <button @click="handleClick">{{ buttonText }}</button>
        </div>
    `,
    props: {
        title: {
            type: String,
            required: true
        },
        buttonText: {
            type: String,
            default: 'Click Me'
        }
    },
    data() {
        return {
            clicked: false
        };
    },
    methods: {
        handleClick() {
            this.clicked = true;
            this.$emit('clicked');
        }
    }
});
```

## Advanced Frontend Patterns

### Data Loading with Error Handling

```typescript
export class DataService {
    static loadUserData(userId: number): Promise<any> {
        return new Promise((resolve, reject) => {
            nsHttpClient.get(`/api/users/${userId}`)
                .subscribe({
                    next: (userData) => {
                        resolve(userData);
                    },
                    error: (error) => {
                        nsSnackBar.error('Failed to load user data');
                        reject(error);
                    }
                });
        });
    }

    static async saveUserData(userId: number, data: any): Promise<any> {
        try {
            const result = await new Promise((resolve, reject) => {
                nsHttpClient.put(`/api/users/${userId}`, data)
                    .subscribe({
                        next: resolve,
                        error: reject
                    });
            });
            
            nsSnackBar.success('User data saved successfully');
            return result;
        } catch (error) {
            nsSnackBar.error('Failed to save user data');
            throw error;
        }
    }
}
```

### Reactive Form Component

```typescript
const ReactiveForm = defineComponent({
    name: 'ReactiveForm',
    template: `
        <div class="reactive-form">
            <form @submit.prevent="handleSubmit">
                <div v-for="field in formFields" :key="field.name" class="form-group">
                    <label :for="field.name">{{ field.label }}</label>
                    
                    <input 
                        v-if="field.type === 'text' || field.type === 'email'"
                        :id="field.name"
                        v-model="formData[field.name]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :class="getFieldClasses(field)"
                        @blur="validateField(field)"
                    />
                    
                    <textarea 
                        v-else-if="field.type === 'textarea'"
                        :id="field.name"
                        v-model="formData[field.name]"
                        :placeholder="field.placeholder"
                        :class="getFieldClasses(field)"
                        @blur="validateField(field)"
                    ></textarea>
                    
                    <select 
                        v-else-if="field.type === 'select'"
                        :id="field.name"
                        v-model="formData[field.name]"
                        :class="getFieldClasses(field)"
                        @change="validateField(field)"
                    >
                        <option value="">Select...</option>
                        <option v-for="option in field.options" 
                                :key="option.value" 
                                :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                    
                    <div v-if="fieldErrors[field.name]" class="error-message">
                        {{ fieldErrors[field.name] }}
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" :disabled="!isFormValid || submitting">
                        {{ submitting ? 'Submitting...' : 'Submit' }}
                    </button>
                    <button type="button" @click="resetForm">Reset</button>
                </div>
            </form>
        </div>
    `,
    props: {
        fields: {
            type: Array,
            required: true
        },
        initialData: {
            type: Object,
            default: () => ({})
        }
    },
    data() {
        return {
            formData: {},
            fieldErrors: {},
            submitting: false
        };
    },
    computed: {
        formFields() {
            return this.fields;
        },
        isFormValid() {
            return Object.keys(this.fieldErrors).length === 0;
        }
    },
    mounted() {
        this.initializeForm();
    },
    methods: {
        initializeForm() {
            this.formFields.forEach(field => {
                this.formData[field.name] = this.initialData[field.name] || '';
            });
        },
        
        validateField(field) {
            const value = this.formData[field.name];
            const rules = field.validation ? field.validation.split('|') : [];
            
            for (const rule of rules) {
                const error = this.validateRule(value, rule, field);
                if (error) {
                    this.fieldErrors[field.name] = error;
                    return;
                }
            }
            
            delete this.fieldErrors[field.name];
        },
        
        validateRule(value, rule, field) {
            if (rule === 'required' && (!value || value.trim() === '')) {
                return `${field.label} is required`;
            }
            
            if (rule === 'email' && value && !/\S+@\S+\.\S+/.test(value)) {
                return `${field.label} must be a valid email`;
            }
            
            if (rule.startsWith('min:')) {
                const min = parseInt(rule.split(':')[1]);
                if (value && value.length < min) {
                    return `${field.label} must be at least ${min} characters`;
                }
            }
            
            if (rule.startsWith('max:')) {
                const max = parseInt(rule.split(':')[1]);
                if (value && value.length > max) {
                    return `${field.label} must not exceed ${max} characters`;
                }
            }
            
            return null;
        },
        
        getFieldClasses(field) {
            return [
                'form-control',
                {
                    'is-invalid': this.fieldErrors[field.name],
                    'is-valid': !this.fieldErrors[field.name] && this.formData[field.name]
                }
            ];
        },
        
        handleSubmit() {
            // Validate all fields
            this.formFields.forEach(field => {
                this.validateField(field);
            });
            
            if (!this.isFormValid) {
                nsSnackBar.error('Please correct the validation errors');
                return;
            }
            
            this.submitForm();
        },
        
        submitForm() {
            this.submitting = true;
            
            nsHttpClient.post('/api/submit-form', this.formData)
                .subscribe({
                    next: (response) => {
                        nsSnackBar.success('Form submitted successfully!');
                        this.$emit('submitted', response);
                        this.submitting = false;
                    },
                    error: (error) => {
                        if (error.errors) {
                            // Handle server validation errors
                            Object.keys(error.errors).forEach(field => {
                                this.fieldErrors[field] = error.errors[field][0];
                            });
                        } else {
                            nsSnackBar.error('Failed to submit form');
                        }
                        this.submitting = false;
                    }
                });
        },
        
        resetForm() {
            this.initializeForm();
            this.fieldErrors = {};
        }
    }
});

// Register the component globally
nsExtraComponents.reactiveForm = ReactiveForm;
```

### Data Table Component

```typescript
const DataTable = defineComponent({
    name: 'DataTable',
    template: `
        <div class="data-table">
            <div class="table-controls">
                <input 
                    v-model="searchQuery" 
                    type="text" 
                    placeholder="Search..."
                    class="search-input"
                    @input="handleSearch"
                />
                <select v-model="pageSize" @change="handlePageSizeChange">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th v-for="column in columns" 
                            :key="column.key"
                            @click="handleSort(column)"
                            :class="{ sortable: column.sortable }">
                            {{ column.title }}
                            <span v-if="sortColumn === column.key">
                                {{ sortDirection === 'asc' ? '↑' : '↓' }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in displayedData" :key="item.id">
                        <td v-for="column in columns" :key="column.key">
                            <span v-if="column.render">
                                <component :is="column.render" :item="item" :value="item[column.key]" />
                            </span>
                            <span v-else>{{ item[column.key] }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="pagination">
                <button @click="previousPage" :disabled="currentPage <= 1">Previous</button>
                <span>Page {{ currentPage }} of {{ totalPages }}</span>
                <button @click="nextPage" :disabled="currentPage >= totalPages">Next</button>
            </div>
        </div>
    `,
    props: {
        columns: Array,
        data: Array,
        loading: Boolean
    },
    data() {
        return {
            searchQuery: '',
            currentPage: 1,
            pageSize: 10,
            sortColumn: null,
            sortDirection: 'asc'
        };
    },
    computed: {
        filteredData() {
            if (!this.searchQuery) return this.data;
            
            return this.data.filter(item => {
                return this.columns.some(column => {
                    const value = item[column.key];
                    return value && value.toString().toLowerCase()
                        .includes(this.searchQuery.toLowerCase());
                });
            });
        },
        
        sortedData() {
            if (!this.sortColumn) return this.filteredData;
            
            return [...this.filteredData].sort((a, b) => {
                const aVal = a[this.sortColumn];
                const bVal = b[this.sortColumn];
                
                if (this.sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
        },
        
        displayedData() {
            const start = (this.currentPage - 1) * this.pageSize;
            const end = start + this.pageSize;
            return this.sortedData.slice(start, end);
        },
        
        totalPages() {
            return Math.ceil(this.sortedData.length / this.pageSize);
        }
    },
    methods: {
        handleSearch() {
            this.currentPage = 1; // Reset to first page when searching
        },
        
        handleSort(column) {
            if (!column.sortable) return;
            
            if (this.sortColumn === column.key) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column.key;
                this.sortDirection = 'asc';
            }
        },
        
        handlePageSizeChange() {
            this.currentPage = 1; // Reset to first page
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        }
    }
});

// Register the component globally
nsExtraComponents.dataTable = DataTable;
```

## Best Practices

### Error Handling

1. **Always handle errors**: Use proper error handling in HTTP requests
2. **User feedback**: Provide clear feedback for success/error states
3. **Graceful degradation**: Handle network failures gracefully

### Performance

1. **Debounce searches**: Use debouncing for search inputs
2. **Lazy loading**: Load data on demand
3. **Caching**: Cache frequently used data
4. **Virtual scrolling**: For large datasets

### User Experience

1. **Loading states**: Show loading indicators for async operations
2. **Validation feedback**: Provide real-time validation feedback
3. **Accessibility**: Use proper ARIA labels and keyboard navigation
4. **Responsive design**: Ensure components work on all screen sizes
