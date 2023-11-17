<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Mail\ApplicationCreated;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ApplicationController extends Controller
{
    public function store(Request $request)
    {
        if ($this->checkDate()) {
            return redirect()->back()->with("error", "you can create only 1 application a day !");
        }

        if ($request->hasFile("file")) {
            $name = $request->file("file")->getClientOriginalName();
            $path = $request->file('file')->storeAs(
                'files',
                $name,
                'public'
            );
        }
        $request->validate([
            'subject' => 'required|max:255',
            'message' => 'required',
            'file' => 'file|mimes:png,jpg,pdf',
        ]);
        $application = Application::create([
            "user_id" => auth()->user()->id,
            "subject" => $request->subject,
            "message" => $request->message,
            "file_url" => $path ?? null,
        ]);

        dispatch(new SendEmailJob($application));

        return redirect()->route("dashboard")->with("success", "");
    }

    public function checkDate()
    {
        if (auth()->user()->applications()->latest()->first() == null) {
            return false;
        }

        $last_application = auth()->user()->applications()->latest()->first();
        $last_app_date = Carbon::parse($last_application->created_at)->format("Y-m-d");
        $today = Carbon::now()->format("Y-m-d");
        if ($last_app_date == $today) {
            return true;
        }

    }
}
