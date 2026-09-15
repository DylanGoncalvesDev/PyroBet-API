<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\SportMatch;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    //

    public function index(): View
    {
        $predictions = Prediction::where('user_id', auth()->id())->get();

        return view('predictions.index', compact('predictions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'match_id' => 'required|exists:matches,id',
            'prediction' => 'required|string|in:home,draw,away',
            'home_score_prediction' => 'required|integer|min:0|max:999',
            'away_score_prediction' => 'required|integer|min:0|max:999',
        ]);

        $match = SportMatch::findOrFail($request->match_id);

        /** @var SportMatch $match */
        if (now()->greaterThanOrEqualTo($match->date)) {
            return redirect()->back()->with('danger', '¡El partido ya comenzó o ha finalizado! No puedes realizar predicciones.');
        }

        Prediction::create([
            'user_id' => auth()->id(),
            'match_id' => $request->match_id,
            'status' => 'pending',
            'prediction' => $request->prediction,
            'home_score_prediction' => $request->home_score_prediction,
            'away_score_prediction' => $request->away_score_prediction,
        ]);

        return redirect()->route('matches.index')->with('success', '¡Predicción creada con éxito!');

    }

    public function edit(Prediction $prediction): View
    {
        $predictions = $prediction;

        return view('predictions.edit', compact('predictions'));
    }

    public function update(Request $request, Prediction $prediction): RedirectResponse
    {
        $predictions = $prediction;
        /** @var Prediction $prediction */
        $request->validate([
            'prediction' => 'required|string|in:home,draw,away',
            'home_score_prediction' => 'required|integer|min:0|max:999',
            'away_score_prediction' => 'required|integer|min:0|max:999',
        ]);

        if ($predictions->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar esta predicción.');
        }

        $match = SportMatch::findOrFail($predictions->match_id);
        /** @var SportMatch $match */
        if (now()->greaterThanOrEqualTo($match->date)) {
            return redirect()->back()->with('danger', '¡El partido ya comenzó! No puedes modificar tu predicción.');
        }

        $predictions->update([
            'prediction' => $request->prediction,
            'home_score_prediction' => $request->home_score_prediction,
            'away_score_prediction' => $request->away_score_prediction,
        ]);

        return redirect()->back()->with('success', 'La Prediccion se actualizo con exito.');
    }

    public function destroy(Prediction $prediction): RedirectResponse
    {

        $predictions = $prediction;

        /** @var Prediction $prediction */
        if ($predictions->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar esta predicción.');
        }

        $predictions->delete();

        return redirect()->back()->with('danger', 'Predicción eliminada.');
    }

    public function filter(Request $request): View
    {
        $query = Prediction::where('predictions.user_id', auth()->id());

        if ($request->input('result') === 'correct') {
            $query->where('points', '=', '8');
        }

        if ($request->input('result') === 'correct_winner') {
            $query->where('points', '=', '1');
        }

        if ($request->input('result') === 'correct_score') {
            $query->where('points', '=', '6');
        }

        if ($request->input('result') === 'correct_home_score') {
            $query->join('matches', 'predictions.match_id', '=', 'matches.id')->whereColumn('predictions.home_score_prediction', 'matches.home_team_score');
        }

        if ($request->input('result') === 'correct_away_score') {
            $query->join('matches', 'predictions.match_id', '=', 'matches.id')->whereColumn('predictions.away_score_prediction', 'matches.away_team_score');
        }

        if ($request->input('result') === 'incorrect') {
            $query->where('points', '=', '0');
        }

        if ($request->input('result') === 'newest') {
            $query->orderBy('predictions.created_at', 'desc');
        }

        if ($request->input('result') === 'oldest') {
            $query->orderBy('predictions.created_at', 'asc');
        }

        if ($request->filled('specific_date')) {
            $query->whereDate('predictions.created_at', '=', (string) $request->input('specific_date'));
        }

        $predictions = $query->select('predictions.*')
            ->with(['match.homeTeam', 'match.awayTeam'])
            ->get();

        return view('predictions.index', compact('predictions'));
    }

    public function ranking(): View
    {
        $users = User::withSum('predictions as total_points', 'points')
            ->orderBy('total_points', 'desc')
            ->take(50)
            ->get();

        return view('predictions.ranking', compact('users'));
    }
}
