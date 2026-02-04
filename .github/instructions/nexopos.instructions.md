---
applyTo: '**'
---

# NexoPOS Core Instructions Index

This document serves as a comprehensive index to all NexoPOS Core instruction files. When working with NexoPOS, refer to these specialized instruction files for detailed guidance on specific topics.

## 📖 How to Use This Guide

When you need help with a specific NexoPOS feature or component:
1. Find the relevant topic in the sections below
2. Click the link to read the detailed instructions
3. Follow the examples and conventions described in that file

## 🚀 Getting Started

### Core Overview
- **[nexopos-core-overview.instructions.md](./nexopos-core-overview.instructions.md)** - Start here! Overview of NexoPOS Core framework, key features, global frontend assets (nsHttpClient, popups, notifications), and directory structure.

### Module Development
- **[nexopos-modules.instructions.md](./nexopos-modules.instructions.md)** - Comprehensive guide to creating, structuring, and developing modules. Covers module directory structure, configuration, lifecycle, and best practices.
- **[module-generation.instructions.md](./module-generation.instructions.md)** - Using the `php artisan make:module` command to generate new modules with complete Laravel-like structure.
- **[nexopos-module-quick-reference.instructions.md](./nexopos-module-quick-reference.instructions.md)** - Quick reference guide for common module development tasks.
- **[module-migrations.instructions.md](./module-migrations.instructions.md)** - Creating and managing database migrations within modules.
- **[module-service-provider-conventions.instructions.md](./module-service-provider-conventions.instructions.md)** - Conventions and patterns for module service providers.

## 🗃️ Data Management

### CRUD System
- **[crud-generation.instructions.md](./crud-generation.instructions.md)** - Using `php artisan make:crud` command to generate complete CRUD functionality.
- **[nexopos-crud.instructions.md](./nexopos-crud.instructions.md)** - Comprehensive guide to the NexoPOS CRUD system architecture, components, and implementation.
- **[nexopos-crud-accurate.instructions.md](./nexopos-crud-accurate.instructions.md)** - Accurate and detailed CRUD implementation patterns.

### Database & Models
- **[models.instructions.md](./models.instructions.md)** - Creating Eloquent models that extend `Ns\Models\NsModel`, relationships, scopes, and best practices.
- **[nexopos-migrations.instructions.md](./nexopos-migrations.instructions.md)** - Database migration patterns and conventions in NexoPOS.

## 📝 Forms & Input

### Form Creation
- **[forms.instructions.md](./forms.instructions.md)** - Creating dynamic forms for various purposes (login, registration, contact forms, etc.) using Form classes.
- **[settings-pages.instructions.md](./settings-pages.instructions.md)** - Creating configuration/settings pages with tabs, validation, and automatic option storage.
- **[form-validation.instructions.md](./form-validation.instructions.md)** - Form validation rules and patterns.

### Form Components
- **[nexopos-forminput.instructions.md](./nexopos-forminput.instructions.md)** - Complete guide to the `FormInput` class with all available input types (text, select, checkbox, radio, etc.).
- **[fields.instructions.md](./fields.instructions.md)** - Simple field definitions for basic forms.
- **[nexopos-tabs.instructions.md](./nexopos-tabs.instructions.md)** - Using `ns-tabs` and `ns-tabs-item` components to create tabbed interfaces.

## 🎨 Frontend & UI

### JavaScript/TypeScript Assets
- **[frontend-assets-apis.instructions.md](./frontend-assets-apis.instructions.md)** - Frontend assets and APIs available in NexoPOS (global objects, utilities, helpers).
- **[nexopos-httpclient.instructions.md](./nexopos-httpclient.instructions.md)** - Using the `nsHttpClient` for making HTTP requests with RxJS observables. **Important:** Response callbacks receive the body directly, not wrapped in `.data`.

### UI Components
- **[nexopos-popup.instructions.md](./nexopos-popup.instructions.md)** - Promise-based popup system for modal dialogs, forms, and interactive components.
- **[nexopos-asidemenu.instructions.md](./nexopos-asidemenu.instructions.md)** - Creating and managing sidebar/aside menu items.
- **[nexopos-widgets.instructions.md](./nexopos-widgets.instructions.md)** - Creating dashboard widgets with Vue 3 components.
- **[widgets.instructions.md](./widgets.instructions.md)** - Additional widget development patterns.

### View System
- **[nexopos-view-injection.instructions.md](./nexopos-view-injection.instructions.md)** - Injecting custom views into various dashboard parts using Laravel Events (the official and only supported method).

## 🔐 Security & Permissions

