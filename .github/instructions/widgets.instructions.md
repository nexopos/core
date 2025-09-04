---
applyTo: '**'
---

# Widgets in NexoPOS Core

Widgets are Vue 3 components that provide dashboard functionality and can be displayed in various parts of the NexoPOS Core interface.

## Creating a Widget Class

Widget classes extend `Ns\Services\WidgetService` and are stored in the `Widgets/` directory:

```php
<?php
namespace Modules\ModuleName\Widgets;

use Ns\Services\WidgetService;

class CourseStatsWidget extends WidgetService
{
    /**
     * The Vue component name for this widget
     */
    protected $vueComponent = 'courseStatsWidget';

    /**
     * Constructor to define widget properties
     */
    public function __construct()
    {
        $this->name = __m('Course Statistics', 'ModuleName');
        $this->description = __m('Displays course enrollment and completion statistics.', 'ModuleName');
        $this->permission = 'modulename.see.course-stats';
    }
}
```

## Widget with Data Method

Widgets can provide data to their Vue components:

```php
<?php
namespace Modules\ModuleName\Widgets;

use Ns\Services\WidgetService;
use Modules\ModuleName\Models\Course;
use Modules\ModuleName\Models\Enrollment;

class ActiveCoursesWidget extends WidgetService
{
    protected $vueComponent = 'activeCoursesWidget';

    public function __construct()
    {
        $this->name = __m('Active Courses', 'ModuleName');
        $this->description = __m('Shows currently active courses with enrollment counts.', 'ModuleName');
        $this->permission = 'modulename.see.active-courses';
    }

    /**
     * Provide data to the widget component
     */
    public function getData()
    {
        return [
            'activeCourses' => Course::where('status', 'active')
                ->withCount('enrollments')
                ->limit(10)
                ->get(),
            'totalEnrollments' => Enrollment::count(),
            'totalCourses' => Course::count(),
        ];
    }
}
```

## Registering Widgets

Register widgets in a service provider's `boot` method:

```php
<?php
namespace Modules\ModuleName\Providers;

use Illuminate\Support\ServiceProvider;
use Ns\Services\WidgetService;
use Modules\ModuleName\Widgets\CourseStatsWidget;
use Modules\ModuleName\Widgets\ActiveCoursesWidget;
use Modules\ModuleName\Widgets\RecentActivitiesWidget;

class WidgetServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $widgetService = app()->make(WidgetService::class);
        $widgetService->registerWidgets([
            CourseStatsWidget::class,
            ActiveCoursesWidget::class,
            RecentActivitiesWidget::class,
        ]);
    }
}
```

## Creating Widget JavaScript/TypeScript Component

Create the frontend component in `Resources/ts/widgets/` (or `Resources/js/widgets/`):

**File: `Resources/ts/widgets/course-stats-widget.ts`**

