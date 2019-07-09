<?php

namespace App\ZL\App;

use App\Models\WxpayRecord;
use App\Repositories\PaymentRepository;
use Payment\Notify\PayNotifyInterface;
use Payment\Config;

/**
 * 客户端需要继承该接口，并实现这个方法，在其中实现对应的业务逻辑
 * Class TestNotify
 * anthor helei
 */
class TestNotify implements PayNotifyInterface
{
    public function notifyProcess(array $data)
    {
        saveNotification($data,'test');

        $pay = app()->build(PaymentRepository::class);
        $channel = $data['channel'];
        if ($channel === Config::ALI_CHARGE) {// 支付宝支付
            $wxrecord = WxpayRecord::where('out_trade_no',$data['order_no'])->first();
            try {
                $pay->successBuyQrcode($wxrecord->qrcode_id, $wxrecord, $data);
            }catch (\Exception $e) {
                $error_arr = [
                    'msg'=>$e->getMessage(),
                    'getLine'=>$e->getLine(),
                    'getTrace'=>$e->getTrace(),
                    'getTraceAsString'=>$e->getTraceAsString(),
                ];
                saveNotification($error_arr,'error');
                exit;
            }
        } elseif ($channel === Config::WX_CHARGE) {// 微信支付
            if($data['result_code']=='SUCCESS'){
                $wxrecord = WxpayRecord::where('out_trade_no',$data['out_trade_no'])->first();

                try {
                    $pay->successBuyQrcode($wxrecord->qrcode_id, $wxrecord, $data);
                } catch (\Exception $e) {
                    $error_arr = [
                        'msg'=>$e->getMessage(),
                        'getLine'=>$e->getLine(),
                        'getTrace'=>$e->getTrace(),
                        'getTraceAsString'=>$e->getTraceAsString(),
                    ];
                    saveNotification($error_arr,'error');
                    exit;
                }
            }else{

            }
        } elseif ($channel === Config::CMB_CHARGE) {// 招商支付

        } elseif ($channel === Config::CMB_BIND) {// 招商签约

        } else {
            // 其它类型的通知
        }

        // 执行业务逻辑，成功后返回true
        return true;
    }
}