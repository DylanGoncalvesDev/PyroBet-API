<?php

namespace App\Http\Controllers;

use App\Models\SportMatch;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    //
    public function index(): View
    {
        $teams = Team::all();

        return view('teams.index', compact('teams'));
    }

    public function create(): View
    {
        return view('teams.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|unique:teams,name|max:50',
            'logo' => 'nullable|string|max:100',
            'country' => 'required|string|max:50',
            'founded_at' => 'required|integer|min:1800|max:'.date('Y'),
            'type' => 'required|string|in:club,national',
            'sport' => 'required|string|in:american football,soccer football,rugby,basketball,baseball,cricket,softball,volleyball,hockey,handball,futsal',
        ]);

        Team::create([
            'name' => $request->name,
            'logo' => $request->filled('logo') ? $request->logo : 'default.png',
            'country' => $request->country,
            'founded_at' => $request->founded_at,
            'type' => $request->type,
            'sport' => $request->sport,
        ]);

        return redirect()->route('teams.index')->with('success', '¡Team created successfully!');
    }

    public function edit(Team $team): View
    {
        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team): RedirectResponse
    {

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('teams', 'name')->ignore($team->getKey()),
            ],
            'logo' => 'nullable|string|max:100',
            'country' => 'required|string|max:50',
            'founded_at' => 'required|integer|min:1800|max:'.date('Y'),
            'type' => 'required|string|in:club,national',
            'sport' => 'required|string|in:american football,soccer football,rugby,basketball,baseball,cricket,softball,volleyball,hockey,handball,futsal',
        ]);

        $team->update([
            'name' => $request->name,
            'logo' => $request->filled('logo') ? $request->logo : $team->logo,
            'country' => $request->country,
            'founded_at' => $request->founded_at,
            'type' => $request->type,
            'sport' => $request->sport,
        ]);

        return redirect()->route('teams.index')->with('success', '¡Team updated successfully!');
    }

    public function destroy(Team $team): RedirectResponse
    {

        $hasMatches = SportMatch::where('home_team_id', $team->getKey())
            ->orWhere('away_team_id', $team->getKey())
            ->exists();

        if ($hasMatches) {
            return redirect()->back()->with('danger', 'No se puede eliminar un equipo que ya tiene partidos registrados.');
        }
        $team->delete();

        return redirect()->route('teams.index')->with('danger', 'Team deleted successfully');
    }
}
