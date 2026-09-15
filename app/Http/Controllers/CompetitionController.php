<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompetitionController extends Controller
{
    //
    public function index(): View
    {
        $competitions = Competition::all();

        return view('competitions.index', compact('competitions'));
    }

    public function create(): View
    {
        return view('competitions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|unique:competitions,name|max:60',
            'description' => 'nullable|string|max:500',
            'status' => 'required|string|in:not_started,in_progress,finished',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Competition::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->filled('status') ? $request->status : 'not_started',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('competitions.index')->with('success', '¡Competition created successfully!');
    }

    public function edit(Competition $competition): View
    {
        return view('competitions.edit', compact('competition'));
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:60',
                Rule::unique('competitions', 'name')->ignore($competition->getKey()),
            ],
            'status' => 'required|string|in:not_started,in_progress,finished',
            'description' => 'nullable|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $competition->update([
            'name' => $request->name,
            'status' => $request->status,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('competitions.index')->with('success', '¡Competition updated successfully!');
    }

    public function destroy(Competition $competition): RedirectResponse
    {

        $competition->delete();

        return redirect()->route('competitions.index')->with('danger', 'Competition deleted successfully');
    }
}
