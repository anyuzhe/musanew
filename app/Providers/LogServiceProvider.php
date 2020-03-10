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
        //"eloquent.updated: App\Models\Recruit"
        Event::listen('eloquent.*', function ($eventName, $data) {
            if (preg_match('/eloquent\.(.+):\s(.+)/', $eventName, $match) === 0 || $match[2]=='App\Models\ModelLog') {
                return;
            }
            /** $match @val array
            array:3 [▼
            0 => "eloquent.updated: App\Models\Recruit"
            1 => "updated"
            2 => "App\Models\Recruit"
            ]
             */
            // only record when 'created', 'updated', 'deleted'
            if (!in_array($match[1], ['created', 'updated', 'deleted'])) {
//                dump($match[1]);
                return;
            }
            $user = TokenRepository::getUser();
            // only record the admin operation.
//            if (!$user) {
//                return;
//            }

            $model = $data[0];
            $class = get_class($model);
            $diff = array_diff_assoc($model->getOriginal(), $model->getAttributes());
            $keys = array_keys($diff);
            $data = [];

            $this->checkLeading($model, $match);
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
            ModelLog::create([
                'user_id' => $user?$user->id:null,
                'url' => request()->fullUrl(),
                'action' => $match[1], // updated created deleted
                'ip' => request()->getClientIp(),
                'model_id' => $model->id,
                'model_type' => $class,
                'data' => json_encode($data, 256),
                'created_at' => now(),
            ]);
        });
    }

    public function checkLeading($model, $match)
    {
        if($match[2]=='App\Models\Recruit' && in_array($match[1], ['created', 'updated'])){
            $leading = $model->leading;
            if($match[1]=='created'){
                $model->leading_id = 0;
                sendLeadingEmail($model,null, $leading);
            }else{
                $model->leading_id = $model->getOriginal('leading_id');
                sendLeadingEmail($model,null, $leading);
            }
        }elseif($match[2]=='App\Models\Entrust' && in_array($match[1], ['created', 'updated'])){
            $leading = $model->leading;
            if($match[1]=='created'){
                $model->leading_id = 0;
                sendLeadingEmail(null,$model, $leading);
            }else{
                $model->leading_id = $model->getOriginal('leading_id');
                sendLeadingEmail(null,$model, $leading);
            }
        }
    }
}
