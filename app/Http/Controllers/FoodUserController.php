<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\FoodUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FoodUserController extends Controller
{
    public function goToSeleccionarView()
    {
        $user = auth()->user();

        $comidasCreadasByUnidad = Food::where('unit_id', auth()->user()->unit_id)->pluck('id','descripcion');

        $comidasSeleccionadasByUsuario = $user->foods()
            ->get();

        $comidasByUsuario = [];

        foreach ($comidasSeleccionadasByUsuario as $comida) {
            $date = Carbon::parse($comida->pivot->date)->format('d-m');
            $comidasByUsuario[] = [
                'date' => $date,
                'comida' => $comida->descripcion,
                'food_id' => $comida->id,
            ];
        }
        return view('seleccionar', compact(['comidasByUsuario', 'comidasCreadasByUnidad']));
    }

    public function guardarRacionesSeleccionadas(Request $request)
    {
        FoodUser::where('user_id', '=', $request->userId)->delete();
        $seleccionadas = $request->mealPlan;
        $comidasSeleccionadas = [];
        foreach ($seleccionadas as $item) {
            $comidasSeleccionadas[] = [
                'user_id' => $request->userId,
                'food_id' => $item['food_id'],
                'date' => $item['date'],
            ];
        }
        FoodUser::insert($comidasSeleccionadas);
    }

    public function valesTodayByUser($userId)
    {
        $comidasCreadasByUnidad = Food::where('unit_id', auth()->user()->unit_id)->pluck('descripcion','id');

        $comidasHoy = FoodUser::where('user_id', $userId)
            ->whereDate('date', Carbon::today())
            ->get();

        $resultado = $comidasHoy->mapWithKeys(function ($foodUser) use ($comidasCreadasByUnidad) {
            $descripcion = $comidasCreadasByUnidad[$foodUser->food_id] ?? 'Desconocido';
            return [$descripcion => 'true'];
        })->toArray();

        $comidasFinales = $comidasCreadasByUnidad->mapWithKeys(fn($desc) => [$desc => 'false'])->merge($resultado);

        return response()->json($comidasFinales);
    }

    public function editValesByUser(Request $request)
    {
        // Eliminar vales previos del usuario para hoy
        FoodUser::where('date', Carbon::today()->format('Y-m-d') . ' 00:00:00')
            ->where('user_id', $request->userId)
            ->delete();

        // Obtener los IDs de comidas disponibles en la unidad
        $comidasCreadasByUnidad = Food::where('unit_id', auth()->user()->unit_id)->pluck('id', 'descripcion');

        // Recorrer las selecciones del usuario
        foreach ($request->selections as $comida => $seleccion) {
            if ($seleccion === "true" && isset($comidasCreadasByUnidad[$comida])) {
                FoodUser::create([
                    'date' => Carbon::today()->format('Y-m-d') . ' 00:00:00',
                    'user_id' => $request->userId,
                    'food_id' => $comidasCreadasByUnidad[$comida], // Asociar el ID correcto
                ]);
            }
        }
    }

}
