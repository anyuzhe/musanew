<?php
namespace App\Providers;
use App\Models\ModelLog;
use App\Repositories\TokenRepository;
use App\ZL\Moodle\TokenHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
class LogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen('eloquent.*', function ($eventName, $data) {
            if (preg_match('/eloquent\.(.+):\s(.+)/', $eventName, $match) === 0) {
                return;
            }
            /** $match @val array
            array (
            0 => 'eloquent.booting: App\\Models\\Groupon',
            1 => 'booting',
            2 => 'App\\Models\\Groupon',
            )
             */
            // only record when 'created', 'updated', 'deleted'
            if (!in_array($match[1], ['created', 'updated', 'deleted'])) {
                return;
            }
            $user = TokenRepository::getUser();
            // only record the admin operation.
            if (!$user) {
                return;
            }
            $model = $data[0];
            $class = get_class($model);
            $diff = array_diff_assoc($model->getOriginal(), $model->getAttributes());
            $keys = array_keys($diff);
            $data = [];
            foreach ($keys as $key) {
                if ($key === 'updated_at') {
                    continue;
                }
                $data[$key] = [
                    'old' => $model->getOriginal($key),
                    'new' => $model->getAttributes()[$key]
                ];
            }
            // You can create the table with your situation
            ModelLog::query()->create([
                'admin_id' => $user->id,
                'url' => request()->fullUrl(),
                'action' => $match[1], // updated created deleted
                'ip' => request()->getClientIp(),
                'model_id' => $model->id,
                'model_type' => $class,
                'data' => $data,
                'created_at' => now(),
            ]);
        });
    }
}