```typescript
declare const defineComponent: any;
declare const __m: any;
declare const nsHttpClient: any;
declare const nsComponents: any;

window['courseStatsWidget'] = defineComponent({
    name: 'CourseStatsWidget',
    template: `
        <div class="sgu:bg-white sgu:shadow-md sgu:rounded-lg sgu:p-6">
            <div class="sgu:flex sgu:items-center sgu:justify-between sgu:mb-4">
                <h3 class="sgu:text-lg sgu:font-semibold sgu:text-gray-900">
                    {{ __m('Course Statistics', 'SGUniversity') }}
                </h3>
                <button @click="refreshData" 
                        class="sgu:text-blue-600 hover:sgu:text-blue-800">
                    <i class="las la-sync" :class="{ 'la-spin': loading }"></i>
                </button>
            </div>
            
            <div v-if="loading" class="sgu:text-center sgu:py-8">
                <i class="las la-spinner la-spin sgu:text-2xl sgu:text-gray-400"></i>
            </div>
            
            <div v-else class="sgu:grid sgu:grid-cols-2 sgu:gap-4">
                <div class="sgu:text-center">
                    <div class="sgu:text-3xl sgu:font-bold sgu:text-blue-600">
                        {{ stats.totalCourses }}
                    </div>
                    <div class="sgu:text-sm sgu:text-gray-600">
                        {{ __m('Total Courses', 'SGUniversity') }}
                    </div>
                </div>
                
                <div class="sgu:text-center">
                    <div class="sgu:text-3xl sgu:font-bold sgu:text-green-600">
                        {{ stats.totalEnrollments }}
                    </div>
                    <div class="sgu:text-sm sgu:text-gray-600">
                        {{ __m('Total Enrollments', 'SGUniversity') }}
                    </div>
                </div>
                
                <div class="sgu:text-center">
                    <div class="sgu:text-3xl sgu:font-bold sgu:text-purple-600">
                        {{ stats.activeCourses }}
                    </div>
                    <div class="sgu:text-sm sgu:text-gray-600">
                        {{ __m('Active Courses', 'SGUniversity') }}
                    </div>
                </div>
                
                <div class="sgu:text-center">
                    <div class="sgu:text-3xl sgu:font-bold sgu:text-orange-600">
                        {{ stats.completionRate }}%
                    </div>
                    <div class="sgu:text-sm sgu:text-gray-600">
                        {{ __m('Completion Rate', 'SGUniversity') }}
                    </div>
                </div>
            </div>
        </div>
    `,
    data() {
        return {
            loading: true,
            stats: {
                totalCourses: 0,
                totalEnrollments: 0,
                activeCourses: 0,
                completionRate: 0
            }
        };
    },
    mounted() {
        this.loadStats();
    },
    methods: {
        loadStats() {
            this.loading = true;
            
            nsHttpClient.get('/api/ns-lms/widgets/course-stats')
                .subscribe({
                    next: (response) => {
                        this.stats = response;
                        this.loading = false;
                    },
                    error: (error) => {
                        console.error('Failed to load course stats:', error);
                        this.loading = false;
                    }
                });
        },
        
        refreshData() {
            this.loadStats();
        }
    }
});
```

## Advanced Widget with Charts

**File: `Resources/ts/widgets/enrollment-chart-widget.ts`**

```typescript
declare const defineComponent: any;
declare const __m: any;
declare const nsHttpClient: any;
declare const Chart: any;

window['enrollmentChartWidget'] = defineComponent({
    name: 'EnrollmentChartWidget',
    template: `
        <div class="sgu:bg-white sgu:shadow-md sgu:rounded-lg sgu:p-6">
            <div class="sgu:flex sgu:items-center sgu:justify-between sgu:mb-4">
                <h3 class="sgu:text-lg sgu:font-semibold sgu:text-gray-900">
                    {{ __m('Enrollment Trends', 'SGUniversity') }}
                </h3>
                <select v-model="selectedPeriod" @change="loadChartData" 
                        class="sgu:text-sm sgu:border sgu:rounded sgu:px-2 sgu:py-1">
                    <option value="7">{{ __m('Last 7 Days', 'SGUniversity') }}</option>
                    <option value="30">{{ __m('Last 30 Days', 'SGUniversity') }}</option>
                    <option value="90">{{ __m('Last 90 Days', 'SGUniversity') }}</option>
                </select>
            </div>
            
            <div class="sgu:h-64">
                <canvas ref="chartCanvas"></canvas>
            </div>
        </div>
    `,
    data() {
        return {
            chart: null,
            selectedPeriod: '30',
            chartData: []
        };
    },
    mounted() {
        this.initChart();
        this.loadChartData();
    },
    beforeUnmount() {
        if (this.chart) {
            this.chart.destroy();
        }
    },
    methods: {
        initChart() {
            const ctx = this.$refs.chartCanvas.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: this.__m('Enrollments', 'SGUniversity'),
                        data: [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },
        
        loadChartData() {
            nsHttpClient.get(`/api/ns-lms/widgets/enrollment-chart?period=${this.selectedPeriod}`)
                .subscribe({
                    next: (response) => {
                        this.chart.data.labels = response.labels;
                        this.chart.data.datasets[0].data = response.data;
                        this.chart.update();
                    },
                    error: (error) => {
                        console.error('Failed to load chart data:', error);
                    }
                });
        }
    }
});
```

