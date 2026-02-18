<?php

namespace App\Http\Controllers;

use App\Models\Request as RepairRequest; // Alias Request model to avoid conflict with Illuminate\Http\Request
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function create()
    {
        return view('requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clientName' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^[\+\d\s\-\(\)]+$/', 'max:25'],
            'address' => 'required|string|max:255',
            'problemText' => 'required|string',
        ]);

        RepairRequest::create($validated);

        return redirect()->route('requests.create')->with('success', 'Заявка успешно создана!');
    }
}
