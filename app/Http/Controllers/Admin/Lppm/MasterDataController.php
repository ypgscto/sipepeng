<?php

namespace App\Http\Controllers\Admin\Lppm;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(): View
    {
        $entities = collect(config('sipepeng_master.entities', []))
            ->map(function (array $config, string $key): array {
                $modelClass = $config['model'];

                return [
                    'key' => $key,
                    'label' => $config['label'],
                    'count' => $modelClass::query()->count(),
                    'active_count' => $modelClass::query()->where('is_active', true)->count(),
                    'route' => route('admin.master.'.$key.'.index'),
                ];
            })
            ->values();

        return view('admin.master.dashboard', [
            'entities' => $entities,
        ]);
    }
}