### Authorization
- **[nexopos-permissions.instructions.md](./nexopos-permissions.instructions.md)** - Understanding and implementing the permission system, creating permissions, and assigning them to roles.
- **[nexopos-roles-permissions.instructions.md](./nexopos-roles-permissions.instructions.md)** - Managing roles and their permissions.
- **[nexopos-middleware.instructions.md](./nexopos-middleware.instructions.md)** - Using middleware for route protection, especially `NsRestrictMiddleware` for permission-based access control.

## ⚙️ Architecture & Services

### Service Providers & Events
- **[service-providers.instructions.md](./service-providers.instructions.md)** - Creating service providers for registering services, filters, hooks, and components.
- **[events-listeners.instructions.md](./events-listeners.instructions.md)** - Laravel event system with automatic listener discovery for decoupled, event-driven architecture.

## 📚 Instruction Categories

### By Development Phase

**Phase 1: Planning & Setup**
1. [nexopos-core-overview.instructions.md](./nexopos-core-overview.instructions.md)
2. [nexopos-modules.instructions.md](./nexopos-modules.instructions.md)
3. [module-generation.instructions.md](./module-generation.instructions.md)

**Phase 2: Database & Models**
1. [models.instructions.md](./models.instructions.md)
2. [module-migrations.instructions.md](./module-migrations.instructions.md)
3. [nexopos-migrations.instructions.md](./nexopos-migrations.instructions.md)

**Phase 3: CRUD & Forms**
1. [crud-generation.instructions.md](./crud-generation.instructions.md)
2. [nexopos-crud.instructions.md](./nexopos-crud.instructions.md)
3. [forms.instructions.md](./forms.instructions.md)
4. [nexopos-forminput.instructions.md](./nexopos-forminput.instructions.md)

**Phase 4: Frontend Integration**
1. [frontend-assets-apis.instructions.md](./frontend-assets-apis.instructions.md)
2. [nexopos-httpclient.instructions.md](./nexopos-httpclient.instructions.md)
3. [nexopos-popup.instructions.md](./nexopos-popup.instructions.md)
4. [nexopos-widgets.instructions.md](./nexopos-widgets.instructions.md)

**Phase 5: Security & Deployment**
1. [nexopos-permissions.instructions.md](./nexopos-permissions.instructions.md)
2. [nexopos-middleware.instructions.md](./nexopos-middleware.instructions.md)
3. [service-providers.instructions.md](./service-providers.instructions.md)

### By Component Type

**Backend Components**
- Models: [models.instructions.md](./models.instructions.md)
- CRUD: [nexopos-crud.instructions.md](./nexopos-crud.instructions.md)
- Events: [events-listeners.instructions.md](./events-listeners.instructions.md)
- Services: [service-providers.instructions.md](./service-providers.instructions.md)
- Permissions: [nexopos-permissions.instructions.md](./nexopos-permissions.instructions.md)
- Middleware: [nexopos-middleware.instructions.md](./nexopos-middleware.instructions.md)

**Frontend Components**
- HTTP Client: [nexopos-httpclient.instructions.md](./nexopos-httpclient.instructions.md)
- Popups: [nexopos-popup.instructions.md](./nexopos-popup.instructions.md)
- Forms: [nexopos-forminput.instructions.md](./nexopos-forminput.instructions.md)
- Tabs: [nexopos-tabs.instructions.md](./nexopos-tabs.instructions.md)
- Widgets: [nexopos-widgets.instructions.md](./nexopos-widgets.instructions.md)
- Menu: [nexopos-asidemenu.instructions.md](./nexopos-asidemenu.instructions.md)

**Configuration & Settings**
- Settings Pages: [settings-pages.instructions.md](./settings-pages.instructions.md)
- Module Config: [module-service-provider-conventions.instructions.md](./module-service-provider-conventions.instructions.md)

## 🔍 Quick Reference by Task

### "I want to create a new module"
→ [module-generation.instructions.md](./module-generation.instructions.md)

### "I want to create CRUD functionality"
→ [crud-generation.instructions.md](./crud-generation.instructions.md) + [nexopos-crud.instructions.md](./nexopos-crud.instructions.md)

### "I want to create a form"
→ [forms.instructions.md](./forms.instructions.md) + [nexopos-forminput.instructions.md](./nexopos-forminput.instructions.md)

### "I want to add settings/configuration"
→ [settings-pages.instructions.md](./settings-pages.instructions.md)

### "I want to make HTTP requests"
→ [nexopos-httpclient.instructions.md](./nexopos-httpclient.instructions.md)

### "I want to show a popup/modal"
→ [nexopos-popup.instructions.md](./nexopos-popup.instructions.md)

