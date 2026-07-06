<?php

namespace App\Traits;

use Kreait\Firebase\Factory;

trait PushNotification
{
    public function toFcm($token, $title, $body, $data = [])
    {
        // DB::table("tuser")->where('fk_employee_id')
        $factory = (new Factory)->withServiceAccount('../app/configs/firebase_cred.json');
        $messaging = $factory->createMessaging();
        $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $token)
            ->withNotification(\Kreait\Firebase\Messaging\Notification::create($title, $body))
            ->withData($data);

        // DB::table('tnotification')
        // ->insert([
        //     'fk_user_id'=>
        // ])
        $messaging->send($message);
    }
}