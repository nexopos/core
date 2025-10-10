/**
 * Example usage of ns-select with hierarchical options
 * This file demonstrates how to use the new hierarchical option feature
 */

// Example 1: Basic hierarchical structure with optgroups
export const exampleHierarchicalField = {
    name: 'category_id',
    label: 'Select Category',
    value: null,
    options: [
        {
            label: 'Electronics',
            value: [
                {
                    label: 'Computers',
                    value: [
                        { label: 'Laptops', value: 1 },
                        { label: 'Desktops', value: 2 },
                        { label: 'Tablets', value: 3 }
                    ]
                },
                {
                    label: 'Mobile Devices',
                    value: [
                        { label: 'Smartphones', value: 4 },
                        { label: 'Feature Phones', value: 5 }
                    ]
                }
            ]
        },
        {
            label: 'Clothing',
            value: [
                { label: 'Men', value: 6 },
                { label: 'Women', value: 7 },
                { label: 'Kids', value: 8 }
            ]
        }
    ]
};

// Example 2: Options with disabled items
export const exampleDisabledField = {
    name: 'product_id',
    label: 'Select Product',
    value: null,
    options: [
        {
            label: 'In Stock',
            value: [
                { label: 'Product A', value: 1 },
                { label: 'Product B', value: 2 }
            ]
        },
        {
            label: 'Out of Stock',
            value: [
                { label: 'Product C (Unavailable)', value: 3, disabled: true },
                { label: 'Product D (Unavailable)', value: 4, disabled: true }
            ]
        },
        // Single disabled option
        { label: 'Discontinued Item', value: 5, disabled: true }
    ]
};

// Example 3: Mixed flat and hierarchical options
export const exampleMixedField = {
    name: 'selection',
    label: 'Select Option',
    value: null,
    options: [
        // Flat options
        { label: 'Option 1', value: 1 },
        { label: 'Option 2', value: 2 },
        
        // Hierarchical group
        {
            label: 'Advanced Options',
            value: [
                { label: 'Advanced 1', value: 3 },
                { label: 'Advanced 2', value: 4, disabled: true },
                {
                    label: 'Expert Options',
                    value: [
                        { label: 'Expert 1', value: 5 },
                        { label: 'Expert 2', value: 6 }
                    ]
                }
            ]
        }
    ]
};

// Example 4: Deep nesting (3+ levels)
export const exampleDeepNestingField = {
    name: 'location',
    label: 'Select Location',
    value: null,
    options: [
        {
            label: 'North America',
            value: [
                {
                    label: 'United States',
                    value: [
                        {
                            label: 'California',
                            value: [
                                { label: 'Los Angeles', value: 1 },
                                { label: 'San Francisco', value: 2 }
                            ]
                        },
                        {
                            label: 'New York',
                            value: [
                                { label: 'New York City', value: 3 },
                                { label: 'Buffalo', value: 4 }
                            ]
                        }
                    ]
                },
                {
                    label: 'Canada',
                    value: [
                        { label: 'Toronto', value: 5 },
                        { label: 'Vancouver', value: 6 }
                    ]
                }
            ]
        }
    ]
};

// Example 5: Real-world e-commerce category example
export const exampleEcommerceCategories = {
    name: 'category',
    label: 'Product Category',
    value: null,
    options: [
        {
            label: 'Food & Beverages',
            value: [
                {
                    label: 'Beverages',
                    value: [
                        { label: 'Coffee', value: 101 },
                        { label: 'Tea', value: 102 },
                        { label: 'Soft Drinks', value: 103 },
                        { label: 'Water', value: 104, disabled: true } // Out of stock
                    ]
                },
                {
                    label: 'Snacks',
                    value: [
                        { label: 'Chips', value: 201 },
                        { label: 'Cookies', value: 202 },
                        { label: 'Candy', value: 203 }
                    ]
                }
            ]
        },
        {
            label: 'Electronics',
            value: [
                {
                    label: 'Audio',
                    value: [
                        { label: 'Headphones', value: 301 },
                        { label: 'Speakers', value: 302 }
                    ]
                },
                {
                    label: 'Video',
                    value: [
                        { label: 'TVs', value: 401 },
                        { label: 'Projectors', value: 402, disabled: true } // Discontinued
                    ]
                }
            ]
        }
    ]
};

// Vue template usage example:
/*
<template>
    <div class="p-4">
        <ns-select :field="categoryField">
            Select a Category
        </ns-select>
    </div>
</template>

<script>
import { exampleHierarchicalField } from './ns-select-examples';

export default {
    data() {
        return {
            categoryField: exampleHierarchicalField
        };
    }
};
</script>
*/
