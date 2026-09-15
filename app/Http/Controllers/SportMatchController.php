<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\SportMatch;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SportMatchController extends Controller
{
    //
    public function index(): View|RedirectResponse
    {
        if (auth()->user() && auth()->user()->role === 'admin') {
            return redirect()->route('admin.matches.index');
        }
        $matches = SportMatch::with(['homeTeam', 'awayTeam'])->get();

        return view('matches.index', compact('matches'));
    }

    public function adminIndex(): View
    {
        $matches = SportMatch::with(['homeTeam', 'awayTeam'])->get();

        return view('admin.matches.index', compact('matches'));
    }

    public function welcome(): View
    {
        $matches = SportMatch::with(['homeTeam', 'awayTeam'])->get();

        return view('welcome', compact('matches'));
    }

    public function show(SportMatch $sportMatch): View
    {
        $sportMatch->load(['homeTeam', 'awayTeam', 'competition']);

        return view('matches.show', compact('sportMatch'));
    }

    public function create(): View
    {
        $teams = Team::all();
        $competitions = Competition::all();

        return view('matches.create', compact('teams', 'competitions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'date' => $request->status === 'upcoming' ? 'required|date|after_or_equal:today' : 'required|date',
            'location' => 'required|string|max:255',
            'stage' => 'required|string|max:100',
            'status' => 'required|in:upcoming,live,finished',
            'home_team_score' => 'nullable|integer|min:0|max:999',
            'away_team_score' => 'nullable|integer|min:0|max:999',
            'sport' => 'required|string|in:american football,soccer football,rugby,basketball,baseball,cricket,softball,volleyball,hockey,handball,futsal',
            'competition_id' => 'required|exists:competitions,id',
        ]);

        SportMatch::create([
            'home_team_id' => $request->home_team_id,
            'away_team_id' => $request->away_team_id,
            'date' => $request->date,
            'location' => $request->location,
            'stage' => $request->stage,
            'status' => $request->status,
            'home_team_score' => $request->home_team_score,
            'away_team_score' => $request->away_team_score,
            'sport' => $request->sport,
            'competition_id' => $request->competition_id,
        ]);

        return redirect()->route('matches.index')->with('success', '¡The Match has been created successfuly!');
    }

    public function edit(SportMatch $sportMatch): View
    {

        return view('matches.edit', compact('sportMatch'));
    }

    public function update(Request $request, SportMatch $sportMatch): RedirectResponse
    {
        $request->validate([
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'date' => 'required|date',
            'location' => 'required|string',
            'stage' => 'required|string',
            'status' => 'required|in:upcoming,live,finished',
            'home_team_score' => 'nullable|integer|min:0|max:999',
            'away_team_score' => 'nullable|integer|min:0|max:999',
            'sport' => 'required|string|in:american football,soccer football,rugby,basketball,baseball,cricket,softball,volleyball,hockey,handball,futsal',
            'competition_id' => 'required|exists:competitions,id',
        ]);

        if (in_array($request->status, ['live', 'finished']) && strtotime($sportMatch->date) > time()) {
            return redirect()->back()->withInput()->with('danger', 'Error: the match has not yet reached its scheduled date.');
        }

        $sportMatch->update([
            'home_team_id' => $request->home_team_id,
            'away_team_id' => $request->away_team_id,
            'date' => $request->date,
            'location' => $request->location,
            'stage' => $request->stage,
            'status' => $request->status,
            'home_team_score' => $request->home_team_score,
            'away_team_score' => $request->away_team_score,
            'sport' => $request->sport,
            'competition_id' => $request->competition_id,
        ]);

        if ($request->status === 'finished') {
            $predictions = $sportMatch->predictions;

            foreach ($predictions as $prediction) {
                $earnedPoints = 0;
                $result = 'draw';
                if ($request->home_team_score > $request->away_team_score) {
                    $result = 'home';
                } elseif ($request->home_team_score < $request->away_team_score) {
                    $result = 'away';
                }

                $hitResult = ($prediction->prediction === $result);

                if ($hitResult && $prediction->home_score_prediction == $request->home_team_score && $prediction->away_score_prediction == $request->away_team_score) {
                    $earnedPoints = 8;
                    $prediction->status = 'correct';
                } elseif ($prediction->home_score_prediction == $request->home_team_score && $prediction->away_score_prediction == $request->away_team_score) {
                    $earnedPoints = 6;
                    $prediction->status = 'correct';
                } elseif ($hitResult && ($prediction->home_score_prediction == $request->home_team_score || $prediction->away_score_prediction == $request->away_team_score)) {
                    $earnedPoints = 4;
                    $prediction->status = 'correct';
                } elseif ($prediction->home_score_prediction == $request->home_team_score || $prediction->away_score_prediction == $request->away_team_score) {
                    $earnedPoints = 2;
                    $prediction->status = 'correct';
                } elseif ($hitResult) {
                    $earnedPoints = 1;
                    $prediction->status = 'correct';
                } else {
                    $earnedPoints = 0;
                    $prediction->status = 'incorrect';
                }

                $prediction->update([
                    'points' => $earnedPoints,
                    'status' => $prediction->status,
                ]);
            }
        }

        return redirect()->route('admin.matches.index')->with('success', 'The Match has been updated successfully');
    }

    public function destroy(SportMatch $sportMatch): RedirectResponse
    {
        /** @var SportMatch $sportMatch */
        if ($sportMatch->predictions()->exists()) {
            return redirect()->back()->with('danger', 'No se puede eliminar un partido que ya tiene predicciones de usuarios.');
        }
        $sportMatch->delete();

        return redirect()->route('matches.index')->with('danger', 'The Match has been deleted successfuly');
    }

    public function filter(Request $request): View
    {
        $query = SportMatch::query();

        if ($request->input('result') === 'upcoming') {
            $query->where('status', '=', 'upcoming');
        }

        if ($request->input('result') === 'live') {
            $query->where('status', '=', 'live');
        }

        if ($request->input('result') === 'finished') {
            $query->where('status', '=', 'finished');
        }

        if ($request->filled('search_team')) {
            $wantedTeam = (string) $request->input('search_team');
            $query->join('teams as home', 'matches.home_team_id', '=', 'home.id')
                ->join('teams as away', 'matches.away_team_id', '=', 'away.id')
                ->where(function ($q) use ($wantedTeam) {
                    $q->where('home.name', 'LIKE', '%'.$wantedTeam.'%')
                        ->orWhere('away.name', 'LIKE', '%'.$wantedTeam.'%');
                });
        }

        if ($request->input('result') === 'newest') {
            $query->orderBy('matches.created_at', 'desc');
        }

        if ($request->input('result') === 'oldest') {
            $query->orderBy('matches.created_at', 'asc');
        }

        if ($request->filled('specific_date')) {
            $query->whereDate('matches.date', '=', (string) $request->input('specific_date'));
        }

        $matches = $query->select('matches.*')->with(['homeTeam', 'awayTeam'])->get();

        return view('matches.index', compact('matches'));
    }
}