## Widget View Injection

Create a listener to inject widget scripts:

```php
<?php
namespace Modules\ModuleName\Listeners;

use Ns\Events\RenderFooterEvent;

class RenderFooterEventListener
{
    public function handle(RenderFooterEvent $event): void
    {
        // Inject widget scripts on dashboard home
        if ($event->routeName === 'ns.dashboard.home') {
            $event->output->addView('ModuleName::widgets.scripts');
        }
    }
}
```

**View: `Views/widgets/scripts.blade.php`**

```blade
@moduleViteAssets('Resources/ts/widgets/course-stats-widget.ts', 'ModuleName')
@moduleViteAssets('Resources/ts/widgets/enrollment-chart-widget.ts', 'ModuleName')
```

## Widget API Endpoints

Create API endpoints to provide data for widgets:

```php
<?php
namespace Modules\ModuleName\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ModuleName\Models\Course;
use Modules\ModuleName\Models\Enrollment;

class WidgetController extends Controller
{
    public function courseStats()
    {
        return response()->json([
            'totalCourses' => Course::count(),
            'totalEnrollments' => Enrollment::count(),
            'activeCourses' => Course::where('status', 'active')->count(),
            'completionRate' => $this->calculateCompletionRate(),
        ]);
    }

    public function enrollmentChart(Request $request)
    {
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);

        $enrollments = Enrollment::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];
        
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M j');
            
            $enrollment = $enrollments->firstWhere('date', $date);
            $data[] = $enrollment ? $enrollment->count : 0;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    private function calculateCompletionRate()
    {
        $totalEnrollments = Enrollment::count();
        if ($totalEnrollments === 0) return 0;

        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        return round(($completedEnrollments / $totalEnrollments) * 100, 1);
    }
}
```

## Routes for Widget APIs

Add routes in `Routes/api.php`:

```php
<?php
use Illuminate\Support\Facades\Route;
use Modules\ModuleName\Controllers\Api\WidgetController;

Route::prefix('ns-lms/widgets')->group(function () {
    Route::get('course-stats', [WidgetController::class, 'courseStats']);
    Route::get('enrollment-chart', [WidgetController::class, 'enrollmentChart']);
});
```

## Vite Configuration

Update your `vite.config.js` to include widget files:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'Resources/css/style.css',
                'Resources/ts/app.ts',
                'Resources/ts/widgets/course-stats-widget.ts',
                'Resources/ts/widgets/enrollment-chart-widget.ts',
            ],
            buildDirectory: 'build/ModuleName',
        }),
    ],
    build: {
        rollupOptions: {
            input: {
                'style': 'Resources/css/style.css',
                'app': 'Resources/ts/app.ts',
                'course-stats-widget': 'Resources/ts/widgets/course-stats-widget.ts',
                'enrollment-chart-widget': 'Resources/ts/widgets/enrollment-chart-widget.ts',
            }
        }
    }
});
```

## Best Practices

1. **Descriptive naming**: Use clear, descriptive names for widgets and components
2. **Proper permissions**: Always set appropriate permissions for widgets
3. **Localization**: Use `__m()` for all text in both PHP and JavaScript
4. **Error handling**: Handle API errors gracefully in Vue components
5. **Performance**: Cache widget data when appropriate
6. **Responsive design**: Ensure widgets work on different screen sizes
7. **Consistent styling**: Use the module's CSS prefix (e.g., `sgu:`) for styling
