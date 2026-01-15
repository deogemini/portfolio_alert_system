<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $to = env('CONTACT_EMAIL', env('MAIL_FROM_ADDRESS'));
        if ($to) {
            Notification::route('mail', $to)->notify(new ContactMessage(
                $data['name'],
                $data['email'],
                $data['message']
            ));
        }

        return ['ok' => true];
    }
}

