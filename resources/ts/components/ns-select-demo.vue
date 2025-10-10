<!--
  Comprehensive Demo Component for ns-select Hierarchical Options
  
  This demo showcases all the new features:
  - Hierarchical options with optgroups
  - Disabled options
  - Deep nesting (multiple levels)
  - Mixed flat and nested options
-->

<template>
    <div class="p-8 space-y-8 max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">ns-select Hierarchical Options Demo</h1>

        <!-- Example 1: Basic Hierarchical -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Example 1: Basic Hierarchical Options</h2>
            <ns-select :field="basicHierarchical">
                Select Product Category
            </ns-select>
            <p class="mt-2 text-sm text-gray-600">Selected value: {{ basicHierarchical.value }}</p>
        </div>

        <!-- Example 2: Disabled Options -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Example 2: Options with Disabled Items</h2>
            <ns-select :field="disabledOptions">
                Select Product
            </ns-select>
            <p class="mt-2 text-sm text-gray-600">Selected value: {{ disabledOptions.value }}</p>
            <p class="mt-1 text-xs text-gray-500">Note: Some options are disabled and cannot be selected</p>
        </div>

        <!-- Example 3: Deep Nesting -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Example 3: Deep Nesting (3+ Levels)</h2>
            <ns-select :field="deepNesting">
                Select Location
            </ns-select>
            <p class="mt-2 text-sm text-gray-600">Selected value: {{ deepNesting.value }}</p>
        </div>

        <!-- Example 4: Mixed Structure -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Example 4: Mixed Flat and Hierarchical</h2>
            <ns-select :field="mixedStructure">
                Select Option
            </ns-select>
            <p class="mt-2 text-sm text-gray-600">Selected value: {{ mixedStructure.value }}</p>
            <p class="mt-1 text-xs text-gray-500">Combines simple options with grouped options</p>
        </div>

        <!-- Example 5: Real-world E-commerce -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Example 5: Real-world E-commerce Categories</h2>
            <ns-select :field="ecommerceCategories">
                Select Product Category
            </ns-select>
            <p class="mt-2 text-sm text-gray-600">Selected value: {{ ecommerceCategories.value }}</p>
        </div>

        <!-- Option Structure Reference -->
        <div class="bg-gray-50 p-6 rounded-lg border-2 border-gray-200">
            <h2 class="text-xl font-semibold mb-4">Option Structure Reference</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-sm mb-2">Simple Option:</h3>
                    <pre class="bg-gray-800 text-green-400 p-3 rounded text-xs overflow-x-auto">{{ JSON.stringify(simpleOptionExample, null, 2) }}</pre>
                </div>
                <div>
                    <h3 class="font-semibold text-sm mb-2">Disabled Option:</h3>
                    <pre class="bg-gray-800 text-green-400 p-3 rounded text-xs overflow-x-auto">{{ JSON.stringify(disabledOptionExample, null, 2) }}</pre>
                </div>
                <div>
                    <h3 class="font-semibold text-sm mb-2">Optgroup (Hierarchical):</h3>
                    <pre class="bg-gray-800 text-green-400 p-3 rounded text-xs overflow-x-auto">{{ JSON.stringify(optgroupExample, null, 2) }}</pre>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NsSelectDemo',
    data() {
        return {
            // Example 1: Basic hierarchical
            basicHierarchical: {
                name: 'category',
                value: null,
                options: [
                    {
                        label: 'Electronics',
                        value: [
                            { label: 'Laptops', value: 1 },
                            { label: 'Desktops', value: 2 },
                            { label: 'Tablets', value: 3 }
                        ]
                    },
                    {
                        label: 'Clothing',
                        value: [
                            { label: 'Men', value: 4 },
                            { label: 'Women', value: 5 },
                            { label: 'Kids', value: 6 }
                        ]
                    },
                    {
                        label: 'Books',
                        value: [
                            { label: 'Fiction', value: 7 },
                            { label: 'Non-Fiction', value: 8 }
                        ]
                    }
                ]
            },

            // Example 2: Disabled options
            disabledOptions: {
                name: 'product',
                value: null,
                options: [
                    {
                        label: 'In Stock',
                        value: [
                            { label: 'Product A - $29.99', value: 1 },
                            { label: 'Product B - $39.99', value: 2 }
                        ]
                    },
                    {
                        label: 'Out of Stock',
                        value: [
                            { label: 'Product C - $49.99 (Unavailable)', value: 3, disabled: true },
                            { label: 'Product D - $59.99 (Unavailable)', value: 4, disabled: true }
                        ]
                    },
                    { label: 'Discontinued - Product E', value: 5, disabled: true }
                ]
            },

            // Example 3: Deep nesting
            deepNesting: {
                name: 'location',
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
                                            { label: 'San Francisco', value: 2 },
                                            { label: 'San Diego', value: 3 }
                                        ]
                                    },
                                    {
                                        label: 'New York',
                                        value: [
                                            { label: 'New York City', value: 4 },
                                            { label: 'Buffalo', value: 5 }
                                        ]
                                    }
                                ]
                            },
                            {
                                label: 'Canada',
                                value: [
                                    { label: 'Toronto', value: 6 },
                                    { label: 'Vancouver', value: 7 }
                                ]
                            }
                        ]
                    },
                    {
                        label: 'Europe',
                        value: [
                            {
                                label: 'United Kingdom',
                                value: [
                                    { label: 'London', value: 8 },
                                    { label: 'Manchester', value: 9 }
                                ]
                            }
                        ]
                    }
                ]
            },

            // Example 4: Mixed structure
            mixedStructure: {
                name: 'selection',
                value: null,
                options: [
                    { label: 'Quick Option 1', value: 1 },
                    { label: 'Quick Option 2', value: 2 },
                    {
                        label: 'Advanced Options',
                        value: [
                            { label: 'Advanced 1', value: 3 },
                            { label: 'Advanced 2', value: 4 },
                            { label: 'Advanced 3 (Disabled)', value: 5, disabled: true }
                        ]
                    },
                    { label: 'Quick Option 3', value: 6 },
                    {
                        label: 'Expert Options',
                        value: [
                            {
                                label: 'Expert Level 1',
                                value: [
                                    { label: 'Expert 1.1', value: 7 },
                                    { label: 'Expert 1.2', value: 8 }
                                ]
                            }
                        ]
                    }
                ]
            },

            // Example 5: E-commerce categories
            ecommerceCategories: {
                name: 'ecommerce_category',
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
                                    { label: 'Energy Drinks', value: 104, disabled: true }
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
                                label: 'Computers',
                                value: [
                                    { label: 'Laptops', value: 301 },
                                    { label: 'Desktops', value: 302 },
                                    { label: 'Accessories', value: 303 }
                                ]
                            },
                            {
                                label: 'Mobile Devices',
                                value: [
                                    { label: 'Smartphones', value: 401 },
                                    { label: 'Tablets', value: 402 }
                                ]
                            }
                        ]
                    },
                    {
                        label: 'Home & Garden',
                        value: [
                            { label: 'Furniture', value: 501 },
                            { label: 'Decor', value: 502 },
                            { label: 'Tools', value: 503 }
                        ]
                    }
                ]
            },

            // Reference examples
            simpleOptionExample: {
                label: 'My Option',
                value: 1
            },
            disabledOptionExample: {
                label: 'Unavailable Option',
                value: 2,
                disabled: true
            },
            optgroupExample: {
                label: 'My Group',
                value: [
                    { label: 'Sub Option 1', value: 1 },
                    { label: 'Sub Option 2', value: 2 }
                ]
            }
        };
    }
};
</script>

<style scoped>
/* Optional: Add any custom styling for the demo */
</style>
