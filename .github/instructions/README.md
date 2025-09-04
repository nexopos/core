---
applyTo: '**'
---

# NexoPOS Core Framework - Complete Documentation

This directory contains comprehensive instructions for working with NexoPOS Core, a decoupled Laravel framework for building modular applications.

## 📚 Core Framework Overview

- **[Framework Overview](./nexopos-core-overview.instructions.md)** - Introduction to NexoPOS Core features and architecture

## 🏗️ Module System

- **[Module Generation](./module-generation.instructions.md)** - Creating new modules with the `make:module` command
- **[Module Migrations](./module-migrations.instructions.md)** - Running database migrations for modules
- **[CRUD Generation](./crud-generation.instructions.md)** - Generating complete CRUD functionality

## 🎨 User Interface Components

- **[Settings Pages](./settings-pages.instructions.md)** - Creating configuration forms with tabs and validation
- **[Forms](./forms.instructions.md)** - Building dynamic forms for data collection
- **[Fields](./fields.instructions.md)** - Simple field definitions for basic forms
- **[Widgets](./widgets.instructions.md)** - Dashboard widgets with Vue 3 components

## 🔧 Backend Architecture

- **[Service Providers](./service-providers.instructions.md)** - Registering services, filters, and components
- **[Events and Listeners](./events-listeners.instructions.md)** - Event-driven architecture with automatic discovery
- **[Models](./models.instructions.md)** - Eloquent models with module-specific features

## 🎯 Frontend Development

- **[Form Validation](./form-validation.instructions.md)** - Client-side and server-side validation system
- **[Frontend Assets and APIs](./frontend-assets-apis.instructions.md)** - Vue 3, HTTP client, popups, notifications

## 🚀 Quick Start Guide

1. **Generate a Module**:
   ```bash
   php artisan make:module
   ```

2. **Create Database Structure**:
   ```bash
   php artisan module:migrate ModuleName
   ```

3. **Generate CRUD Operations**:
   ```bash
   php artisan make:crud ModuleName
   ```

4. **Add Settings Page**:
   - Create class in `Settings/` directory extending `SettingsPage`
   - Define form structure with tabs and fields

5. **Create Widgets**:
   - Create widget class in `Widgets/` directory
   - Create Vue component in `Resources/ts/widgets/`
   - Register widget in service provider

## 📋 Key Features

### 🔄 Automatic Discovery
- **Routes**: `web.php` and `api.php` automatically loaded
- **Views**: Module views accessible via namespace (e.g., `ModuleName::view`)
- **Events**: Listeners automatically discovered
- **Migrations**: Module migrations automatically detected

### 🎨 Frontend Integration
- **Vue 3**: Full Vue 3 support with TypeScript
- **TailwindCSS 4**: Modern utility-first CSS framework
- **Vite**: Fast build tool for assets
- **Global APIs**: HTTP client, popups, notifications, form validation

### 🔐 Built-in Features
- **Permission System**: Role-based access control
- **Option Storage**: Automatic settings persistence
- **Event System**: Laravel events with module integration
- **Widget System**: Dashboard widgets with Vue components
- **Form Builder**: Dynamic forms with validation

## 🏗️ Architecture Principles

### Modular Design
- Self-contained modules with all necessary files
- Clear separation of concerns
- Reusable components across modules

### Laravel Standards
- PSR-4 autoloading
- Laravel naming conventions
- Standard directory structure
- Eloquent relationships and scopes

### Modern Frontend
- Vue 3 Composition API
- TypeScript support
- Reactive data binding
- Component-based architecture

## 📖 Documentation Structure

Each instruction file contains:
- **Overview**: What the feature does
- **Creating**: Step-by-step creation guide
- **Examples**: Real-world code examples
- **Best Practices**: Recommended patterns
- **Integration**: How it works with other features

## 🔗 Related Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Vue 3 Documentation**: https://vuejs.org/guide/
- **TailwindCSS Documentation**: https://tailwindcss.com/docs
- **TypeScript Documentation**: https://www.typescriptlang.org/docs/

## 🤝 Contributing

When working with NexoPOS Core:

1. **Follow conventions**: Use established naming and structure patterns
2. **Document changes**: Update relevant instruction files
3. **Test thoroughly**: Ensure all features work as expected
4. **Use localization**: Always use `__m()` for translatable text
5. **Handle errors**: Implement proper error handling and user feedback
