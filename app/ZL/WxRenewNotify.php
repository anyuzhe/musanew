<?php
namespace App\ZL;

use Payment\Notify\PayNotifyInterface;

class WxRenewNotify implements PayNotifyInterface
{
    /**
     * 客户端的业务逻辑，
     * @param array $data
     * @return bool  返回值一定是bool值
     * @author helei
     */
    public function notifyProcess(array $data)
    {
        // 一般支付的处理业务
//        1. 检查订单是否存在
//        2. 检查金额是否正确
//        3. 检查订单是否已经处理过（防止重复通知）
//        4. 更新订单
        $this->saveALlDataToLog($data);
//        $this->setUser($data);
//        return $this->editRecord($data);
        return true;
    }

    public function saveALlDataToLog($data)
    {
        \DB::table('notifications')->insert([
            'created_at'=>date('Y-m-d H:i:s'),
            'json'=>json_encode($data,256)
        ]);
    }

    public function editRecord($data)
    {
        return M('WxpayRecord')->data([
            'trade_state'=>$data['trade_state'],
            'transaction_id'=>$data['transaction_id'],
            'time_end'=>$data['time_end'],
            'notify_time'=>$data['notify_time'],
            'notify_type'=>$data['notify_type'],
            'updated_at'=>date('Y-m-d H:i:s'),
        ])->where([
            'id'=>json_decode($data['extra_param'])->record_id
        ])->save();
    }

    public function setUser($data)
    {
        if(strtolower($data['trade_state'])==='success'){
            $user_id = json_decode($data['extra_param'])->user_id;
            $payment_config_id = json_decode($data['extra_param'])->payment_config;
            $user = M('User')->find($user_id);
            $payment_config_id = M('PaymentConfig')->find($payment_config_id);
            if($user['end_time']>time()){
                $end_time = $user['end_time']+$payment_config_id['add_time'];
            }else{
                $end_time = time()+$payment_config_id['add_time'];
            }
            M('User')->data([
                'end_time' => $end_time
            ])->where([
                'id'=>$user_id
            ])->save();
        }
    }
}