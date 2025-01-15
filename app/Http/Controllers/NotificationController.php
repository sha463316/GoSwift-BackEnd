<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function showNotifications()
    {
        $notifications = Notification::where('user_id', Auth::user()->id)->get();

        foreach ($notifications as $notification) {

            $notification->read = true;
            $notification->save();

        }
        return response()->json(['notifications' => $notifications]);
    }

    public function showNotification($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found.']);
        }
        return response()->json(['notifications' => $notification]);
    }


        public function numberOfNotifications()
        {

            $notifications = Notification::where('user_id', Auth::user()->id)->get();

            $num = 0;
            foreach ($notifications as $notification) {
                if (!$notification->read) {
                    $num +=1;
                }
            }
            return response()->json(['number' => $num]);
        }

    public function createNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);
        $notification = Notification::create([
            'user_id' => $request->input('user_id'),
            'message' => $request->input('message'),
            'read' => false
        ]);
        return response()->json(['notification' => $notification]);
    }

}
