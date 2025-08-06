<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ManageCityContact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'city-contact:manage {action} {--name=} {--slug=} {--email=} {--flag=} {--active=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage city contact configurations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                return $this->listCities();
            case 'add':
                return $this->addCity();
            case 'edit':
                return $this->editCity();
            case 'delete':
                return $this->deleteCity();
            case 'show':
                return $this->showConfig();
            default:
                $this->error('Invalid action. Use: list, add, edit, delete, show');
                return 1;
        }
    }

    private function listCities()
    {
        $cities = getActiveCities();
        
        if ($cities->isEmpty()) {
            $this->info('No cities found.');
            return 0;
        }

        $this->info('Active Cities:');
        $this->table(
            ['Name', 'Slug', 'Email', 'Flag', 'Active'],
            $cities->map(function ($city) {
                return [
                    $city['name'],
                    $city['slug'],
                    $city['email'],
                    $city['flag'] ?? 'No flag',
                    $city['is_active'] ? 'Yes' : 'No'
                ];
            })->toArray()
        );

        return 0;
    }

    private function addCity()
    {
        $name = $this->option('name') ?: $this->ask('City name (Arabic)');
        $slug = $this->option('slug') ?: $this->ask('City slug (URL-friendly)');
        $email = $this->option('email') ?: $this->ask('Contact email');
        $flag = $this->option('flag') ?: $this->ask('Flag path (optional)');
        $active = $this->option('active') !== null ? $this->option('active') : $this->confirm('Active?', true);

        $config = getCityContactConfig();
        
        // Check if slug already exists
        $existingSlugs = collect($config['cities'])->pluck('slug')->toArray();
        if (in_array($slug, $existingSlugs)) {
            $this->error('Slug already exists!');
            return 1;
        }

        $newCity = [
            'name' => $name,
            'slug' => $slug,
            'email' => $email,
            'flag' => $flag,
            'is_active' => $active,
        ];

        $config['cities'][] = $newCity;
        saveCityContactConfig($config);

        $this->info("City '{$name}' added successfully!");
        return 0;
    }

    private function editCity()
    {
        $slug = $this->option('slug') ?: $this->ask('City slug to edit');
        
        $config = getCityContactConfig();
        $cityIndex = collect($config['cities'])->search(function ($city) use ($slug) {
            return $city['slug'] === $slug;
        });

        if ($cityIndex === false) {
            $this->error("City with slug '{$slug}' not found!");
            return 1;
        }

        $city = $config['cities'][$cityIndex];
        
        $name = $this->option('name') ?: $this->ask('City name (Arabic)', $city['name']);
        $newSlug = $this->option('slug') ?: $this->ask('City slug (URL-friendly)', $city['slug']);
        $email = $this->option('email') ?: $this->ask('Contact email', $city['email']);
        $flag = $this->option('flag') ?: $this->ask('Flag path (optional)', $city['flag']);
        $active = $this->option('active') !== null ? $this->option('active') : $this->confirm('Active?', $city['is_active']);

        // Check if new slug already exists (excluding current city)
        if ($newSlug !== $slug) {
            $existingSlugs = collect($config['cities'])->pluck('slug')->toArray();
            unset($existingSlugs[$cityIndex]);
            if (in_array($newSlug, $existingSlugs)) {
                $this->error('Slug already exists!');
                return 1;
            }
        }

        $config['cities'][$cityIndex] = [
            'name' => $name,
            'slug' => $newSlug,
            'email' => $email,
            'flag' => $flag,
            'is_active' => $active,
        ];

        saveCityContactConfig($config);

        $this->info("City '{$name}' updated successfully!");
        return 0;
    }

    private function deleteCity()
    {
        $slug = $this->option('slug') ?: $this->ask('City slug to delete');
        
        $config = getCityContactConfig();
        $cityIndex = collect($config['cities'])->search(function ($city) use ($slug) {
            return $city['slug'] === $slug;
        });

        if ($cityIndex === false) {
            $this->error("City with slug '{$slug}' not found!");
            return 1;
        }

        $cityName = $config['cities'][$cityIndex]['name'];
        
        if (!$this->confirm("Are you sure you want to delete '{$cityName}'?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        unset($config['cities'][$cityIndex]);
        $config['cities'] = array_values($config['cities']);
        
        saveCityContactConfig($config);

        $this->info("City '{$cityName}' deleted successfully!");
        return 0;
    }

    private function showConfig()
    {
        $config = getCityContactConfig();
        $this->info('Current Configuration:');
        $this->line(json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return 0;
    }
} 