### "I want to create a dashboard widget"
→ [nexopos-widgets.instructions.md](./nexopos-widgets.instructions.md)

### "I want to add permissions"
→ [nexopos-permissions.instructions.md](./nexopos-permissions.instructions.md)

### "I want to protect routes"
→ [nexopos-middleware.instructions.md](./nexopos-middleware.instructions.md)

### "I want to inject custom views"
→ [nexopos-view-injection.instructions.md](./nexopos-view-injection.instructions.md)

### "I want to create database tables"
→ [module-migrations.instructions.md](./module-migrations.instructions.md) + [nexopos-migrations.instructions.md](./nexopos-migrations.instructions.md)

### "I want to create models"
→ [models.instructions.md](./models.instructions.md)

### "I want to listen to events"
→ [events-listeners.instructions.md](./events-listeners.instructions.md)

### "I want to register services"
→ [service-providers.instructions.md](./service-providers.instructions.md)

## ⚠️ Important Conventions

### File Naming
- Module directories: PascalCase (e.g., `MyModule`)
- Model files: PascalCase (e.g., `Course.php`)
- Database tables: snake_case with module prefix (e.g., `mymodule_courses`)
- CRUD classes: Extend `CrudService`
- Models: Extend `Ns\Models\NsModel`

### Key Differences from Standard Laravel
1. **Models**: Extend `Ns\Models\NsModel` instead of `Illuminate\Database\Eloquent\Model`
2. **HTTP Client**: Use `nsHttpClient` with RxJS subscriptions, not async/await
3. **View Injection**: Use Laravel Events, not `Hook::addAction()` (deprecated)
4. **Translations**: Use `__m()` for module translations instead of `__()`
5. **Response Format**: `nsHttpClient` returns response body directly, not wrapped in `.data`

## 📋 Complete File List

All instruction files in this directory (excluding README.md):

1. [crud-generation.instructions.md](./crud-generation.instructions.md)
2. [events-listeners.instructions.md](./events-listeners.instructions.md)
3. [fields.instructions.md](./fields.instructions.md)
4. [form-validation.instructions.md](./form-validation.instructions.md)
5. [forms.instructions.md](./forms.instructions.md)
6. [frontend-assets-apis.instructions.md](./frontend-assets-apis.instructions.md)
7. [models.instructions.md](./models.instructions.md)
8. [module-generation.instructions.md](./module-generation.instructions.md)
9. [module-migrations.instructions.md](./module-migrations.instructions.md)
10. [module-service-provider-conventions.instructions.md](./module-service-provider-conventions.instructions.md)
11. [nexopos-asidemenu.instructions.md](./nexopos-asidemenu.instructions.md)
12. [nexopos-core-overview.instructions.md](./nexopos-core-overview.instructions.md)
13. [nexopos-crud-accurate.instructions.md](./nexopos-crud-accurate.instructions.md)
14. [nexopos-crud.instructions.md](./nexopos-crud.instructions.md)
15. [nexopos-forminput.instructions.md](./nexopos-forminput.instructions.md)
16. [nexopos-httpclient.instructions.md](./nexopos-httpclient.instructions.md)
17. [nexopos-middleware.instructions.md](./nexopos-middleware.instructions.md)
18. [nexopos-migrations.instructions.md](./nexopos-migrations.instructions.md)
19. [nexopos-module-quick-reference.instructions.md](./nexopos-module-quick-reference.instructions.md)
20. [nexopos-modules.instructions.md](./nexopos-modules.instructions.md)
21. [nexopos-permissions.instructions.md](./nexopos-permissions.instructions.md)
22. [nexopos-popup.instructions.md](./nexopos-popup.instructions.md)
23. [nexopos-roles-permissions.instructions.md](./nexopos-roles-permissions.instructions.md)
24. [nexopos-tabs.instructions.md](./nexopos-tabs.instructions.md)
25. [nexopos-view-injection.instructions.md](./nexopos-view-injection.instructions.md)
26. [nexopos-widgets.instructions.md](./nexopos-widgets.instructions.md)
27. [service-providers.instructions.md](./service-providers.instructions.md)
28. [settings-pages.instructions.md](./settings-pages.instructions.md)
29. [widgets.instructions.md](./widgets.instructions.md)

---

**Note for AI Assistants**: When working with NexoPOS Core, always consult the relevant instruction file from the list above. Each file contains detailed examples, code patterns, and best practices for its specific topic. Start with [nexopos-core-overview.instructions.md](./nexopos-core-overview.instructions.md) to understand the framework fundamentals, then dive into specific topics as needed.
